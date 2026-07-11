<?php

namespace App\Support;

use App\Models\Coa;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\PajakPembelian;
use App\Models\PeriodeAkuntansi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Satu-satunya pintu masuk pencatatan ke buku besar.
 * Semua laporan keuangan (buku besar, laba rugi, neraca, arus kas,
 * perubahan modal) diturunkan dari jurnal yang diposting lewat kelas ini.
 */
class JurnalPosting
{
    // toleransi pembulatan untuk cek keseimbangan debit = kredit
    private const TOLERANSI = 0.0001;

    /**
     * Posting satu jurnal berimbang.
     *
     * @param  array  $lines  [['kode_akun' => '1-1000', 'debit' => 0, 'kredit' => 0, 'keterangan' => null], ...]
     * @param  string  $tipeSumber  penjualan|penarikan|pembayaran|petty_cash|gaji|losses|penyusutan|stok_taking|saldo_awal|closing|manual
     * @param  string|null  $sumberId  id record sumber; satu (tipeSumber, sumberId) hanya boleh diposting sekali
     */
    public static function post(string $spbuId, string $tanggal, string $tipeSumber, ?string $sumberId, ?string $keterangan, array $lines, ?string $postedBy = null): Jurnal
    {
        if (count($lines) < 2) {
            throw new InvalidArgumentException('Jurnal minimal terdiri dari 2 baris (debit dan kredit).');
        }

        $totalDebit = 0;
        $totalKredit = 0;
        foreach ($lines as $line) {
            $debit = floatval($line['debit'] ?? 0);
            $kredit = floatval($line['kredit'] ?? 0);
            if ($debit < 0 || $kredit < 0) {
                throw new InvalidArgumentException('Nilai debit/kredit tidak boleh negatif; gunakan sisi lawan.');
            }
            if ($debit > 0 && $kredit > 0) {
                throw new InvalidArgumentException('Satu baris jurnal hanya boleh berisi debit saja atau kredit saja.');
            }
            $totalDebit += $debit;
            $totalKredit += $kredit;
        }
        if (abs($totalDebit - $totalKredit) > self::TOLERANSI) {
            throw new InvalidArgumentException(sprintf('Jurnal tidak seimbang: debit %.6f vs kredit %.6f.', $totalDebit, $totalKredit));
        }
        if ($totalDebit <= self::TOLERANSI) {
            throw new InvalidArgumentException('Jurnal bernilai nol tidak boleh diposting.');
        }

        // baris bernilai nol (mis. pajak 0) dibuang agar jurnal tetap bersih
        $lines = array_values(array_filter($lines, fn ($l) => floatval($l['debit'] ?? 0) > 0 || floatval($l['kredit'] ?? 0) > 0));

        $kodeAkun = array_unique(array_column($lines, 'kode_akun'));
        $coa = Coa::whereIn('kode_akun', $kodeAkun)->get()->keyBy('kode_akun');
        foreach ($kodeAkun as $kode) {
            if (! isset($coa[$kode])) {
                throw new RuntimeException("Akun {$kode} tidak ditemukan di COA. Jalankan CoaSeeder atau tambahkan akun tersebut.");
            }
            if ($coa[$kode]->is_header) {
                throw new InvalidArgumentException("Akun {$kode} ({$coa[$kode]->nama_akun}) adalah akun header dan tidak bisa menerima posting.");
            }
            if (! $coa[$kode]->is_aktif) {
                throw new InvalidArgumentException("Akun {$kode} ({$coa[$kode]->nama_akun}) sudah tidak aktif.");
            }
        }

        self::pastikanPeriodeTerbuka($spbuId, $tanggal);

        return DB::transaction(function () use ($spbuId, $tanggal, $tipeSumber, $sumberId, $keterangan, $lines, $postedBy, $coa) {
            if (! is_null($sumberId)) {
                $sudahAda = Jurnal::where('tipe_sumber', $tipeSumber)->where('sumber_id', $sumberId)->lockForUpdate()->first();
                if ($sudahAda) {
                    throw new RuntimeException("Transaksi {$tipeSumber} ini sudah pernah diposting sebagai jurnal {$sudahAda->nomor_jurnal}.");
                }
            }

            $jurnal = Jurnal::create([
                'spbu_id' => $spbuId,
                'nomor_jurnal' => self::nomorBaru($tanggal),
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'tipe_sumber' => $tipeSumber,
                'sumber_id' => $sumberId,
                'is_posted' => true,
                'posted_by' => $postedBy,
            ]);

            foreach ($lines as $i => $line) {
                JurnalDetail::create([
                    'jurnal_id' => $jurnal->jurnal_id,
                    'coa_id' => $coa[$line['kode_akun']]->coa_id,
                    'urutan' => $i + 1,
                    'debit' => floatval($line['debit'] ?? 0),
                    'kredit' => floatval($line['kredit'] ?? 0),
                    'keterangan' => $line['keterangan'] ?? null,
                ]);
            }

            return $jurnal;
        });
    }

