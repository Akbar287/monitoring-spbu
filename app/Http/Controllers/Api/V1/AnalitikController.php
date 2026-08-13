<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\DetailPenjualan;
use App\Models\DokumenStok;
use App\Models\Losses;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\RencanaPembelian;
use App\Models\StandMeter;
use App\Models\StokInventory;
use App\Support\Akun;
use App\Support\ApiResponse;
use App\Support\LaporanKeuangan as LK;
use App\Support\MobileProfile;
use App\Support\Uang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Beranda analitik untuk stakeholder — dikelompokkan per domain menu.
 *
 * Angka keuangan diambil dari App\Support\LaporanKeuangan (sumber yang sama
 * dengan laporan, sudah diverifikasi identik dengan controller web), bukan
 * dihitung ulang. Nilai uang & volume tetap string desimal 6 angka.
 *
 * Selain angka, tiap domain menyertakan `insight`: kalimat interpretasi yang
 * sudah dihitung (rasio susut, selisih setor, ketahanan stok) agar pembaca
 * tidak perlu menafsirkan sendiri.
 */
class AnalitikController extends Controller
{
    public function index(Request $request)
    {
        $spbu = MobileProfile::spbuUtama($request->user()->member()->first());
        $spbuId = $spbu?->spbu_id;

        $hariIni = Carbon::now()->format('Y-m-d');
        $awalBulan = Carbon::now()->startOfMonth()->format('Y-m-d');
        $akhirBulan = Carbon::now()->endOfMonth()->format('Y-m-d');
        $mulai30 = Carbon::now()->subDays(29)->format('Y-m-d');

        return ApiResponse::ok([
            'per' => $hariIni,
            'spbu' => $spbu ? $spbu->nama_spbu : 'Semua SPBU (konsolidasi)',
            'periode' => ['awal_bulan' => $awalBulan, 'akhir_bulan' => $akhirBulan],
            'domain' => [
                $this->domainPenjualan($spbuId, $hariIni, $awalBulan, $mulai30),
                $this->domainPersediaan($spbuId, $awalBulan, $hariIni, $mulai30),
                $this->domainTotalisator($spbuId, $awalBulan, $hariIni),
                $this->domainProcurement($spbuId),
                $this->domainKeuangan($spbuId, $awalBulan, $akhirBulan, $hariIni),
            ],
        ]);
    }

    // ------------------------------------------------- Penjualan & Setoran

    private function domainPenjualan(?string $spbuId, string $hariIni, string $awalBulan, string $mulai30): array
    {
        $q = fn () => Penjualan::query()->when($spbuId, fn ($x) => $x->where('spbu_id', $spbuId));

        $bulan = $q()->whereBetween('tanggal', [$awalBulan, $hariIni])->get();
        $hari = $q()->where('tanggal', $hariIni)->get();

        $penjualanBulan = Uang::jumlah($bulan, 'total_penjualan');
        $kotakBulan = Uang::jumlah($bulan, 'total_uang_kotak');
        $selisih = Uang::kurang($penjualanBulan, $kotakBulan);

        // tren 12 bulan — nilai penjualan
        $tren = $this->deretBulanan(
            fn ($dari, $sampai) => Uang::of(
                $q()->whereBetween('tanggal', [$dari, $sampai])->sum('total_penjualan')
            )
        );

        $rasio = Uang::bandingkan($penjualanBulan, '0') > 0
            ? Uang::kali(Uang::bagi($selisih, $penjualanBulan), '100')
            : '0';

        $perTahap = [];
        foreach (\App\Models\StatusSetoran::orderBy('nama_status')->get() as $st) {
            $perTahap[] = [
                'label' => $st->nama_status,
                'nilai' => (string) $q()->where('status_setoran_id', $st->status_setoran_id)->count(),
            ];
        }

        return [
            'kunci' => 'penjualan',
            'judul' => 'Penjualan & Setoran',
            'ikon' => 'cash-outline',
            'warna' => 'hijau',
            'kpi' => [
                ['label' => 'Penjualan hari ini', 'nilai' => Uang::jumlah($hari, 'total_penjualan'), 'format' => 'uang'],
                ['label' => 'Penjualan bulan ini', 'nilai' => $penjualanBulan, 'format' => 'uang'],
                ['label' => 'Uang kotak diterima', 'nilai' => $kotakBulan, 'format' => 'uang'],
                ['label' => 'Shift tercatat', 'nilai' => (string) $bulan->count(), 'format' => 'angka'],
            ],
            'chart' => [
                ['jenis' => 'garis', 'judul' => 'Penjualan 12 bulan', 'format' => 'uang', 'data' => $tren],
                ['jenis' => 'batang', 'judul' => 'Setoran per tahap', 'format' => 'angka', 'data' => $perTahap],
            ],
            'insight' => array_values(array_filter([
                Uang::bandingkan($penjualanBulan, '0') > 0 ? [
                    'nada' => abs((float) $rasio) < 0.5 ? 'success' : 'warning',
                    'teks' => 'Selisih setor bulan ini ' . $this->rp($selisih) . ' ('
                        . number_format((float) $rasio, 2) . '% dari penjualan).'
                        . (abs((float) $rasio) < 0.5 ? ' Masih dalam batas wajar.' : ' Perlu ditelusuri.'),
                ] : null,
                $bulan->count() === 0 ? [
                    'nada' => 'info', 'teks' => 'Belum ada setoran tercatat pada bulan berjalan.',
                ] : null,
            ])),
        ];
    }

