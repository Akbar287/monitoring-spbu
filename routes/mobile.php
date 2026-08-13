<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AnalitikController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\JadwalController;
use App\Http\Controllers\Api\V1\KehadiranController;
use App\Http\Controllers\Api\V1\Financial\JurnalController as JurnalUmumController;
use App\Http\Controllers\Api\V1\Financial\LaporanController;
use App\Http\Controllers\Api\V1\Konfigurasi\DivisiController;
use App\Http\Controllers\Api\V1\Konfigurasi\HariController;
use App\Http\Controllers\Api\V1\Konfigurasi\JabatanController;
use App\Http\Controllers\Api\V1\Konfigurasi\JamKerjaController;
use App\Http\Controllers\Api\V1\Konfigurasi\LevelController;
use App\Http\Controllers\Api\V1\Konfigurasi\PajakController;
use App\Http\Controllers\Api\V1\Konfigurasi\SpbuController;
use App\Http\Controllers\Api\V1\Master\AsetController;
use App\Http\Controllers\Api\V1\Master\DispenserController;
use App\Http\Controllers\Api\V1\Master\DombakController;
use App\Http\Controllers\Api\V1\Master\FasilitasController;
use App\Http\Controllers\Api\V1\Master\HargaController;
use App\Http\Controllers\Api\V1\Master\LampiranController;
use App\Http\Controllers\Api\V1\Master\MemberController;
use App\Http\Controllers\Api\V1\Master\NozzleController;
use App\Http\Controllers\Api\V1\Master\PayungController;
use App\Http\Controllers\Api\V1\Master\ProdukController;
use App\Http\Controllers\Api\V1\NotifikasiController;
use App\Http\Controllers\Api\V1\OpsiController;
use App\Http\Controllers\Api\V1\Persediaan\MonitoringController;
use App\Http\Controllers\Api\V1\Persediaan\PenerimaanController as TerimaStokController;
use App\Http\Controllers\Api\V1\Persediaan\PersediaanController;
use App\Http\Controllers\Api\V1\Persediaan\StokSummaryController;
use App\Http\Controllers\Api\V1\Persediaan\StokTakingController;
use App\Http\Controllers\Api\V1\Persediaan\StokTransferController;
use App\Http\Controllers\Api\V1\Procurement\DeliveryController;
use App\Http\Controllers\Api\V1\Procurement\Ms2Controller;
use App\Http\Controllers\Api\V1\Procurement\PembayaranController;
use App\Http\Controllers\Api\V1\Procurement\PembelianController;
use App\Http\Controllers\Api\V1\Procurement\PerencanaanController;
use App\Http\Controllers\Api\V1\Procurement\ProcurementController;
use App\Http\Controllers\Api\V1\ProfilController;
use App\Http\Controllers\Api\V1\Setoran\PenarikanController;
use App\Http\Controllers\Api\V1\Setoran\PenjualanDetailController;
use App\Http\Controllers\Api\V1\Setoran\SetoranController;
use App\Http\Controllers\Api\V1\Totalisator\TotalisatorController;
use App\Http\Controllers\Api\V1\ReferensiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API (v1)
|--------------------------------------------------------------------------
|
| Didaftarkan RouteServiceProvider dengan prefix `api/v1` dan middleware
| group `api` (stateless, throttle) — BUKAN `web` seperti routes/api.php.
| Autentikasi memakai token Sanctum: header `Authorization: Bearer <token>`.
|
| Dokumentasi endpoint: MOBILE_API.md
|
*/

/**
 * Lima route CRUD seragam untuk sekumpulan resource. Bentuknya sengaja
 * identik agar klien mobile bisa memakai satu layar generik untuk semuanya
 * (lihat mobile/src/master/registry.js).
 */
$crud = function (string $modul, array $resources): void {
    foreach ($resources as $nama => $controller) {
        Route::get($nama, [$controller, 'index'])->name("mobile.$modul.$nama.index");
        Route::post($nama, [$controller, 'store'])->name("mobile.$modul.$nama.store");
        Route::get($nama . '/{id}', [$controller, 'show'])->name("mobile.$modul.$nama.show");
        Route::put($nama . '/{id}', [$controller, 'update'])->name("mobile.$modul.$nama.update");
        Route::delete($nama . '/{id}', [$controller, 'destroy'])->name("mobile.$modul.$nama.destroy");
    }
};