    /**
     * Hapus jurnal hasil posting otomatis, dipakai saat transaksi sumber
     * dibatalkan/di-unconfirm. Gagal jika periodenya sudah ditutup.
     */
    public static function batalkan(string $tipeSumber, string $sumberId): void
    {
        $jurnal = Jurnal::where('tipe_sumber', $tipeSumber)->where('sumber_id', $sumberId)->first();
        if (is_null($jurnal)) {
            return;
        }

        self::pastikanPeriodeTerbuka($jurnal->spbu_id, $jurnal->tanggal);
        $jurnal->delete(); // jurnal_detail ikut terhapus (cascade)
    }

    public static function sudahDiposting(string $tipeSumber, string $sumberId): bool
    {
        return Jurnal::where('tipe_sumber', $tipeSumber)->where('sumber_id', $sumberId)->exists();
    }

    public static function pastikanPeriodeTerbuka(string $spbuId, string $tanggal): void
    {
        $tgl = Carbon::parse($tanggal);
        $tutup = PeriodeAkuntansi::where('spbu_id', $spbuId)
            ->where('tahun', $tgl->year)
            ->where('bulan', $tgl->month)
            ->where('is_closed', true)
            ->exists();
        if ($tutup) {
            throw new RuntimeException("Periode {$tgl->format('m-Y')} sudah ditutup; jurnal pada periode ini tidak bisa ditambah atau dibatalkan.");
        }
    }

    public static function nomorBaru(string $tanggal): string
    {
        $prefix = 'JRN-' . Carbon::parse($tanggal)->format('Ym') . '-';
        $terakhir = Jurnal::where('nomor_jurnal', 'like', $prefix . '%')
            ->orderBy('nomor_jurnal', 'desc')
            ->lockForUpdate()
            ->value('nomor_jurnal');
        $urut = is_null($terakhir) ? 1 : intval(substr($terakhir, strlen($prefix))) + 1;

        return $prefix . str_pad($urut, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Baris jurnal sisi debit untuk pembelian BBM, mengikuti pilihan user
     * per pembelian (pajak_pembelian.metode_posting_pajak):
     *
     *  - hpp      : net + PPN + PPBKB masuk Persediaan BBM (terbawa ke HPP saat terjual)
     *  - terpisah : net ke Persediaan, PPN ke PPN Masukan (aset), PPBKB ke Beban PPBKB
     *
     * PPh 22 SELALU diposting ke Beban PPh Final — pajak penghasilan bukan
     * komponen biaya perolehan persediaan menurut standar akuntansi.
     * Sisi kredit (Bank/Utang Usaha) ditambahkan oleh pemanggil.
     */
    public static function barisPembelian(PajakPembelian $pajak): array
    {
        $lines = [];
        if ($pajak->metode_posting_pajak === 'terpisah') {
            $lines[] = ['kode_akun' => Akun::PERSEDIAAN_BBM, 'debit' => floatval($pajak->net_price), 'kredit' => 0, 'keterangan' => 'Harga tebus BBM (net)'];
            $lines[] = ['kode_akun' => Akun::PPN_MASUKAN, 'debit' => floatval($pajak->ppn), 'kredit' => 0, 'keterangan' => 'PPN Masukan pembelian BBM'];
            $lines[] = ['kode_akun' => Akun::BEBAN_PPBKB, 'debit' => floatval($pajak->ppbkb), 'kredit' => 0, 'keterangan' => 'PPBKB pembelian BBM'];
        } else {
            $lines[] = ['kode_akun' => Akun::PERSEDIAAN_BBM, 'debit' => floatval($pajak->net_price) + floatval($pajak->ppn) + floatval($pajak->ppbkb), 'kredit' => 0, 'keterangan' => 'Harga tebus BBM (termasuk PPN & PPBKB)'];
        }
        $lines[] = ['kode_akun' => Akun::BEBAN_PPH_FINAL, 'debit' => floatval($pajak->pph), 'kredit' => 0, 'keterangan' => 'PPh 22 final pembelian BBM'];

        return $lines;
    }
}