    // ------------------------------------------------------- Persediaan

    private function domainPersediaan(?string $spbuId, string $awalBulan, string $hariIni, string $mulai30): array
    {
        $produk = Produk::where('is_aktif', true)
            ->when($spbuId, fn ($q) => $q->where('spbu_id', $spbuId))
            ->orderBy('nama_produk')->get();

        $stokPerProduk = [];
        $totalStok = '0';
        foreach ($produk as $p) {
            $inv = $p->stok_inventory()->first();
            $nilai = Uang::of(optional($inv)->stok ?? 0);
            $stokPerProduk[] = ['label' => $p->nama_produk, 'nilai' => $nilai];
            $totalStok = Uang::tambah($totalStok, $nilai);
        }

        $lossBulan = Losses::whereBetween('tanggal', [$awalBulan, $hariIni])->get();
        $susut = Uang::jumlah($lossBulan->where('simbol', 'kurang'), 'jumlah');
        $lebih = Uang::jumlah($lossBulan->where('simbol', 'lebih'), 'jumlah');

        // volume terjual 30 hari -> untuk ketahanan stok
        $liter30 = Uang::of(
            DetailPenjualan::whereHas('penjualan', fn ($q) => $q
                ->when($spbuId, fn ($x) => $x->where('spbu_id', $spbuId))
                ->whereBetween('tanggal', [$mulai30, $hariIni]))->sum('liter')
        );
        $literHarian = Uang::bagi($liter30, '30');
        $hariTahan = Uang::bandingkan($literHarian, '0') > 0
            ? Uang::bagi($totalStok, $literHarian)
            : null;

        $rasioSusut = Uang::bandingkan($liter30, '0') > 0
            ? Uang::kali(Uang::bagi($susut, $liter30), '100')
            : '0';

        $menunggu = DokumenStok::where('is_confirmation', 0)->count();

        return [
            'kunci' => 'persediaan',
            'judul' => 'Manajemen Persediaan',
            'ikon' => 'cube-outline',
            'warna' => 'teal',
            'kpi' => [
                ['label' => 'Total stok', 'nilai' => $totalStok, 'format' => 'liter'],
                ['label' => 'Susut bulan ini', 'nilai' => $susut, 'format' => 'liter'],
                ['label' => 'Lebih bulan ini', 'nilai' => $lebih, 'format' => 'liter'],
                ['label' => 'Dokumen menunggu', 'nilai' => (string) $menunggu, 'format' => 'angka'],
            ],
            'chart' => [
                ['jenis' => 'batang', 'judul' => 'Stok per produk', 'format' => 'liter', 'data' => $stokPerProduk],
            ],
            'insight' => array_values(array_filter([
                $hariTahan ? [
                    'nada' => (float) $hariTahan < 3 ? 'danger' : ((float) $hariTahan < 7 ? 'warning' : 'success'),
                    'teks' => 'Dengan rata-rata penjualan ' . number_format((float) $literHarian, 0) . ' L/hari, '
                        . 'stok saat ini cukup untuk sekitar ' . number_format((float) $hariTahan, 1) . ' hari.',
                ] : null,
                Uang::bandingkan($liter30, '0') > 0 ? [
                    'nada' => (float) $rasioSusut > 0.3 ? 'warning' : 'success',
                    'teks' => 'Susut ' . number_format((float) $rasioSusut, 3) . '% dari volume terjual 30 hari terakhir.',
                ] : null,
                $menunggu > 0 ? [
                    'nada' => 'warning',
                    'teks' => $menunggu . ' dokumen stok belum dikonfirmasi — saldo belum mencerminkan pergerakan itu.',
                ] : null,
            ])),
        ];
    }

