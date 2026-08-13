<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\JadwalController;
use App\Http\Controllers\Api\V1\KehadiranController;
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
use App\Http\Controllers\Api\V1\Procurement\DeliveryController;
use App\Http\Controllers\Api\V1\Procurement\Ms2Controller;
use App\Http\Controllers\Api\V1\Procurement\PembayaranController;
use App\Http\Controllers\Api\V1\Procurement\PembelianController;
use App\Http\Controllers\Api\V1\Procurement\PerencanaanController;
use App\Http\Controllers\Api\V1\Procurement\ProcurementController;
use App\Http\Controllers\Api\V1\ProfilController;
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
    Route::prefix('procurement')->group(function () {
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
    | Master Data & Konfigurasi — hanya Penanggung Jawab
    |----------------------------------------------------------------------
    | Sepadan dengan menu "Master Data" dan "Konfigurasi" di web, yang juga
    | hanya tampil untuk PJ (App\Support\Navigation). Bedanya, di sini
    | pembatasan benar-benar ditegakkan server lewat middleware `level`.
    */
    Route::middleware('level:Penanggung Jawab')->group(function () use ($crud) {
        // Daftar pilihan dropdown untuk kedua modul — cukup diambil sekali
        Route::get('opsi', OpsiController::class)->name('mobile.opsi');

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