Route::post('auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('mobile.auth.login');

Route::get('ping', fn () => \App\Support\ApiResponse::ok([
    'app' => config('app.name'),
    'time' => now()->toIso8601String(),
]))->name('mobile.ping');

Route::middleware('auth:sanctum')->group(function () use ($crud) {
    Route::get('auth/me', [AuthController::class, 'me'])->name('mobile.auth.me');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('mobile.auth.logout');
    Route::post('auth/logout-all', [AuthController::class, 'logoutAll'])->name('mobile.auth.logout_all');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('mobile.dashboard');

    Route::get('profil', [ProfilController::class, 'show'])->name('mobile.profil.show');
    Route::put('profil', [ProfilController::class, 'update'])->name('mobile.profil.update');
    Route::post('profil/password', [ProfilController::class, 'updatePassword'])->name('mobile.profil.password');
    Route::post('profil/avatar', [ProfilController::class, 'updateAvatar'])->name('mobile.profil.avatar');

    Route::get('jadwal', [JadwalController::class, 'index'])->name('mobile.jadwal.index');
    Route::get('jadwal/berjalan', [JadwalController::class, 'berjalan'])->name('mobile.jadwal.berjalan');
    Route::get('jadwal/{penjadwalan}', [JadwalController::class, 'show'])->name('mobile.jadwal.show');
    Route::post('jadwal/{penjadwalan}/masuk', [KehadiranController::class, 'masuk'])->name('mobile.kehadiran.masuk');
    Route::post('jadwal/{penjadwalan}/pulang', [KehadiranController::class, 'pulang'])->name('mobile.kehadiran.pulang');

    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('mobile.notifikasi.index');
    Route::get('notifikasi/belum-dibaca', [NotifikasiController::class, 'belumDibaca'])->name('mobile.notifikasi.unread');
    Route::post('notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('mobile.notifikasi.read_all');
    Route::post('notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('mobile.notifikasi.read');

    Route::get('referensi/stok', [ReferensiController::class, 'stok'])->name('mobile.referensi.stok');
    Route::get('referensi/harga', [ReferensiController::class, 'harga'])->name('mobile.referensi.harga');

    /*
    |----------------------------------------------------------------------
    | Procurement — perencanaan sampai delivery
    |----------------------------------------------------------------------
    | Sepadan dengan menu "Procurement" di web, yang terbuka untuk PJ,
    | direksi, dan operator — jadi cukup dijaga autentikasi, tanpa `level`.
    | Seluruh nilai uang dikirim sebagai string 6 desimal (App\Support\Uang).
    */
    Route::prefix('procurement')->middleware('akses.penuh')->group(function () {
        Route::get('ringkasan', [ProcurementController::class, 'ringkasan'])->name('mobile.procurement.ringkasan');

        // Tahap 1 — Perencanaan
        Route::get('perencanaan', [PerencanaanController::class, 'index'])->name('mobile.procurement.perencanaan.index');
        Route::post('perencanaan', [PerencanaanController::class, 'store'])->name('mobile.procurement.perencanaan.store');
        Route::get('perencanaan/{id}', [PerencanaanController::class, 'show'])->name('mobile.procurement.perencanaan.show');
        Route::put('perencanaan/{id}', [PerencanaanController::class, 'update'])->name('mobile.procurement.perencanaan.update');
        Route::delete('perencanaan/{id}', [PerencanaanController::class, 'destroy'])->name('mobile.procurement.perencanaan.destroy');
        Route::post('perencanaan/{id}/konfirmasi', [PerencanaanController::class, 'konfirmasi'])->name('mobile.procurement.perencanaan.konfirmasi');
        Route::post('perencanaan/{id}/produk', [PerencanaanController::class, 'tambahProduk'])->name('mobile.procurement.perencanaan.produk.store');
        Route::put('perencanaan/{id}/produk/{detail}', [PerencanaanController::class, 'ubahProduk'])->name('mobile.procurement.perencanaan.produk.update');
        Route::delete('perencanaan/{id}/produk/{detail}', [PerencanaanController::class, 'hapusProduk'])->name('mobile.procurement.perencanaan.produk.destroy');

        // Tahap 2 — Pembelian
        Route::get('pembelian', [PembelianController::class, 'index'])->name('mobile.procurement.pembelian.index');
        Route::get('pembelian/{id}', [PembelianController::class, 'show'])->name('mobile.procurement.pembelian.show');
        Route::put('pembelian/{id}/produk/{detail}', [PembelianController::class, 'ubahHarga'])->name('mobile.procurement.pembelian.harga');
        Route::put('pembelian/{id}/pajak', [PembelianController::class, 'ubahPajak'])->name('mobile.procurement.pembelian.pajak');
        Route::post('pembelian/{id}/pajak/hitung-ulang', [PembelianController::class, 'hitungUlangPajak'])->name('mobile.procurement.pembelian.pajak.hitung');
        Route::put('pembelian/{id}/lanjut', [PembelianController::class, 'lanjut'])->name('mobile.procurement.pembelian.lanjut');
        Route::put('pembelian/{id}/kembalikan', [PembelianController::class, 'kembalikan'])->name('mobile.procurement.pembelian.kembalikan');

        // Tahap 3 — Pembayaran
        Route::get('pembayaran', [PembayaranController::class, 'index'])->name('mobile.procurement.pembayaran.index');
        Route::get('pembayaran/{id}', [PembayaranController::class, 'show'])->name('mobile.procurement.pembayaran.show');
        Route::post('pembayaran/{id}', [PembayaranController::class, 'store'])->name('mobile.procurement.pembayaran.store');
        Route::put('pembayaran/{id}/lanjut', [PembayaranController::class, 'lanjut'])->name('mobile.procurement.pembayaran.lanjut');
        Route::put('pembayaran/{id}/tolak', [PembayaranController::class, 'tolak'])->name('mobile.procurement.pembayaran.tolak');
        Route::put('pembayaran/{id}/{bayar}', [PembayaranController::class, 'update'])->name('mobile.procurement.pembayaran.update');
        Route::delete('pembayaran/{id}/{bayar}', [PembayaranController::class, 'destroy'])->name('mobile.procurement.pembayaran.destroy');
        Route::put('pembayaran/{id}/{bayar}/konfirmasi', [PembayaranController::class, 'konfirmasi'])->name('mobile.procurement.pembayaran.konfirmasi');
        Route::get('pembayaran/{id}/{bayar}/lampiran', [PembayaranController::class, 'lampiranIndex'])->name('mobile.procurement.pembayaran.lampiran.index');
        Route::post('pembayaran/{id}/{bayar}/lampiran', [PembayaranController::class, 'lampiranStore'])->name('mobile.procurement.pembayaran.lampiran.store');
        Route::delete('pembayaran/{id}/{bayar}/lampiran/{file}', [PembayaranController::class, 'lampiranDestroy'])->name('mobile.procurement.pembayaran.lampiran.destroy');

        // Tahap 4 — MS2
        Route::get('ms2', [Ms2Controller::class, 'index'])->name('mobile.procurement.ms2.index');
        Route::get('ms2/kandidat', [Ms2Controller::class, 'kandidat'])->name('mobile.procurement.ms2.kandidat');
        Route::post('ms2', [Ms2Controller::class, 'store'])->name('mobile.procurement.ms2.store');
        Route::get('ms2/{id}', [Ms2Controller::class, 'show'])->name('mobile.procurement.ms2.show');
        Route::delete('ms2/{id}', [Ms2Controller::class, 'destroy'])->name('mobile.procurement.ms2.destroy');
        Route::post('ms2/{id}/kirim', [Ms2Controller::class, 'kirim'])->name('mobile.procurement.ms2.kirim');

        // Tahap 5 — Delivery
        Route::get('delivery', [DeliveryController::class, 'index'])->name('mobile.procurement.delivery.index');
        Route::get('delivery/{id}', [DeliveryController::class, 'show'])->name('mobile.procurement.delivery.show');
        Route::put('delivery/{id}', [DeliveryController::class, 'ubahCatatan'])->name('mobile.procurement.delivery.update');
        Route::put('delivery/{id}/konfirmasi', [DeliveryController::class, 'konfirmasi'])->name('mobile.procurement.delivery.konfirmasi');
        Route::put('delivery/{id}/selesai', [DeliveryController::class, 'selesai'])->name('mobile.procurement.delivery.selesai');
        Route::get('delivery/{id}/lo', [DeliveryController::class, 'loIndex'])->name('mobile.procurement.delivery.lo.index');
        Route::post('delivery/{id}/lo', [DeliveryController::class, 'loStore'])->name('mobile.procurement.delivery.lo.store');
        Route::delete('delivery/{id}/lo/{lo}', [DeliveryController::class, 'loDestroy'])->name('mobile.procurement.delivery.lo.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Master Data & Konfigurasi — hanya jabatan berakses penuh
    |----------------------------------------------------------------------
    | Daftar jabatannya ada di App\Support\Akses. Berbeda dengan web yang
    | hanya menyembunyikan menu (App\Support\Navigation), di sini pembatasan
    | benar-benar ditegakkan server lewat middleware `akses.penuh`.
    */
    Route::middleware('akses.penuh')->group(function () use ($crud) {
        // Daftar pilihan dropdown untuk kedua modul — cukup diambil sekali
        Route::get('opsi', OpsiController::class)->name('mobile.opsi');

        // Beranda analitik stakeholder — dikelompokkan per domain menu
        Route::get('analitik', [AnalitikController::class, 'index'])->name('mobile.analitik');

        Route::prefix('master')->group(function () use ($crud) {
            // Lampiran/foto — hanya aset & fasilitas yang punya tabel berkas
            Route::get('{jenis}/{id}/lampiran', [LampiranController::class, 'index'])
                ->whereIn('jenis', ['aset', 'fasilitas'])->name('mobile.master.lampiran.index');
            Route::post('{jenis}/{id}/lampiran', [LampiranController::class, 'store'])
                ->whereIn('jenis', ['aset', 'fasilitas'])->name('mobile.master.lampiran.store');
            Route::delete('{jenis}/{id}/lampiran/{lampiran}', [LampiranController::class, 'destroy'])
                ->whereIn('jenis', ['aset', 'fasilitas'])->name('mobile.master.lampiran.destroy');

            // Aksi khusus pegawai
            Route::post('member/{id}/avatar', [MemberController::class, 'unggahAvatar'])->name('mobile.master.member.avatar');
            Route::put('member/{id}/reset-password', [MemberController::class, 'resetPassword'])->name('mobile.master.member.reset');
            Route::post('member/{id}/jabatan', [MemberController::class, 'tambahJabatan'])->name('mobile.master.member.jabatan.store');
            Route::delete('member/{id}/jabatan/{jabatan}', [MemberController::class, 'hapusJabatan'])->name('mobile.master.member.jabatan.destroy');
            Route::post('member/{id}/spbu', [MemberController::class, 'tambahSpbu'])->name('mobile.master.member.spbu.store');
            Route::delete('member/{id}/spbu/{spbu}', [MemberController::class, 'hapusSpbu'])->name('mobile.master.member.spbu.destroy');

            $crud('master', [
                'aset' => AsetController::class,
                'fasilitas' => FasilitasController::class,
                'produk' => ProdukController::class,
                'harga' => HargaController::class,
                'dombak' => DombakController::class,
                'payung' => PayungController::class,
                'dispenser' => DispenserController::class,
                'nozzle' => NozzleController::class,
                'member' => MemberController::class,
            ]);
        });

        /*
        |------------------------------------------------------------------
        | Manajemen Persediaan
        |------------------------------------------------------------------
        | Semua pergerakan stok melewati `dokumen_stok` dan baru memengaruhi
        | saldo setelah dikonfirmasi. Logika bersama ada di
        | App\Support\Persediaan.
        */
        Route::prefix('persediaan')->group(function () {
            Route::get('ringkasan', [PersediaanController::class, 'ringkasan'])->name('mobile.persediaan.ringkasan');
            Route::get('dombak', [PersediaanController::class, 'opsiDombak'])->name('mobile.persediaan.dombak');
            Route::get('dombak/{dombak}/konversi', [PersediaanController::class, 'cariKonversi'])->name('mobile.persediaan.konversi');

            // Monitoring Stok
            Route::get('monitoring', [MonitoringController::class, 'index'])->name('mobile.persediaan.monitoring.index');
            Route::post('monitoring', [MonitoringController::class, 'store'])->name('mobile.persediaan.monitoring.store');
            Route::get('monitoring/{id}', [MonitoringController::class, 'show'])->name('mobile.persediaan.monitoring.show');
            Route::get('monitoring/{id}/riwayat', [MonitoringController::class, 'riwayat'])->name('mobile.persediaan.monitoring.riwayat');
            Route::get('monitoring/{id}/opsi-riwayat', [MonitoringController::class, 'opsiRiwayat'])->name('mobile.persediaan.monitoring.opsi_riwayat');
            Route::post('monitoring/{id}/dombak', [MonitoringController::class, 'tambahDombak'])->name('mobile.persediaan.monitoring.dombak.store');
            Route::delete('monitoring/{id}/dombak/{dombak}', [MonitoringController::class, 'hapusDombak'])->name('mobile.persediaan.monitoring.dombak.destroy');
            Route::post('monitoring/{id}/koreksi', [MonitoringController::class, 'koreksi'])->name('mobile.persediaan.monitoring.koreksi');

            // Stok Taking
            Route::get('stok-taking', [StokTakingController::class, 'index'])->name('mobile.persediaan.taking.index');
            Route::get('stok-taking/riwayat', [StokTakingController::class, 'riwayat'])->name('mobile.persediaan.taking.riwayat');
            Route::post('stok-taking', [StokTakingController::class, 'store'])->name('mobile.persediaan.taking.store');
            Route::get('stok-taking/{id}', [StokTakingController::class, 'show'])->name('mobile.persediaan.taking.show');
            Route::post('stok-taking/{id}/konfirmasi', [StokTakingController::class, 'konfirmasi'])->name('mobile.persediaan.taking.konfirmasi');
            Route::delete('stok-taking/{id}', [StokTakingController::class, 'destroy'])->name('mobile.persediaan.taking.destroy');

            // Penerimaan BBM
            Route::get('penerimaan/lo', [TerimaStokController::class, 'index'])->name('mobile.persediaan.penerimaan.lo');
            Route::get('penerimaan/lo/{id}', [TerimaStokController::class, 'showLo'])->name('mobile.persediaan.penerimaan.lo.show');
            Route::post('penerimaan/lo/{id}', [TerimaStokController::class, 'store'])->name('mobile.persediaan.penerimaan.store');
            Route::get('penerimaan/verifikasi', [TerimaStokController::class, 'verifikasi'])->name('mobile.persediaan.penerimaan.verifikasi');
            Route::get('penerimaan/riwayat', [TerimaStokController::class, 'riwayat'])->name('mobile.persediaan.penerimaan.riwayat');
            Route::get('penerimaan/{id}', [TerimaStokController::class, 'show'])->name('mobile.persediaan.penerimaan.show');
            Route::post('penerimaan/{id}/konfirmasi', [TerimaStokController::class, 'konfirmasi'])->name('mobile.persediaan.penerimaan.konfirmasi');
            Route::delete('penerimaan/{id}', [TerimaStokController::class, 'destroy'])->name('mobile.persediaan.penerimaan.destroy');

            // Stok Transfer
            Route::get('stok-transfer', [StokTransferController::class, 'index'])->name('mobile.persediaan.transfer.index');
            Route::get('stok-transfer/riwayat', [StokTransferController::class, 'riwayat'])->name('mobile.persediaan.transfer.riwayat');
            Route::post('stok-transfer', [StokTransferController::class, 'store'])->name('mobile.persediaan.transfer.store');
            Route::get('stok-transfer/{id}', [StokTransferController::class, 'show'])->name('mobile.persediaan.transfer.show');
            Route::post('stok-transfer/{id}/konfirmasi', [StokTransferController::class, 'konfirmasi'])->name('mobile.persediaan.transfer.konfirmasi');
            Route::delete('stok-transfer/{id}', [StokTransferController::class, 'destroy'])->name('mobile.persediaan.transfer.destroy');

            // Stok Summary
            Route::get('summary/{tanggal}', [StokSummaryController::class, 'show'])->name('mobile.persediaan.summary');
        });

        /*
        |------------------------------------------------------------------
        | Totalisator (stand_meter)
        |------------------------------------------------------------------
        | Buka shift -> isi angka akhir -> konfirmasi. Konfirmasi memotong
        | saldo dombak dan membuat dokumen stok "Good Issue Sales".
        */
        Route::prefix('totalisator')->group(function () {
            Route::get('ringkasan', [TotalisatorController::class, 'ringkasan'])->name('mobile.totalisator.ringkasan');
            Route::get('riwayat', [TotalisatorController::class, 'riwayat'])->name('mobile.totalisator.riwayat');
            Route::get('nozzle/{dombak}', [TotalisatorController::class, 'nozzle'])->name('mobile.totalisator.nozzle');
            Route::get('saran/{nozzle}', [TotalisatorController::class, 'saran'])->name('mobile.totalisator.saran');

            Route::get('/', [TotalisatorController::class, 'index'])->name('mobile.totalisator.index');
            Route::post('/', [TotalisatorController::class, 'store'])->name('mobile.totalisator.store');
            Route::get('{id}', [TotalisatorController::class, 'show'])->name('mobile.totalisator.show');
            Route::put('{id}', [TotalisatorController::class, 'ubah'])->name('mobile.totalisator.update');
            Route::put('{id}/akhir', [TotalisatorController::class, 'akhir'])->name('mobile.totalisator.akhir');
            Route::post('{id}/konfirmasi', [TotalisatorController::class, 'konfirmasi'])->name('mobile.totalisator.konfirmasi');
            Route::delete('{id}', [TotalisatorController::class, 'destroy'])->name('mobile.totalisator.destroy');
        });

        /*
        |------------------------------------------------------------------
        | Setoran
        |------------------------------------------------------------------
        | Open Penjualan -> Penitipan -> Closing -> Setoran Ke Bank -> Selesai.
        | Satu daftar generik melayani semua tahap (parameter `tahap`) dengan
        | pencarian, filter lengkap, dan paginasi seperti halaman web.
        */
        Route::prefix('setoran')->group(function () {
            Route::get('ringkasan', [SetoranController::class, 'ringkasan'])->name('mobile.setoran.ringkasan');

            // bantuan input (didaftarkan sebelum {id} agar tidak tertangkap)
            Route::get('stand-meter-tersedia', [PenjualanDetailController::class, 'standMeterTersedia'])->name('mobile.setoran.sm_tersedia');
            Route::get('petugas/{jabatan}', [PenjualanDetailController::class, 'petugas'])->name('mobile.setoran.petugas');

            Route::get('/', [SetoranController::class, 'index'])->name('mobile.setoran.index');
            Route::post('/', [PenjualanDetailController::class, 'store'])->name('mobile.setoran.store');
            Route::get('{id}', [SetoranController::class, 'show'])->name('mobile.setoran.show');
            Route::delete('{id}', [SetoranController::class, 'destroy'])->name('mobile.setoran.destroy');

            // perpindahan tahap & persetujuan
            Route::post('{id}/maju', [SetoranController::class, 'maju'])->name('mobile.setoran.maju');
            Route::post('{id}/mundur', [SetoranController::class, 'mundur'])->name('mobile.setoran.mundur');
            Route::post('{id}/selesai', [SetoranController::class, 'selesai'])->name('mobile.setoran.selesai');
            Route::post('{id}/persetujuan', [SetoranController::class, 'persetujuan'])->name('mobile.setoran.persetujuan');
            Route::post('{id}/detail/{detail}/konfirmasi', [SetoranController::class, 'konfirmasiDetail'])->name('mobile.setoran.detail.konfirmasi');

            // isi setoran
            Route::post('{id}/stand-meter', [PenjualanDetailController::class, 'tambahStandMeter'])->name('mobile.setoran.sm.store');
            Route::delete('{id}/stand-meter/{detail}', [PenjualanDetailController::class, 'hapusStandMeter'])->name('mobile.setoran.sm.destroy');
            Route::post('{id}/pj', [PenjualanDetailController::class, 'tambahPj'])->name('mobile.setoran.pj.store');
            Route::delete('{id}/pj/{pj}', [PenjualanDetailController::class, 'hapusPj'])->name('mobile.setoran.pj.destroy');

            // setoran ke bank (penarikan) + bukti setor
            Route::get('{id}/bank', [PenarikanController::class, 'index'])->name('mobile.setoran.bank.index');
            Route::post('{id}/bank', [PenarikanController::class, 'store'])->name('mobile.setoran.bank.store');
            Route::get('{id}/bank/{bank}', [PenarikanController::class, 'show'])->name('mobile.setoran.bank.show');
            Route::put('{id}/bank/{bank}', [PenarikanController::class, 'update'])->name('mobile.setoran.bank.update');
            Route::delete('{id}/bank/{bank}', [PenarikanController::class, 'destroy'])->name('mobile.setoran.bank.destroy');
            Route::post('{id}/bank/{bank}/konfirmasi', [PenarikanController::class, 'konfirmasi'])->name('mobile.setoran.bank.konfirmasi');
            Route::get('{id}/bank/{bank}/lampiran', [PenarikanController::class, 'lampiran'])->name('mobile.setoran.bank.lampiran');
            Route::post('{id}/bank/{bank}/lampiran', [PenarikanController::class, 'unggahLampiran'])->name('mobile.setoran.bank.lampiran.store');
            Route::delete('{id}/bank/{bank}/lampiran/{file}', [PenarikanController::class, 'hapusLampiran'])->name('mobile.setoran.bank.lampiran.destroy');
        });

        /*
        |------------------------------------------------------------------
        | Financial — BACA-SAJA
        |------------------------------------------------------------------
        | Seluruh laporan diturunkan dari `jurnal_detail` lewat satu sumber
        | perhitungan bersama (App\Support\LaporanKeuangan) yang bentuk
        | query-nya disalin persis dari controller web.
        |
        | Tidak ada endpoint tulis: pencatatan jurnal hanya boleh lewat
        | App\Support\JurnalPosting yang dipicu transaksi operasional.
        */
        Route::prefix('financial')->group(function () {
            Route::get('ringkasan', [LaporanController::class, 'ringkasan'])->name('mobile.financial.ringkasan');
            Route::get('neraca-saldo', [LaporanController::class, 'neracaSaldo'])->name('mobile.financial.neraca_saldo');
            Route::get('laba-rugi', [LaporanController::class, 'labaRugi'])->name('mobile.financial.laba_rugi');
            Route::get('neraca', [LaporanController::class, 'neraca'])->name('mobile.financial.neraca');
            Route::get('buku-besar', [LaporanController::class, 'bukuBesar'])->name('mobile.financial.buku_besar');

            Route::get('coa', [JurnalUmumController::class, 'coa'])->name('mobile.financial.coa');
            Route::get('jurnal', [JurnalUmumController::class, 'index'])->name('mobile.financial.jurnal.index');
            Route::get('jurnal/{id}', [JurnalUmumController::class, 'show'])->name('mobile.financial.jurnal.show');
        });

        Route::prefix('konfigurasi')->group(function () use ($crud) {
            $crud('konfigurasi', [
                'spbu' => SpbuController::class,
                'divisi' => DivisiController::class,
                'level' => LevelController::class,
                'jabatan' => JabatanController::class,
                'hari' => HariController::class,
                'jam-kerja' => JamKerjaController::class,
                'pajak' => PajakController::class,
            ]);
        });
    });
});