    // ------------------------------------------------------ Totalisator

    private function domainTotalisator(?string $spbuId, string $awalBulan, string $hariIni): array
    {
        $terbuka = StandMeter::where('is_confirmation', 0)->count();
        $belumAkhir = StandMeter::where('is_confirmation', 0)
            ->whereRaw('COALESCE(stand_meter_akhir, 0) = 0')->count();

        // liter per produk bulan ini, dari detail penjualan (angka resmi)
        $perProduk = DB::table('detail_penjualan')
            ->join('penjualan', 'penjualan.penjualan_id', 'detail_penjualan.penjualan_id')
            ->join('stand_meter', 'stand_meter.stand_meter_id', 'detail_penjualan.stand_meter_id')
            ->join('nozzle', 'nozzle.nozzle_id', 'stand_meter.nozzle_id')
            ->join('produk', 'produk.produk_id', 'nozzle.produk_id')
            ->whereBetween('penjualan.tanggal', [$awalBulan, $hariIni])
            ->when($spbuId, fn ($q) => $q->where('penjualan.spbu_id', $spbuId))
            ->groupBy('produk.nama_produk')
            ->select('produk.nama_produk', DB::raw('sum(detail_penjualan.liter) as liter'))
            ->orderBy('produk.nama_produk')
            ->get()
            ->map(fn ($r) => ['label' => $r->nama_produk, 'nilai' => Uang::of($r->liter)])
            ->values()->all();

        return [
            'kunci' => 'totalisator',
            'judul' => 'Totalisator',
            'ikon' => 'speedometer-outline',
            'warna' => 'pink',
            'kpi' => [
                ['label' => 'Shift terbuka', 'nilai' => (string) $terbuka, 'format' => 'angka'],
                ['label' => 'Belum diisi akhir', 'nilai' => (string) $belumAkhir, 'format' => 'angka'],
                ['label' => 'Liter bulan ini', 'nilai' => Uang::jumlah($perProduk, 'nilai'), 'format' => 'liter'],
            ],
            'chart' => [
                ['jenis' => 'donat', 'judul' => 'Volume terjual per produk', 'format' => 'liter', 'data' => $perProduk],
            ],
            'insight' => array_values(array_filter([
                $belumAkhir > 0 ? [
                    'nada' => 'warning',
                    'teks' => $belumAkhir . ' shift belum diisi angka meter akhir — penjualannya belum terhitung.',
                ] : null,
            ])),
        ];
    }

    // ------------------------------------------------------ Procurement

    private function domainProcurement(?string $spbuId): array
    {
        $tahap = [];
        foreach (\App\Models\StatusPurchase::orderBy('nama_status')->get() as $st) {
            $tahap[] = [
                'label' => $st->nama_status,
                'nilai' => (string) RencanaPembelian::where('status_purchase_id', $st->status_purchase_id)
                    ->when($spbuId, fn ($q) => $q->where('spbu_id', $spbuId))->count(),
            ];
        }

        $tren = $this->deretBulanan(fn ($dari, $sampai) => Uang::of(
            RencanaPembelian::whereBetween('tanggal_pembelian', [$dari, $sampai])
                ->when($spbuId, fn ($q) => $q->where('spbu_id', $spbuId))
                ->sum('grand_total')
        ));

        $ditolak = RencanaPembelian::where('is_denied', true)
            ->when($spbuId, fn ($q) => $q->where('spbu_id', $spbuId))->count();

        return [
            'kunci' => 'procurement',
            'judul' => 'Procurement',
            'ikon' => 'cart-outline',
            'warna' => 'oranye',
            'kpi' => [
                ['label' => 'Dokumen berjalan', 'nilai' => (string) collect($tahap)->sum(fn ($t) => (int) $t['nilai']), 'format' => 'angka'],
                ['label' => 'Ditolak', 'nilai' => (string) $ditolak, 'format' => 'angka'],
            ],
            'chart' => [
                ['jenis' => 'batang', 'judul' => 'Dokumen per tahap', 'format' => 'angka', 'data' => $tahap],
                ['jenis' => 'garis', 'judul' => 'Nilai pembelian 12 bulan', 'format' => 'uang', 'data' => $tren],
            ],
            'insight' => array_values(array_filter([
                $ditolak > 0 ? ['nada' => 'warning', 'teks' => $ditolak . ' rencana pembelian ditolak dan perlu ditinjau.'] : null,
            ])),
        ];
    }

    // -------------------------------------------------------- Keuangan

