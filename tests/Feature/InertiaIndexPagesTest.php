<?php

namespace Tests\Feature;

use App\Models\UserLogin;
use Tests\TestCase;

class InertiaIndexPagesTest extends TestCase
{
    private array $pages = [
        '/konfigurasi/spbu',
        '/konfigurasi/divisi',
        '/konfigurasi/hari',
        '/konfigurasi/jam_kerja',
        '/konfigurasi/level',
        '/konfigurasi/jabatan',
        '/master/aset',
        '/master/fasilitas',
        '/master/produk',
        '/master/dombak',
        '/master/payung',
        '/master/dispenser',
        '/master/nozzle',
        '/master/member',
        '/master/harga',
        '/stok/monitoring',
        '/procurement/perencanaan',
        '/procurement/pembelian',
        '/procurement/pembayaran',
        '/procurement/delivery',
        '/notifikasi',
        '/jadwal_absensi/absensi',
        '/master/harga/riwayat',
        '/stok/totalisator',
        '/stok/totalisator/riwayat',
        '/stok/stok-taking',
        '/stok/stok-taking/riwayat',
        '/stok/stok-transfer',
        '/stok/stok-transfer/riwayat',
        '/stok/penerimaan',
        '/stok/penerimaan/riwayat',
        '/stok/penerimaan/verifikasi',
        '/artikel/kategori',
        '/artikel/tag',
        '/artikel/post',
        '/financial/petty-cash',
        '/financial/rekap-pajak',
        '/konfigurasi/pajak',
        '/procurement/ms2',
        '/procurement/ms2/pengiriman',
        '/procurement/perencanaan/ditolak',
        '/setoran/open-penjualan',
        '/setoran/penitipan-setoran',
        '/setoran/closing-penjualan',
        '/setoran/setoran-bank',
        '/kehadiran',
    ];

    /** halaman React non-DataTable: hanya diuji render (tanpa cek JSON Yajra) */
    private array $renderOnly = [
        '/stok/stok-summary',
        '/jadwal_absensi/jadwal',
        '/financial/margin',
        '/financial/laba-rugi',
        '/financial/neraca',
        '/financial/buku-besar',
        '/financial/arus-kas',
        '/financial/perubahan-modal',
        '/jadwal_absensi/laporan_absensi',
    ];

    /**
     * Halaman index yang dimigrasi harus merender Inertia (non-AJAX)…
     */
    public function test_index_pages_render_inertia(): void
    {
        $user = UserLogin::first();
        $this->assertNotNull($user);

        foreach (array_merge($this->pages, $this->renderOnly) as $page) {
            $this->actingAs($user)->get($page)->assertOk()->assertSee('data-page', false);
        }
    }

    /**
     * …dan cabang AJAX Yajra tetap mengembalikan JSON DataTables yang sama.
     */
    public function test_index_pages_still_serve_yajra_json(): void
    {
        $user = UserLogin::first();
        $this->assertNotNull($user);

        foreach ($this->pages as $page) {
            $response = $this->actingAs($user)->get(
                $page . '?draw=1&start=0&length=10&search%5Bvalue%5D=&columns%5B0%5D%5Bdata%5D=action&columns%5B0%5D%5Bname%5D=action&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false',
                ['X-Requested-With' => 'XMLHttpRequest']
            );
            $response->assertOk();
            $response->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        }
    }

    /**
     * Regresi: kunjungan Inertia (router.get filter tanggal) juga membawa
     * X-Requested-With, jadi cabang Yajra tidak boleh menangkapnya —
     * harus tetap merespons Inertia, bukan JSON DataTables.
     */
    public function test_date_filtered_inertia_visit_gets_inertia_response_not_json(): void
    {
        $user = UserLogin::first();
        $this->assertNotNull($user);

        $version = (new \App\Http\Middleware\HandleInertiaRequests())
            ->version(\Illuminate\Http\Request::create('/'));

        $filtered = [
            '/procurement/perencanaan',
            '/procurement/pembelian',
            '/procurement/pembayaran',
            '/procurement/delivery',
            '/notifikasi',
            '/jadwal_absensi/absensi?status_kehadiran_id=all&jam_kerja_id=all&jabatan_id=all',
            '/stok/totalisator?produk_id=all&jam_kerja_id=all',
            '/stok/stok-taking?produkSelected=all',
            '/stok/stok-transfer',
            '/stok/penerimaan/riwayat',
            '/financial/rekap-pajak',
            '/setoran/open-penjualan',
            '/setoran/penitipan-setoran',
            '/setoran/closing-penjualan',
            '/setoran/setoran-bank',
        ];

        foreach ($filtered as $page) {
            $sep = str_contains($page, '?') ? '&' : '?';
            $response = $this->actingAs($user)->get(
                $page . $sep . 'waktu_mulai=2026-06-01&waktu_selesai=2026-06-30',
                [
                    'X-Inertia' => 'true',
                    'X-Inertia-Version' => $version,
                    'X-Requested-With' => 'XMLHttpRequest',
                ]
            );
            $response->assertOk();
            $json = $response->json();
            $this->assertArrayNotHasKey('draw', $json, "$page mengembalikan JSON Yajra ke kunjungan Inertia");
            $this->assertSame('Table/Index', $json['component'] ?? null, "$page tidak merender Table/Index");
        }
    }
}
