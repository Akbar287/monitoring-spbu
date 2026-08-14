<?php

namespace App\Http\Controllers\Api\V1\Financial;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Spbu;
use App\Support\ApiResponse;
use App\Support\LaporanKeuangan as LK;
use App\Support\Uang;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Laporan keuangan turunan buku besar — SEMUANYA baca-saja.
 *
 * Setiap baris membawa `kode_akun`, `nama_akun`, `tipe_akun`, dan `coa_id`
 * sehingga tiap angka bisa ditelusuri ke akun COA-nya, lalu didalami lewat
 * Buku Besar hingga ke nomor jurnalnya.
 *
 * Angka dikembalikan sebagai string desimal 6 angka (presisi kolom
 * `jurnal_detail.debit/kredit`), bukan float.
 */
class LaporanController extends Controller
{
    /** Ringkasan modul + daftar akun untuk penyaring. */
    public function ringkasan(Request $request)
    {
        $spbuId = $request->input('spbu_id');
        $per = $request->input('per', Carbon::now()->toDateString());
        $saldo = LK::saldoSampai($spbuId, $per);

        $totalTipe = [];
        foreach (LK::akun() as $a) {
            $nilai = $saldo[$a->coa_id] ?? '0';
            $totalTipe[$a->tipe_akun] = Uang::tambah($totalTipe[$a->tipe_akun] ?? '0', $nilai);
        }

        return ApiResponse::ok([
            'per' => $per,
            'spbu' => Spbu::orderBy('nama_spbu')->get()
                ->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu])->values()->all(),
            'akun' => LK::akun(hanyaAktif: true)->map(fn ($a) => [
                'value' => $a->coa_id,
                'label' => $a->kode_akun . ' — ' . $a->nama_akun,
                'kode_akun' => $a->kode_akun,
            ])->values()->all(),
            'jumlah_jurnal' => \App\Models\Jurnal::when($spbuId, fn ($q) => $q->where('spbu_id', $spbuId))->count(),
            'total_per_tipe' => $totalTipe,
        ]);
    }

    /**
     * Neraca Saldo — alat validasi utama: total debit harus sama dengan
     * total kredit. Perhitungan mengikuti NeracaSaldoController web.
     */
    public function neracaSaldo(Request $request)
    {
        [$spbuId, $dari, $sampai] = $this->rentang($request);

        $awal = LK::saldoSebelum($spbuId, $dari);
        $mutasi = LK::mutasi($spbuId, $dari, $sampai);

        $baris = [];
        $t = array_fill_keys(['awal_d', 'awal_k', 'mut_d', 'mut_k', 'akhir_d', 'akhir_k'], Uang::of(0));

        foreach (LK::akun() as $a) {
            $sAwal = $awal[$a->coa_id] ?? '0';
            $mD = $mutasi[$a->coa_id]['debit'] ?? '0';
            $mK = $mutasi[$a->coa_id]['kredit'] ?? '0';
            $sAkhir = Uang::kurang(Uang::tambah($sAwal, $mD), $mK);

            if (LK::nol($sAwal) && LK::nol($mD) && LK::nol($mK) && LK::nol($sAkhir)) {
                continue; // akun tanpa aktivitas disembunyikan, sama seperti web
            }

            $baris[] = array_merge(LK::baris($a, $sAkhir), [
                'awal_debit' => $this->sisi($sAwal, true),
                'awal_kredit' => $this->sisi($sAwal, false),
                'mutasi_debit' => $mD,
                'mutasi_kredit' => $mK,
                'akhir_debit' => $this->sisi($sAkhir, true),
                'akhir_kredit' => $this->sisi($sAkhir, false),
            ]);

            $t['awal_d'] = Uang::tambah($t['awal_d'], $this->sisi($sAwal, true));
            $t['awal_k'] = Uang::tambah($t['awal_k'], $this->sisi($sAwal, false));
            $t['mut_d'] = Uang::tambah($t['mut_d'], $mD);
            $t['mut_k'] = Uang::tambah($t['mut_k'], $mK);
            $t['akhir_d'] = Uang::tambah($t['akhir_d'], $this->sisi($sAkhir, true));
            $t['akhir_k'] = Uang::tambah($t['akhir_k'], $this->sisi($sAkhir, false));
        }

        $seimbang = LK::nol(Uang::kurang($t['akhir_d'], $t['akhir_k']))
            && LK::nol(Uang::kurang($t['mut_d'], $t['mut_k']));

        return ApiResponse::ok([
            'judul' => 'Neraca Saldo',
            'periode' => $this->labelPeriode($dari, $sampai),
            'spbu' => $this->labelSpbu($spbuId),
            'baris' => $baris,
            'total' => $t,
            'seimbang' => $seimbang,
            'catatan' => empty($baris)
                ? ['nada' => 'info', 'teks' => 'Belum ada jurnal pada periode ini.']
                : ($seimbang
                    ? ['nada' => 'success', 'teks' => 'Neraca saldo seimbang: total debit = total kredit.']
                    : ['nada' => 'danger', 'teks' => 'PERINGATAN: neraca saldo TIDAK seimbang — ada jurnal yang rusak, segera periksa.']),
        ]);
    }

    /** Laba Rugi — mengikuti LabaRugiController web. */
    public function labaRugi(Request $request)
    {
        [$spbuId, $dari, $sampai] = $this->rentang($request);
        $saldo = LK::saldo($spbuId, $dari, $sampai);

        // arah 1: positif di sisi kredit (pendapatan); -1: positif di sisi debit (beban)
        $bagian = function (array $tipe, int $arah) use ($saldo) {
            $baris = [];
            $subtotal = '0';
            foreach (LK::akun($tipe) as $a) {
                $nilai = $saldo[$a->coa_id] ?? '0';
                if ($arah === 1) {
                    $nilai = Uang::kali($nilai, '-1');
                }
                if (LK::nol($nilai)) {
                    continue;
                }
                $baris[] = LK::baris($a, $nilai);
                $subtotal = Uang::tambah($subtotal, $nilai);
            }

            // normalkan agar kontrak API selalu 6 desimal, termasuk saat nol
            return ['baris' => $baris, 'subtotal' => Uang::of($subtotal)];
        };

        $pendapatan = $bagian(['pendapatan'], 1);
        $hpp = $bagian(['hpp'], -1);
        $labaKotor = Uang::kurang($pendapatan['subtotal'], $hpp['subtotal']);

        $beban = $bagian(['beban'], -1);
        $labaOperasional = Uang::kurang($labaKotor, $beban['subtotal']);

        $lainPendapatan = $bagian(['pendapatan_lain'], 1);
        $lainBeban = $bagian(['beban_lain'], -1);
        $labaBersih = Uang::kurang(
            Uang::tambah($labaOperasional, $lainPendapatan['subtotal']),
            $lainBeban['subtotal']
        );

        return ApiResponse::ok([
            'judul' => 'Laba Rugi',
            'periode' => $this->labelPeriode($dari, $sampai),
            'spbu' => $this->labelSpbu($spbuId),
            'bagian' => [
                ['judul' => 'Pendapatan', 'baris' => $pendapatan['baris'], 'subtotal' => $pendapatan['subtotal']],
                ['judul' => 'Harga Pokok Penjualan', 'baris' => $hpp['baris'], 'subtotal' => $hpp['subtotal']],
                ['judul' => 'Beban Operasional', 'baris' => $beban['baris'], 'subtotal' => $beban['subtotal']],
                ['judul' => 'Pendapatan Lain-lain', 'baris' => $lainPendapatan['baris'], 'subtotal' => $lainPendapatan['subtotal']],
                ['judul' => 'Beban Lain-lain', 'baris' => $lainBeban['baris'], 'subtotal' => $lainBeban['subtotal']],
            ],
            'ikhtisar' => [
                ['label' => 'Laba Kotor', 'nilai' => $labaKotor],
                ['label' => 'Laba Operasional', 'nilai' => $labaOperasional],
                ['label' => 'Laba (Rugi) Bersih', 'nilai' => $labaBersih, 'tebal' => true],
            ],
        ]);
    }

    /** Neraca per tanggal — mengikuti NeracaController web. */
    public function neraca(Request $request)
    {
        $spbuId = $request->input('spbu_id');
        $per = $request->input('per', Carbon::now()->toDateString());
        $saldo = LK::saldoSampai($spbuId, $per);

        // laba berjalan = akumulasi akun nominal yang belum ditutup ke laba ditahan
        $labaBerjalan = '0';
        foreach (LK::akun(Coa::TIPE_NOMINAL) as $a) {
            $labaBerjalan = Uang::kurang($labaBerjalan, $saldo[$a->coa_id] ?? '0');
        }

        $bagian = function (array $tipe, int $arah) use ($saldo) {
            $baris = [];
            $subtotal = '0';
            foreach (LK::akun($tipe) as $a) {
                $nilai = $arah === 1 ? ($saldo[$a->coa_id] ?? '0') : Uang::kali($saldo[$a->coa_id] ?? '0', '-1');
                if (LK::nol($nilai)) {
                    continue;
                }
                $baris[] = LK::baris($a, $nilai);
                $subtotal = Uang::tambah($subtotal, $nilai);
            }

            return ['baris' => $baris, 'subtotal' => Uang::of($subtotal)];
        };

        $aset = $bagian(['aset'], 1);
        $kewajiban = $bagian(['kewajiban'], -1);
        $modal = $bagian(['modal'], -1);

        $totalModal = Uang::of($modal['subtotal']);
        if (! LK::nol($labaBerjalan)) {
            $modal['baris'][] = [
                'kode_akun' => '—',
                'nama_akun' => 'Laba (Rugi) Berjalan',
                'tipe_akun' => 'modal',
                'saldo_normal' => 'kredit',
                'coa_id' => null,
                'nilai' => $labaBerjalan,
            ];
            $totalModal = Uang::tambah($totalModal, $labaBerjalan);
        }

        $totalPasiva = Uang::tambah($kewajiban['subtotal'], $totalModal);
        $selisih = Uang::kurang($aset['subtotal'], $totalPasiva);
        $seimbang = LK::nol($selisih);

        return ApiResponse::ok([
            'judul' => 'Neraca',
            'per' => $per,
            'periode' => 'Per ' . Carbon::parse($per)->format('d/m/Y'),
            'spbu' => $this->labelSpbu($spbuId),
            'aset' => ['baris' => $aset['baris'], 'total' => $aset['subtotal']],
            'kewajiban' => ['baris' => $kewajiban['baris'], 'total' => $kewajiban['subtotal']],
            'modal' => ['baris' => $modal['baris'], 'total' => $totalModal],
            'total_pasiva' => $totalPasiva,
            'selisih' => $selisih,
            'seimbang' => $seimbang,
            'catatan' => $seimbang
                ? ['nada' => 'success', 'teks' => 'Neraca seimbang: Aset = Kewajiban + Modal.']
                : ['nada' => 'danger', 'teks' => 'PERINGATAN: Aset tidak sama dengan Kewajiban + Modal. Selisih ' . $selisih . '.'],
        ]);
    }

    /** Buku Besar satu akun — mengikuti BukuBesarController web. */
    public function bukuBesar(Request $request)
    {
        $request->validate(['coa_id' => ['required', 'exists:coa,coa_id']]);
        [$spbuId, $dari, $sampai] = $this->rentang($request);

        $coa = Coa::find($request->input('coa_id'));
        $awal = LK::saldoSebelum($spbuId, $dari)[$coa->coa_id] ?? '0';
        $arah = $coa->saldo_normal === 'debit' ? 1 : -1;

        $saldo = $awal;
        $totalDebit = '0';
        $totalKredit = '0';
        $baris = [];

        foreach (LK::rincianAkun($spbuId, $coa->coa_id, $dari, $sampai) as $m) {
            $saldo = Uang::kurang(Uang::tambah($saldo, $m->debit), $m->kredit);
            $totalDebit = Uang::tambah($totalDebit, $m->debit);
            $totalKredit = Uang::tambah($totalKredit, $m->kredit);

            $baris[] = [
                'tanggal' => $m->tanggal,
                'nomor_jurnal' => $m->nomor_jurnal,
                'tipe_sumber' => $m->tipe_sumber,
                'keterangan' => $m->ket_detail ?: $m->ket_jurnal,
                'debit' => Uang::of($m->debit),
                'kredit' => Uang::of($m->kredit),
                // saldo berjalan ditampilkan mengikuti sisi saldo normal akun
                'saldo' => $arah === 1 ? $saldo : Uang::kali($saldo, '-1'),
            ];
        }

        return ApiResponse::ok([
            'judul' => 'Buku Besar',
            'periode' => $this->labelPeriode($dari, $sampai),
            'spbu' => $this->labelSpbu($spbuId),
            'akun' => [
                'kode_akun' => $coa->kode_akun,
                'nama_akun' => $coa->nama_akun,
                'tipe_akun' => $coa->tipe_akun,
                'saldo_normal' => $coa->saldo_normal,
            ],
            'saldo_awal' => $arah === 1 ? $awal : Uang::kali($awal, '-1'),
            'saldo_akhir' => $arah === 1 ? $saldo : Uang::kali($saldo, '-1'),
            'total_debit' => $totalDebit,
            'total_kredit' => $totalKredit,
            'baris' => $baris,
        ]);
    }

    // --------------------------------------------------------------- privat

    private function rentang(Request $request): array
    {
        $request->validate([
            'spbu_id' => ['nullable', 'exists:spbu,spbu_id'],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
        ]);

        return [
            $request->input('spbu_id'),
            $request->input('dari', Carbon::now()->firstOfMonth()->toDateString()),
            $request->input('sampai', Carbon::now()->endOfMonth()->toDateString()),
        ];
    }

    private function sisi(string $nilai, bool $debit): string
    {
        $positif = Uang::bandingkan($nilai, '0') > 0;

        if ($debit) {
            return $positif ? Uang::of($nilai) : '0.000000';
        }

        return $positif ? '0.000000' : Uang::kali($nilai, '-1');
    }

    private function labelPeriode(string $dari, string $sampai): string
    {
        return Carbon::parse($dari)->format('d/m/Y') . ' s/d ' . Carbon::parse($sampai)->format('d/m/Y');
    }

    private function labelSpbu(?string $spbuId): string
    {
        return $spbuId
            ? (optional(Spbu::find($spbuId))->nama_spbu ?? '-')
            : 'Semua (konsolidasi)';
    }
}