    private function domainKeuangan(?string $spbuId, string $awalBulan, string $akhirBulan, string $hariIni): array
    {
        // sumber yang sama dengan laporan keuangan — sudah diverifikasi
        $saldoPeriode = LK::saldo($spbuId, $awalBulan, $akhirBulan);
        $saldoSampai = LK::saldoSampai($spbuId, $hariIni);

        $jumlahTipe = function ($saldo, array $tipe, int $arah) {
            $t = '0';
            foreach (LK::akun($tipe) as $a) {
                $n = $saldo[$a->coa_id] ?? '0';
                $t = Uang::tambah($t, $arah === 1 ? Uang::kali($n, '-1') : $n);
            }
            return $t;
        };

        $pendapatan = $jumlahTipe($saldoPeriode, ['pendapatan'], 1);
        $hpp = $jumlahTipe($saldoPeriode, ['hpp'], -1);
        $beban = $jumlahTipe($saldoPeriode, ['beban'], -1);
        $labaKotor = Uang::kurang($pendapatan, $hpp);
        $labaBersih = Uang::kurang(
            Uang::tambah(Uang::kurang($labaKotor, $beban), $jumlahTipe($saldoPeriode, ['pendapatan_lain'], 1)),
            $jumlahTipe($saldoPeriode, ['beban_lain'], -1)
        );

        // posisi kas & bank per hari ini, mengacu kode akun
        $kas = [];
        foreach ([Akun::KAS_OPERATOR, Akun::KAS_KECIL, Akun::BANK] as $kode) {
            $a = Coa::where('kode_akun', $kode)->first();
            if ($a) {
                $kas[] = [
                    'label' => $a->kode_akun . ' ' . $a->nama_akun,
                    'nilai' => $saldoSampai[$a->coa_id] ?? '0.000000',
                ];
            }
        }

        $marginPersen = Uang::bandingkan($pendapatan, '0') > 0
            ? Uang::kali(Uang::bagi($labaKotor, $pendapatan), '100') : '0';

        return [
            'kunci' => 'keuangan',
            'judul' => 'Keuangan',
            'ikon' => 'stats-chart-outline',
            'warna' => 'biru',
            'kpi' => [
                ['label' => 'Pendapatan bulan ini', 'nilai' => $pendapatan, 'format' => 'uang'],
                ['label' => 'HPP', 'nilai' => $hpp, 'format' => 'uang'],
                ['label' => 'Laba kotor', 'nilai' => $labaKotor, 'format' => 'uang'],
                ['label' => 'Laba bersih', 'nilai' => $labaBersih, 'format' => 'uang'],
            ],
            'chart' => [
                ['jenis' => 'batang', 'judul' => 'Struktur laba rugi bulan ini', 'format' => 'uang', 'data' => [
                    ['label' => 'Pendapatan', 'nilai' => $pendapatan],
                    ['label' => 'HPP', 'nilai' => $hpp],
                    ['label' => 'Beban', 'nilai' => $beban],
                    ['label' => 'Laba bersih', 'nilai' => $labaBersih],
                ]],
                ['jenis' => 'batang', 'judul' => 'Posisi kas & bank', 'format' => 'uang', 'data' => $kas],
            ],
            'insight' => array_values(array_filter([
                Uang::bandingkan($pendapatan, '0') > 0 ? [
                    'nada' => (float) $marginPersen >= 5 ? 'success' : 'warning',
                    'teks' => 'Margin kotor ' . number_format((float) $marginPersen, 2) . '% dari pendapatan bulan ini.',
                ] : ['nada' => 'info', 'teks' => 'Belum ada pendapatan terjurnal pada bulan berjalan.'],
                Uang::bandingkan($labaBersih, '0') < 0 ? [
                    'nada' => 'danger',
                    'teks' => 'Bulan berjalan masih rugi ' . $this->rp(Uang::kali($labaBersih, '-1')) . '.',
                ] : null,
            ])),
            'catatan' => 'Angka diturunkan dari buku besar (jurnal_detail) — sumber yang sama dengan laporan Financial.',
        ];
    }

    // --------------------------------------------------------------- privat

    /** Deret 12 bulan terakhir: [{label: 'Agu 26', nilai: '...'}]. */
    private function deretBulanan(callable $hitung): array
    {
        $hasil = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = Carbon::now()->startOfMonth()->subMonths($i);
            $hasil[] = [
                'label' => $bulan->locale('id')->isoFormat('MMM YY'),
                'nilai' => $hitung($bulan->format('Y-m-d'), $bulan->copy()->endOfMonth()->format('Y-m-d')),
            ];
        }

        return $hasil;
    }

    private function rp(string $n): string
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    }
}
