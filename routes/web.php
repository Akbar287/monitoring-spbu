<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false
]);

// Route::get('/upload', [App\Http\Controllers\WelcomeController::class, 'uploadTest'])->name('uploadTest');
// Route::get('/upload', [App\Http\Controllers\DumpController::class, 'uploadDataTotalisatorBaru'])->name('uploadDataTotalisator');
// Route::get('/upload', [App\Http\Controllers\DumpController::class, 'uploadStokTaking'])->name('uploadStokTaking');
// Route::get('/temp', [App\Http\Controllers\DumpController::class, 'temp'])->name('temp');

Route::get('/foo', function () {
    Artisan::call('storage:link');
});

Route::middleware(['auth', 'cache.header'])->group(function ($route) {
    $route->get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    $route->get('/profil', [App\Http\Controllers\HomeController::class, 'profil'])->name('profil');
    $route->get('/profil/edit', [App\Http\Controllers\HomeController::class, 'profil_edit'])->name('profil.edit');
    $route->post('/profil', [App\Http\Controllers\HomeController::class, 'profil_store'])->name('profil.store');
    $route->get('/profil/password', [App\Http\Controllers\HomeController::class, 'profil_edit_password'])->name('profil.edit_password');
    $route->post('/profil/password', [App\Http\Controllers\HomeController::class, 'profil_store_password'])->name('profil.store_password');

    $route->get('/notifikasi', [App\Http\Controllers\HomeController::class, 'notifikasi'])->name('notifikasi');

    // Master Data
    $route->get('/master', App\Http\Controllers\MasterController::class)->name('menu.master');

    $route->get('/master/aset', [App\Http\Controllers\AsetController::class, 'index'])->name('aset.index');
    $route->get('/master/aset/create', [App\Http\Controllers\AsetController::class, 'create'])->name('aset.create');
    $route->post('/master/aset', [App\Http\Controllers\AsetController::class, 'store'])->name('aset.store');
    $route->get('/master/aset/{aset}', [App\Http\Controllers\AsetController::class, 'show'])->name('aset.show');
    $route->get('/master/aset/{aset}/edit', [App\Http\Controllers\AsetController::class, 'edit'])->name('aset.edit');
    $route->put('/master/aset/{aset}', [App\Http\Controllers\AsetController::class, 'update'])->name('aset.update');
    $route->delete('/master/aset/{aset}', [App\Http\Controllers\AsetController::class, 'destroy'])->name('aset.destroy');

    $route->get('/master/aset/{aset}/file', [App\Http\Controllers\FileAsetController::class, 'index'])->name('aset.file.index');
    $route->get('/master/aset/{aset}/file/create', [App\Http\Controllers\FileAsetController::class, 'create'])->name('aset.file.create');
    $route->post('/master/aset/{aset}/file', [App\Http\Controllers\FileAsetController::class, 'store'])->name('aset.file.store');
    $route->get('/master/aset/{aset}/file/{fileAset}', [App\Http\Controllers\FileAsetController::class, 'show'])->name('aset.file.show');
    $route->get('/master/aset/{aset}/file/{fileAset}/unduh', [App\Http\Controllers\FileAsetController::class, 'download'])->name('aset.file.download');
    $route->get('/master/aset/{aset}/file/{fileAset}/edit', [App\Http\Controllers\FileAsetController::class, 'edit'])->name('aset.file.edit');
    $route->put('/master/aset/{aset}/file/{fileAset}', [App\Http\Controllers\FileAsetController::class, 'update'])->name('aset.file.update');
    $route->delete('/master/aset/{aset}/file/{fileAset}', [App\Http\Controllers\FileAsetController::class, 'destroy'])->name('aset.file.delete');

    $route->get('/master/fasilitas', [App\Http\Controllers\FasilitasController::class, 'index'])->name('fasilitas.index');
    $route->get('/master/fasilitas/create', [App\Http\Controllers\FasilitasController::class, 'create'])->name('fasilitas.create');
    $route->post('/master/fasilitas', [App\Http\Controllers\FasilitasController::class, 'store'])->name('fasilitas.store');
    $route->get('/master/fasilitas/{fasilitas}', [App\Http\Controllers\FasilitasController::class, 'show'])->name('fasilitas.show');
    $route->get('/master/fasilitas/{fasilitas}/edit', [App\Http\Controllers\FasilitasController::class, 'edit'])->name('fasilitas.edit');
    $route->put('/master/fasilitas/{fasilitas}', [App\Http\Controllers\FasilitasController::class, 'update'])->name('fasilitas.update');
    $route->delete('/master/fasilitas/{fasilitas}', [App\Http\Controllers\FasilitasController::class, 'destroy'])->name('fasilitas.destroy');

    $route->get('/master/fasilitas/{fasilitas}/file', [App\Http\Controllers\FileFasilitasController::class, 'index'])->name('fasilitas.file.index');
    $route->get('/master/fasilitas/{fasilitas}/file/create', [App\Http\Controllers\FileFasilitasController::class, 'create'])->name('fasilitas.file.create');
    $route->post('/master/fasilitas/{fasilitas}/file', [App\Http\Controllers\FileFasilitasController::class, 'store'])->name('fasilitas.file.store');
    $route->get('/master/fasilitas/{fasilitas}/file/{fileFasilitas}', [App\Http\Controllers\FileFasilitasController::class, 'show'])->name('fasilitas.file.show');
    $route->get('/master/fasilitas/{fasilitas}/file/{fileFasilitas}/unduh', [App\Http\Controllers\FileFasilitasController::class, 'download'])->name('fasilitas.file.download');
    $route->get('/master/fasilitas/{fasilitas}/file/{fileFasilitas}/edit', [App\Http\Controllers\FileFasilitasController::class, 'edit'])->name('fasilitas.file.edit');
    $route->put('/master/fasilitas/{fasilitas}/file/{fileFasilitas}', [App\Http\Controllers\FileFasilitasController::class, 'update'])->name('fasilitas.file.update');
    $route->delete('/master/fasilitas/{fasilitas}/file/{fileFasilitas}', [App\Http\Controllers\FileFasilitasController::class, 'destroy'])->name('fasilitas.file.delete');

    $route->get('/master/produk', [App\Http\Controllers\ProdukController::class, 'index'])->name('produk.index');
    $route->get('/master/produk/create', [App\Http\Controllers\ProdukController::class, 'create'])->name('produk.create');
    $route->post('/master/produk', [App\Http\Controllers\ProdukController::class, 'store'])->name('produk.store');
    $route->get('/master/produk/{produk}', [App\Http\Controllers\ProdukController::class, 'show'])->name('produk.show');
    $route->get('/master/produk/{produk}/edit', [App\Http\Controllers\ProdukController::class, 'edit'])->name('produk.edit');
    $route->put('/master/produk/{produk}', [App\Http\Controllers\ProdukController::class, 'update'])->name('produk.update');
    $route->delete('/master/produk/{produk}', [App\Http\Controllers\ProdukController::class, 'destroy'])->name('produk.destroy');

    $route->get('/master/harga/riwayat', [App\Http\Controllers\HargaController::class, 'riwayat_index'])->name('harga.riwayat.index');
    $route->get('/master/harga/riwayat/{harga}', [App\Http\Controllers\HargaController::class, 'riwayat_show'])->name('harga.riwayat.show');

    $route->get('/master/harga', [App\Http\Controllers\HargaController::class, 'index'])->name('harga.index');
    $route->get('/master/harga/create', [App\Http\Controllers\HargaController::class, 'create'])->name('harga.create');
    $route->post('/master/harga', [App\Http\Controllers\HargaController::class, 'store'])->name('harga.store');
    $route->get('/master/harga/{harga}', [App\Http\Controllers\HargaController::class, 'show'])->name('harga.show');
    $route->get('/master/harga/{harga}/edit', [App\Http\Controllers\HargaController::class, 'edit'])->name('harga.edit');
    $route->put('/master/harga/{harga}', [App\Http\Controllers\HargaController::class, 'update'])->name('harga.update');
    $route->delete('/master/harga/{harga}', [App\Http\Controllers\HargaController::class, 'destroy'])->name('harga.destroy');

    $route->get('/master/member', [App\Http\Controllers\MemberController::class, 'index'])->name('member.index');
    $route->get('/master/member/create', [App\Http\Controllers\MemberController::class, 'create'])->name('member.create');
    $route->post('/master/member', [App\Http\Controllers\MemberController::class, 'store'])->name('member.store');
    $route->get('/master/member/{member}', [App\Http\Controllers\MemberController::class, 'show'])->name('member.show');
    $route->get('/master/member/{member}/edit', [App\Http\Controllers\MemberController::class, 'edit'])->name('member.edit');
    $route->put('/master/member/{member}', [App\Http\Controllers\MemberController::class, 'update'])->name('member.update');
    $route->put('/master/member/{member}/reset', [App\Http\Controllers\MemberController::class, 'reset_pw'])->name('member.reset_pw');
    $route->delete('/master/member/{member}', [App\Http\Controllers\MemberController::class, 'destroy'])->name('member.destroy');

    $route->get('/master/member/{member}/jabatan', [App\Http\Controllers\MemberController::class, 'member_jabatan_index'])->name('member.jabatan.index');
    $route->get('/master/member/{member}/jabatan/create', [App\Http\Controllers\MemberController::class, 'member_jabatan_create'])->name('member.jabatan.create');
    $route->post('/master/member/{member}/jabatan/{jabatan}', [App\Http\Controllers\MemberController::class, 'member_jabatan_store'])->name('member.jabatan.store');
    $route->delete('/master/member/{member}/jabatan/{jabatan}', [App\Http\Controllers\MemberController::class, 'member_jabatan_destroy'])->name('member.jabatan.delete');

    $route->get('/master/dombak', [App\Http\Controllers\DombakController::class, 'index'])->name('dombak.index');
    $route->get('/master/dombak/create', [App\Http\Controllers\DombakController::class, 'create'])->name('dombak.create');
    $route->get('/master/dombak/api', [App\Http\Controllers\DombakController::class, 'api'])->name('dombak.api');
    $route->post('/master/dombak', [App\Http\Controllers\DombakController::class, 'store'])->name('dombak.store');
    $route->get('/master/dombak/{dombak}', [App\Http\Controllers\DombakController::class, 'show'])->name('dombak.show');
    $route->get('/master/dombak/{dombak}/edit', [App\Http\Controllers\DombakController::class, 'edit'])->name('dombak.edit');
    $route->put('/master/dombak/{dombak}', [App\Http\Controllers\DombakController::class, 'update'])->name('dombak.update');
    $route->delete('/master/dombak/{dombak}', [App\Http\Controllers\DombakController::class, 'destroy'])->name('dombak.destroy');

    $route->get('/master/dombak/konversi/api', [App\Http\Controllers\KonversiController::class, 'api'])->name('konversi.api');
    $route->get('/master/dombak/{dombak}/konversi/upload', [App\Http\Controllers\KonversiController::class, 'upload_show'])->name('konversi.upload_show');
    $route->post('/master/dombak/{dombak}/konversi/upload', [App\Http\Controllers\KonversiController::class, 'upload_store'])->name('konversi.upload_store');

    $route->get('/master/dombak/{dombak}/konversi', [App\Http\Controllers\KonversiController::class, 'index'])->name('konversi.index');
    $route->get('/master/dombak/{dombak}/konversi/create', [App\Http\Controllers\KonversiController::class, 'create'])->name('konversi.create');
    $route->delete('/master/dombak/{dombak}/konversi/delete', [App\Http\Controllers\KonversiController::class, 'destroy_all'])->name('konversi.destroy.all');
    $route->post('/master/dombak/{dombak}/konversi', [App\Http\Controllers\KonversiController::class, 'store'])->name('konversi.store');
    $route->get('/master/dombak/{dombak}/konversi/{konversi}', [App\Http\Controllers\KonversiController::class, 'show'])->name('konversi.show');
    $route->get('/master/dombak/{dombak}/konversi/{konversi}/edit', [App\Http\Controllers\KonversiController::class, 'edit'])->name('konversi.edit');
    $route->put('/master/dombak/{dombak}/konversi/{konversi}', [App\Http\Controllers\KonversiController::class, 'update'])->name('konversi.update');
    $route->delete('/master/dombak/{dombak}/konversi/{konversi}', [App\Http\Controllers\KonversiController::class, 'destroy'])->name('konversi.destroy');

    $route->get('/master/payung/{payung}/dombak', [App\Http\Controllers\PayungController::class, 'dombak'])->name('payung.dombak');
    $route->get('/master/payung/{payung}/dombak/create', [App\Http\Controllers\PayungController::class, 'dombak_create'])->name('payung.dombak.create');
    $route->post('/master/payung/{payung}/dombak/{dombak}', [App\Http\Controllers\PayungController::class, 'dombak_store'])->name('payung.dombak.store');
    $route->delete('/master/payung/{payung}/dombak/{dombak}', [App\Http\Controllers\PayungController::class, 'dombak_destroy'])->name('payung.dombak.delete');

    $route->get('/master/payung', [App\Http\Controllers\PayungController::class, 'index'])->name('payung.index');
    $route->get('/master/payung/create', [App\Http\Controllers\PayungController::class, 'create'])->name('payung.create');
    $route->post('/master/payung', [App\Http\Controllers\PayungController::class, 'store'])->name('payung.store');
    $route->get('/master/payung/{payung}', [App\Http\Controllers\PayungController::class, 'show'])->name('payung.show');
    $route->get('/master/payung/{payung}/edit', [App\Http\Controllers\PayungController::class, 'edit'])->name('payung.edit');
    $route->put('/master/payung/{payung}', [App\Http\Controllers\PayungController::class, 'update'])->name('payung.update');
    $route->delete('/master/payung/{payung}', [App\Http\Controllers\PayungController::class, 'destroy'])->name('payung.destroy');

    $route->get('/master/dispenser', [App\Http\Controllers\DispenserController::class, 'index'])->name('dispenser.index');
    $route->get('/master/dispenser/create', [App\Http\Controllers\DispenserController::class, 'create'])->name('dispenser.create');
    $route->post('/master/dispenser', [App\Http\Controllers\DispenserController::class, 'store'])->name('dispenser.store');
    $route->get('/master/dispenser/{dispenser}', [App\Http\Controllers\DispenserController::class, 'show'])->name('dispenser.show');
    $route->get('/master/dispenser/{dispenser}/edit', [App\Http\Controllers\DispenserController::class, 'edit'])->name('dispenser.edit');
    $route->put('/master/dispenser/{dispenser}', [App\Http\Controllers\DispenserController::class, 'update'])->name('dispenser.update');
    $route->delete('/master/dispenser/{dispenser}', [App\Http\Controllers\DispenserController::class, 'destroy'])->name('dispenser.destroy');

    $route->get('/master/nozzle', [App\Http\Controllers\NozzleController::class, 'index'])->name('nozzle.index');
    $route->get('/master/nozzle/create', [App\Http\Controllers\NozzleController::class, 'create'])->name('nozzle.create');
    $route->post('/master/nozzle', [App\Http\Controllers\NozzleController::class, 'store'])->name('nozzle.store');
    $route->get('/master/nozzle/{nozzle}', [App\Http\Controllers\NozzleController::class, 'show'])->name('nozzle.show');
    $route->get('/master/nozzle/{nozzle}/edit', [App\Http\Controllers\NozzleController::class, 'edit'])->name('nozzle.edit');
    $route->put('/master/nozzle/{nozzle}', [App\Http\Controllers\NozzleController::class, 'update'])->name('nozzle.update');
    $route->delete('/master/nozzle/{nozzle}', [App\Http\Controllers\NozzleController::class, 'destroy'])->name('nozzle.destroy');

    //Procurement
    $route->get('/procurement', App\Http\Controllers\ProcurementController::class)->name('menu.procurement');

    $route->get('/procurement/perencanaan', [App\Http\Controllers\RencanaPembelianController::class, 'index'])->name('perencanaan.index');
    $route->get('/procurement/perencanaan/create', [App\Http\Controllers\RencanaPembelianController::class, 'create'])->name('perencanaan.create');
    $route->get('/procurement/perencanaan/ditolak', [App\Http\Controllers\RencanaPembelianController::class, 'index_ditolak'])->name('perencanaan.ditolak');
    $route->get('/procurement/perencanaan/{rencana}/ditolak', [App\Http\Controllers\RencanaPembelianController::class, 'show_ditolak'])->name('perencanaan.ditolak.show');
    $route->post('/procurement/perencanaan', [App\Http\Controllers\RencanaPembelianController::class, 'store'])->name('perencanaan.store');
    $route->get('/procurement/perencanaan/{rencana}', [App\Http\Controllers\RencanaPembelianController::class, 'show'])->name('perencanaan.show');
    $route->get('/procurement/perencanaan/{rencana}/edit', [App\Http\Controllers\RencanaPembelianController::class, 'edit'])->name('perencanaan.edit');
    $route->put('/procurement/perencanaan/{rencana}', [App\Http\Controllers\RencanaPembelianController::class, 'update'])->name('perencanaan.update');
    $route->delete('/procurement/perencanaan/{rencana}', [App\Http\Controllers\RencanaPembelianController::class, 'destroy'])->name('perencanaan.destroy');
    $route->get('/procurement/perencanaan/{rencana}/konfirmasi', [App\Http\Controllers\RencanaPembelianController::class, 'konfirmasi'])->name('perencanaan.konfirmasi');
    $route->put('/procurement/perencanaan/{rencana}/konfirmasi', [App\Http\Controllers\RencanaPembelianController::class, 'konfirmasi_update'])->name('perencanaan.konfirmasi.update');

    $route->get('/procurement/perencanaan/{rencana}/produk', [App\Http\Controllers\DetailRencanaPembelianController::class, 'index'])->name('perencanaan.detail.index');
    $route->get('/procurement/perencanaan/{rencana}/produk/create', [App\Http\Controllers\DetailRencanaPembelianController::class, 'create'])->name('perencanaan.detail.create');
    $route->post('/procurement/perencanaan/{rencana}/produk', [App\Http\Controllers\DetailRencanaPembelianController::class, 'store'])->name('perencanaan.detail.store');
    $route->get('/procurement/perencanaan/{rencana}/produk/{detail}', [App\Http\Controllers\DetailRencanaPembelianController::class, 'show'])->name('perencanaan.detail.show');
    $route->get('/procurement/perencanaan/{rencana}/produk/{detail}/edit', [App\Http\Controllers\DetailRencanaPembelianController::class, 'edit'])->name('perencanaan.detail.edit');
    $route->put('/procurement/perencanaan/{rencana}/produk/{detail}', [App\Http\Controllers\DetailRencanaPembelianController::class, 'update'])->name('perencanaan.detail.update');
    $route->delete('/procurement/perencanaan/{rencana}/produk/{detail}', [App\Http\Controllers\DetailRencanaPembelianController::class, 'destroy'])->name('perencanaan.detail.destroy');

    $route->get('/procurement/pembelian', [App\Http\Controllers\PembelianController::class, 'index'])->name('pembelian.index');
    $route->get('/procurement/pembelian/{pembelian}/harga', [App\Http\Controllers\PembelianController::class, 'konfirmasi_harga'])->name('pembelian.konfirmasi');
    $route->get('/procurement/pembelian/{pembelian}/ulang', [App\Http\Controllers\PembelianController::class, 'konfirmasi_ulang'])->name('pembelian.perencanaan');
    $route->get('/procurement/pembelian/{pembelian}/detail', [App\Http\Controllers\PembelianController::class, 'show'])->name('pembelian.show');

    $route->get('/procurement/pembelian/{pembelian}/produk/pajak', [App\Http\Controllers\DetailPembelianController::class, 'edit_pajak'])->name('pembelian.detail.edit_pajak');
    $route->put('/procurement/pembelian/{pembelian}/produk/pajak', [App\Http\Controllers\DetailPembelianController::class, 'update_pajak'])->name('pembelian.detail.update_pajak');

    $route->get('/procurement/pembelian/{pembelian}/produk', [App\Http\Controllers\DetailPembelianController::class, 'index'])->name('pembelian.detail.index');
    $route->get('/procurement/pembelian/{pembelian}/produk/{detail}', [App\Http\Controllers\DetailPembelianController::class, 'show'])->name('pembelian.detail.show');
    $route->get('/procurement/pembelian/{pembelian}/produk/{detail}/edit', [App\Http\Controllers\DetailPembelianController::class, 'edit'])->name('pembelian.detail.edit');
    $route->put('/procurement/pembelian/{pembelian}/produk/{detail}', [App\Http\Controllers\DetailPembelianController::class, 'update'])->name('pembelian.detail.update');

    $route->get('/procurement/pembayaran', [App\Http\Controllers\PembayaranController::class, 'index'])->name('pembayaran.index');
    $route->get('/procurement/pembayaran/{rencana}', [App\Http\Controllers\PembayaranController::class, 'show'])->name('pembayaran.show');
    $route->put('/procurement/pembayaran/{rencana}/delivery', [App\Http\Controllers\PembayaranController::class, 'konfirmasi_delivery'])->name('pembayaran.delivery');
    $route->put('/procurement/pembayaran/{rencana}/ditolak', [App\Http\Controllers\PembayaranController::class, 'konfirmasi_ditolak'])->name('pembayaran.ditolak');

    $route->get('/procurement/pembayaran/{rencana}/pajak', [App\Http\Controllers\PembayaranController::class, 'pajak_edit'])->name('pembayaran.pajak_edit');
    $route->put('/procurement/pembayaran/{rencana}/pajak', [App\Http\Controllers\PembayaranController::class, 'pajak_update'])->name('pembayaran.pajak_update');

    $route->get('/procurement/pembayaran/{rencana}/tambah', [App\Http\Controllers\PembayaranController::class, 'create'])->name('pembayaran.create');
    $route->post('/procurement/pembayaran/{rencana}', [App\Http\Controllers\PembayaranController::class, 'store'])->name('pembayaran.store');
    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}', [App\Http\Controllers\PembayaranController::class, 'detail'])->name('pembayaran.detail');
    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}/edit', [App\Http\Controllers\PembayaranController::class, 'edit'])->name('pembayaran.edit');
    $route->put('/procurement/pembayaran/{rencana}/{pembayaran}', [App\Http\Controllers\PembayaranController::class, 'update'])->name('pembayaran.update');

    $route->put('/procurement/pembayaran/{rencana}/{pembayaran}/confirmation_admin_ok', [App\Http\Controllers\PembayaranController::class, 'update_confirmation_admin_ok'])->name('pembayaran.update.confirmation_admin_ok');
    $route->put('/procurement/pembayaran/{rencana}/{pembayaran}/confirmation_admin_no', [App\Http\Controllers\PembayaranController::class, 'update_confirmation_admin_no'])->name('pembayaran.update.confirmation_admin_no');
    $route->put('/procurement/pembayaran/{rencana}/{pembayaran}/confirmation_top_ok', [App\Http\Controllers\PembayaranController::class, 'update_confirmation_top_ok'])->name('pembayaran.update.confirmation_top_ok');
    $route->put('/procurement/pembayaran/{rencana}/{pembayaran}/confirmation_top_no', [App\Http\Controllers\PembayaranController::class, 'update_confirmation_top_no'])->name('pembayaran.update.confirmation_top_no');

    $route->delete('/procurement/pembayaran/{rencana}/{pembayaran}', [App\Http\Controllers\PembayaranController::class, 'destroy'])->name('pembayaran.destroy');

    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}/tambah', [App\Http\Controllers\PembayaranController::class, 'file_create'])->name('pembayaran.file.create');
    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}/{file}/unduh', [App\Http\Controllers\PembayaranController::class, 'file_unduh'])->name('pembayaran.file.unduh');
    $route->post('/procurement/pembayaran/{rencana}/{pembayaran}', [App\Http\Controllers\PembayaranController::class, 'file_store'])->name('pembayaran.file.store');
    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}/{file}', [App\Http\Controllers\PembayaranController::class, 'file_show'])->name('pembayaran.file.show');
    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}/{file}/edit', [App\Http\Controllers\PembayaranController::class, 'file_edit'])->name('pembayaran.file.edit');
    $route->put('/procurement/pembayaran/{rencana}/{pembayaran}/{file}/', [App\Http\Controllers\PembayaranController::class, 'file_update'])->name('pembayaran.file.update');
    $route->delete('/procurement/pembayaran/{rencana}/{pembayaran}/{file}', [App\Http\Controllers\PembayaranController::class, 'file_destroy'])->name('pembayaran.file.destroy');

    $route->get('/procurement/ms2/pengiriman', [App\Http\Controllers\Ms2Controller::class, 'index_pengiriman'])->name('ms2.pengiriman');
    $route->get('/procurement/ms2/pengiriman/tambah', [App\Http\Controllers\Ms2Controller::class, 'create_pengiriman'])->name('ms2.pengiriman.create');
    $route->post('/procurement/ms2/pengiriman/', [App\Http\Controllers\Ms2Controller::class, 'store_pengiriman'])->name('ms2.pengiriman.store');
    $route->get('/procurement/ms2/pengiriman/{ms2}', [App\Http\Controllers\Ms2Controller::class, 'show_pengiriman'])->name('ms2.pengiriman.show');
    $route->delete('/procurement/ms2/pengiriman/{ms2}', [App\Http\Controllers\Ms2Controller::class, 'destroy_pengiriman'])->name('ms2.pengiriman.delete');
    $route->get('/procurement/ms2/pengiriman/{ms2}/konfirm', [App\Http\Controllers\Ms2Controller::class, 'konfirm_pengiriman'])->name('ms2.pengiriman.konfirm_pengiriman');
    $route->put('/procurement/ms2/pengiriman/{ms2}/update-pengiriman', [App\Http\Controllers\Ms2Controller::class, 'update_pengiriman_to_delivery'])->name('ms2.pengiriman.update_pengiriman_to_delivery');
    $route->get('/procurement/ms2', [App\Http\Controllers\Ms2Controller::class, 'index_produk'])->name('ms2.produk.index');
    $route->get('/procurement/ms2/{produk}', [App\Http\Controllers\Ms2Controller::class, 'show_produk'])->name('ms2.produk.show');
    $route->get('/procurement/ms2/{produk}/{detail}', [App\Http\Controllers\Ms2Controller::class, 'show_detail'])->name('ms2.detail.show');

    $route->get('/procurement/delivery/{pengiriman}/file_lo_show/{file_lo}/lampiran', [App\Http\Controllers\FileLampiranLoController::class, 'index'])->name('pengiriman.file_lo_show.lampiran.index');
    $route->get('/procurement/delivery/{pengiriman}/file_lo_show/{file_lo}/lampiran/create', [App\Http\Controllers\FileLampiranLoController::class, 'create'])->name('pengiriman.file_lo_show.lampiran.create');
    $route->post('/procurement/delivery/{pengiriman}/file_lo_show/{file_lo}/lampiran', [App\Http\Controllers\FileLampiranLoController::class, 'store'])->name('pengiriman.file_lo_show.lampiran.store');
    $route->get('/procurement/delivery/{pengiriman}/file_lo_show/{file_lo}/lampiran/{fileLampiranLo}/unduh', [App\Http\Controllers\FileLampiranLoController::class, 'unduh'])->name('pengiriman.file_lo_show.lampiran.unduh');
    $route->get('/procurement/delivery/{pengiriman}/file_lo_show/{file_lo}/lampiran/{fileLampiranLo}', [App\Http\Controllers\FileLampiranLoController::class, 'show'])->name('pengiriman.file_lo_show.lampiran.show');
    $route->get('/procurement/delivery/{pengiriman}/file_lo_show/{file_lo}/lampiran/{fileLampiranLo}/edit', [App\Http\Controllers\FileLampiranLoController::class, 'edit'])->name('pengiriman.file_lo_show.lampiran.edit');
    $route->put('/procurement/delivery/{pengiriman}/file_lo_show/{file_lo}/lampiran/{fileLampiranLo}', [App\Http\Controllers\FileLampiranLoController::class, 'update'])->name('pengiriman.file_lo_show.lampiran.update');
    $route->delete('/procurement/delivery/{pengiriman}/file_lo_show/{file_lo}/lampiran/{fileLampiranLo}', [App\Http\Controllers\FileLampiranLoController::class, 'destroy'])->name('pengiriman.file_lo_show.lampiran.destroy');

    $route->get('/procurement/delivery/{pengiriman}/file_lo_create', [App\Http\Controllers\PengirimanController::class, 'file_lo_create'])->name('pengiriman.file_lo_create');
    $route->post('/procurement/delivery/{pengiriman}/file_lo_store', [App\Http\Controllers\PengirimanController::class, 'file_lo_store'])->name('pengiriman.file_lo_store');
    $route->get('/procurement/delivery/{pengiriman}/file_lo_show/{file_lo}', [App\Http\Controllers\PengirimanController::class, 'show_file_lo'])->name('pengiriman.file_lo.show');
    $route->delete('/procurement/delivery/{pengiriman}/file_lo_show/{file_lo}', [App\Http\Controllers\PengirimanController::class, 'delete_file_lo'])->name('pengiriman.file_lo.delete');

    $route->get('/procurement/delivery/{pengiriman}/catatan', [App\Http\Controllers\PengirimanController::class, 'ubah_catatan'])->name('pengiriman.ubah_catatan');
    $route->put('/procurement/delivery/{pengiriman}/catatan', [App\Http\Controllers\PengirimanController::class, 'update_catatan'])->name('pengiriman.update_catatan');

    $route->get('/procurement/delivery', [App\Http\Controllers\PengirimanController::class, 'index'])->name('pengiriman.index');
    $route->get('/procurement/delivery/{pengiriman}', [App\Http\Controllers\PengirimanController::class, 'show'])->name('pengiriman.show');
    $route->put('/procurement/delivery/{pengiriman}/done', [App\Http\Controllers\PengirimanController::class, 'done'])->name('pengiriman.done');
    $route->put('/procurement/delivery/{pengiriman}/admin/top', [App\Http\Controllers\PengirimanController::class, 'admin_top'])->name('pengiriman.admin_top');
    $route->put('/procurement/delivery/{pengiriman}/admin/down', [App\Http\Controllers\PengirimanController::class, 'admin_down'])->name('pengiriman.admin_down');
    $route->put('/procurement/delivery/{pengiriman}/top/top', [App\Http\Controllers\PengirimanController::class, 'top_top'])->name('pengiriman.top_top');
    $route->put('/procurement/delivery/{pengiriman}/top/down', [App\Http\Controllers\PengirimanController::class, 'top_down'])->name('pengiriman.top_down');
    $route->delete('/procurement/delivery/{pengiriman}', [App\Http\Controllers\PengirimanController::class, 'destroy'])->name('pengiriman.destroy');

    // Manajemen Persediaan
    $route->get('/stok', App\Http\Controllers\StokController::class)->name('menu.stok');

    $route->get('/stok/monitoring/{stok}/riwayat', [App\Http\Controllers\MonitoringController::class, 'show_riwayat'])->name('monitoring.show_riwayat');
    $route->get('/stok/monitoring/{stok}/riwayat/{dokumenStok}', [App\Http\Controllers\MonitoringController::class, 'show_detail_riwayat'])->name('monitoring.show_detail_riwayat');

    $route->get('/stok/monitoring', [App\Http\Controllers\MonitoringController::class, 'index'])->name('monitoring.index');
    $route->get('/stok/monitoring/create', [App\Http\Controllers\MonitoringController::class, 'create'])->name('monitoring.create');
    $route->post('/stok/monitoring', [App\Http\Controllers\MonitoringController::class, 'store'])->name('monitoring.store');
    $route->get('/stok/monitoring/{stok}', [App\Http\Controllers\MonitoringController::class, 'show'])->name('monitoring.show');
    $route->get('/stok/monitoring/{stok}/edit', [App\Http\Controllers\MonitoringController::class, 'edit'])->name('monitoring.edit');
    $route->put('/stok/monitoring/{stok}', [App\Http\Controllers\MonitoringController::class, 'update'])->name('monitoring.update');
    $route->delete('/stok/monitoring/{stok}', [App\Http\Controllers\MonitoringController::class, 'destroy'])->name('monitoring.delete');

    $route->get('/stok/monitoring/{stok}/dombak', [App\Http\Controllers\MonitoringController::class, 'dombak_index'])->name('monitoring.dombak.index');
    $route->get('/stok/monitoring/{stok}/dombak/create', [App\Http\Controllers\MonitoringController::class, 'dombak_create'])->name('monitoring.dombak.create');
    $route->post('/stok/monitoring/{stok}/dombak/{dombak}', [App\Http\Controllers\MonitoringController::class, 'dombak_store'])->name('monitoring.dombak.store');
    $route->delete('/stok/monitoring/{stok}/dombak/{dombak}', [App\Http\Controllers\MonitoringController::class, 'dombak_destroy'])->name('monitoring.dombak.delete');

    $route->get('/stok/penerimaan/riwayat', [App\Http\Controllers\PenerimaanController::class, 'riwayat'])->name('penerimaan.riwayat');
    $route->get('/stok/penerimaan/riwayat/{penerimaan}', [App\Http\Controllers\PenerimaanController::class, 'riwayat_show'])->name('penerimaan.riwayat.show');

    $route->get('/stok/penerimaan/verifikasi', [App\Http\Controllers\PenerimaanController::class, 'verifikasi'])->name('penerimaan.verifikasi');
    $route->get('/stok/penerimaan/verifikasi/{penerimaan}', [App\Http\Controllers\PenerimaanController::class, 'verifikasi_show'])->name('penerimaan.verifikasi.show');

    $route->get('/stok/penerimaan', [App\Http\Controllers\PenerimaanController::class, 'index'])->name('penerimaan.index');
    $route->get('/stok/penerimaan/{fileLo}', [App\Http\Controllers\PenerimaanController::class, 'show'])->name('penerimaan.show');
    $route->post('/stok/penerimaan/{fileLo}', [App\Http\Controllers\PenerimaanController::class, 'store'])->name('penerimaan.store');
    $route->get('/stok/penerimaan/{penerimaan}/show', [App\Http\Controllers\PenerimaanController::class, 'show_penerimaan'])->name('penerimaan.show_penerimaan');
    $route->put('/stok/penerimaan/{penerimaan}/show', [App\Http\Controllers\PenerimaanController::class, 'confirmation'])->name('penerimaan.show_confirmation');
    $route->delete('/stok/penerimaan/{penerimaan}', [App\Http\Controllers\PenerimaanController::class, 'destroy'])->name('penerimaan.delete');

    $route->get('/stok/penerimaan/riwayat/{penerimaan}/file', [App\Http\Controllers\FilePenerimaanController::class, 'index_riwayat'])->name('penerimaan.file.riwayat.index');
    $route->get('/stok/penerimaan/riwayat/{penerimaan}/file/{file}', [App\Http\Controllers\FilePenerimaanController::class, 'show_riwayat'])->name('penerimaan.file.riwayat.show');

    $route->get('/stok/penerimaan/verifikasi/{penerimaan}/file', [App\Http\Controllers\FilePenerimaanController::class, 'index'])->name('penerimaan.file.index');
    $route->get('/stok/penerimaan/verifikasi/{penerimaan}/file/create', [App\Http\Controllers\FilePenerimaanController::class, 'create'])->name('penerimaan.file.create');
    $route->post('/stok/penerimaan/verifikasi/{penerimaan}/file', [App\Http\Controllers\FilePenerimaanController::class, 'store'])->name('penerimaan.file.store');
    $route->get('/stok/penerimaan/verifikasi/{penerimaan}/file/{file}', [App\Http\Controllers\FilePenerimaanController::class, 'show'])->name('penerimaan.file.show');
    $route->get('/stok/penerimaan/verifikasi/{penerimaan}/file/{file}/download', [App\Http\Controllers\FilePenerimaanController::class, 'download'])->name('penerimaan.file.download');
    $route->get('/stok/penerimaan/verifikasi/{penerimaan}/file/{file}/edit', [App\Http\Controllers\FilePenerimaanController::class, 'edit'])->name('penerimaan.file.edit');
    $route->put('/stok/penerimaan/verifikasi/{penerimaan}/file/{file}', [App\Http\Controllers\FilePenerimaanController::class, 'update'])->name('penerimaan.file.update');
    $route->delete('/stok/penerimaan/verifikasi/{penerimaan}/file/{file}', [App\Http\Controllers\FilePenerimaanController::class, 'destroy'])->name('penerimaan.file.delete');

    $route->get('/stok/stok-transfer/riwayat', [App\Http\Controllers\StokTransferController::class, 'riwayat'])->name('stok-transfer.riwayat');
    $route->get('/stok/stok-transfer/riwayat/{stok}', [App\Http\Controllers\StokTransferController::class, 'riwayat_show'])->name('stok-transfer.riwayat.show');

    $route->get('/stok/stok-transfer', [App\Http\Controllers\StokTransferController::class, 'index'])->name('stok-transfer.index');
    $route->get('/stok/stok-transfer/create', [App\Http\Controllers\StokTransferController::class, 'create'])->name('stok-transfer.create');
    $route->post('/stok/stok-transfer', [App\Http\Controllers\StokTransferController::class, 'store'])->name('stok-transfer.store');
    $route->get('/stok/stok-transfer/{dombakTransfer}', [App\Http\Controllers\StokTransferController::class, 'show'])->name('stok-transfer.show');
    $route->put('/stok/stok-transfer/{dombakTransfer}/confirmation', [App\Http\Controllers\StokTransferController::class, 'confirmation_stok_transfer'])->name('stok-transfer.confirmation');
    $route->get('/stok/stok-transfer/{dombakTransfer}/edit', [App\Http\Controllers\StokTransferController::class, 'edit'])->name('stok-transfer.edit');
    $route->put('/stok/stok-transfer/{dombakTransfer}', [App\Http\Controllers\StokTransferController::class, 'update'])->name('stok-transfer.update');
    $route->delete('/stok/stok-transfer/{dombakTransfer}', [App\Http\Controllers\StokTransferController::class, 'destroy'])->name('stok-transfer.delete');

    $route->get('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file', [App\Http\Controllers\FileStokTransferController::class, 'index'])->name('stok-transfer.file.index');
    $route->get('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/create', [App\Http\Controllers\FileStokTransferController::class, 'create'])->name('stok-transfer.file.create');
    $route->post('/stok/stok-transfer{dombakTransfer}/stok/{dokumenStok}/file', [App\Http\Controllers\FileStokTransferController::class, 'store'])->name('stok-transfer.file.store');
    $route->get('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/{fileDokumenStok}', [App\Http\Controllers\FileStokTransferController::class, 'show'])->name('stok-transfer.file.show');
    $route->get('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/{fileDokumenStok}/download', [App\Http\Controllers\FileStokTransferController::class, 'download'])->name('stok-transfer.file.download');
    $route->get('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/{fileDokumenStok}/edit', [App\Http\Controllers\FileStokTransferController::class, 'edit'])->name('stok-transfer.file.edit');
    $route->put('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/{fileDokumenStok}', [App\Http\Controllers\FileStokTransferController::class, 'update'])->name('stok-transfer.file.update');
    $route->delete('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/{fileDokumenStok}', [App\Http\Controllers\FileStokTransferController::class, 'destroy'])->name('stok-transfer.file.delete');

    $route->get('/stok/stok-summary/api', [App\Http\Controllers\StokSummaryController::class, 'api_call'])->name('stok-summary.api_call');
    $route->get('/stok/stok-summary', [App\Http\Controllers\StokSummaryController::class, 'index'])->name('stok-summary.index');
    $route->get('/stok/stok-summary/create', [App\Http\Controllers\StokSummaryController::class, 'create'])->name('stok-summary.create');
    $route->post('/stok/stok-summary', [App\Http\Controllers\StokSummaryController::class, 'store'])->name('stok-summary.store');
    $route->get('/stok/stok-summary/{date}', [App\Http\Controllers\StokSummaryController::class, 'show'])->name('stok-summary.show');
    $route->get('/stok/stok-summary/{date}/edit', [App\Http\Controllers\StokSummaryController::class, 'edit'])->name('stok-summary.edit');
    $route->put('/stok/stok-summary/{date}', [App\Http\Controllers\StokSummaryController::class, 'edit'])->name('stok-summary.edit');
    $route->delete('/stok/stok-summary/{date}', [App\Http\Controllers\StokSummaryController::class, 'edit'])->name('stok-summary.edit');


    $route->get('/stok/stok-taking/riwayat', [App\Http\Controllers\StokTakingController::class, 'riwayat'])->name('stok-taking.riwayat');
    $route->get('/stok/stok-taking/riwayat/{stok}', [App\Http\Controllers\StokTakingController::class, 'riwayat_show'])->name('stok-taking.riwayat.show');

    $route->get('/stok/stok-taking', [App\Http\Controllers\StokTakingController::class, 'index'])->name('stok-taking.index');
    $route->get('/stok/stok-taking/create', [App\Http\Controllers\StokTakingController::class, 'create'])->name('stok-taking.create');
    $route->post('/stok/stok-taking', [App\Http\Controllers\StokTakingController::class, 'store'])->name('stok-taking.store');
    $route->get('/stok/stok-taking/{stok}', [App\Http\Controllers\StokTakingController::class, 'show'])->name('stok-taking.show');
    $route->put('/stok/stok-taking/{stok}/confirmation', [App\Http\Controllers\StokTakingController::class, 'confirmation_stok_taking'])->name('stok-taking.confirmation');
    $route->get('/stok/stok-taking/{stok}/edit', [App\Http\Controllers\StokTakingController::class, 'edit'])->name('stok-taking.edit');
    $route->put('/stok/stok-taking/{stok}', [App\Http\Controllers\StokTakingController::class, 'update'])->name('stok-taking.update');
    $route->delete('/stok/stok-taking/{stok}', [App\Http\Controllers\StokTakingController::class, 'destroy'])->name('stok-taking.delete');

    $route->get('/stok/stok-taking/{stok}/file', [App\Http\Controllers\FileDokumenStokController::class, 'index'])->name('stok-taking.file.index');
    $route->get('/stok/stok-taking/{stok}/file/create', [App\Http\Controllers\FileDokumenStokController::class, 'create'])->name('stok-taking.file.create');
    $route->post('/stok/stok-taking{stok}/file', [App\Http\Controllers\FileDokumenStokController::class, 'store'])->name('stok-taking.file.store');
    $route->get('/stok/stok-taking/{stok}/file/{file}', [App\Http\Controllers\FileDokumenStokController::class, 'show'])->name('stok-taking.file.show');
    $route->get('/stok/stok-taking/{stok}/file/{file}/download', [App\Http\Controllers\FileDokumenStokController::class, 'download'])->name('stok-taking.file.download');
    $route->get('/stok/stok-taking/{stok}/file/{file}/edit', [App\Http\Controllers\FileDokumenStokController::class, 'edit'])->name('stok-taking.file.edit');
    $route->put('/stok/stok-taking/{stok}/file/{file}', [App\Http\Controllers\FileDokumenStokController::class, 'update'])->name('stok-taking.file.update');
    $route->delete('/stok/stok-taking/{stok}/file/{file}', [App\Http\Controllers\FileDokumenStokController::class, 'destroy'])->name('stok-taking.file.delete');

    $route->get('/stok/totalisator/riwayat', [App\Http\Controllers\StandMeterController::class, 'riwayat'])->name('totalisator.riwayat');
    $route->get('/stok/totalisator/riwayat/{standMeter}', [App\Http\Controllers\StandMeterController::class, 'riwayat_show'])->name('totalisator.riwayat_show');

    $route->get('/stok/totalisator', [App\Http\Controllers\StandMeterController::class, 'index'])->name('totalisator.index');
    $route->get('/stok/totalisator/create', [App\Http\Controllers\StandMeterController::class, 'create'])->name('totalisator.create');
    $route->get('/stok/totalisator/create/api', [App\Http\Controllers\StandMeterController::class, 'create_api'])->name('totalisator.create_api');
    $route->post('/stok/totalisator', [App\Http\Controllers\StandMeterController::class, 'store'])->name('totalisator.store');
    $route->get('/stok/totalisator/{standMeter}', [App\Http\Controllers\StandMeterController::class, 'show'])->name('totalisator.show');
    $route->get('/stok/totalisator/{standMeter}/edit', [App\Http\Controllers\StandMeterController::class, 'edit'])->name('totalisator.edit');
    $route->get('/stok/totalisator/{standMeter}/final', [App\Http\Controllers\StandMeterController::class, 'final'])->name('totalisator.final');
    $route->put('/stok/totalisator/{standMeter}/final', [App\Http\Controllers\StandMeterController::class, 'final_update'])->name('totalisator.final_update');
    $route->put('/stok/totalisator/{standMeter}/confirmation', [App\Http\Controllers\StandMeterController::class, 'confirmation'])->name('totalisator.confirmation');
    $route->put('/stok/totalisator/{standMeter}', [App\Http\Controllers\StandMeterController::class, 'update'])->name('totalisator.update');
    $route->delete('/stok/totalisator/{standMeter}', [App\Http\Controllers\StandMeterController::class, 'destroy'])->name('totalisator.delete');

    $route->get('/stok/totalisator/{standMeter}/file/{file}/download', [App\Http\Controllers\FileStandMeterController::class, 'download'])->name('totalisator.file.download');
    $route->get('/stok/totalisator/{standMeter}/file', [App\Http\Controllers\FileStandMeterController::class, 'index'])->name('totalisator.file.index');
    $route->get('/stok/totalisator/{standMeter}/file/create', [App\Http\Controllers\FileStandMeterController::class, 'create'])->name('totalisator.file.create');
    $route->post('/stok/totalisator/{standMeter}/file', [App\Http\Controllers\FileStandMeterController::class, 'store'])->name('totalisator.file.store');
    $route->get('/stok/totalisator/{standMeter}/file/{file}', [App\Http\Controllers\FileStandMeterController::class, 'show'])->name('totalisator.file.show');
    $route->get('/stok/totalisator/{standMeter}/file/{file}/edit', [App\Http\Controllers\FileStandMeterController::class, 'edit'])->name('totalisator.file.edit');
    $route->put('/stok/totalisator/{standMeter}/file/{file}', [App\Http\Controllers\FileStandMeterController::class, 'update'])->name('totalisator.file.update');
    $route->delete('/stok/totalisator/{standMeter}/file/{file}', [App\Http\Controllers\FileStandMeterController::class, 'destroy'])->name('totalisator.file.delete');

    $route->get('/stok/totalisator/riwayat/{standMeter}/file', [App\Http\Controllers\FileStandMeterController::class, 'riwayat_index'])->name('totalisator.file.riwayat.index');
    $route->get('/stok/totalisator/riwayat/{standMeter}/file/create', [App\Http\Controllers\FileStandMeterController::class, 'riwayat_create'])->name('totalisator.file.riwayat.create');
    $route->post('/stok/totalisator/riwayat/{standMeter}/file', [App\Http\Controllers\FileStandMeterController::class, 'riwayat_store'])->name('totalisator.file.riwayat.store');
    $route->get('/stok/totalisator/riwayat/{standMeter}/file/{file}', [App\Http\Controllers\FileStandMeterController::class, 'riwayat_show'])->name('totalisator.file.riwayat.show');
    $route->get('/stok/totalisator/riwayat/{standMeter}/file/{file}/edit', [App\Http\Controllers\FileStandMeterController::class, 'riwayat_edit'])->name('totalisator.file.riwayat.edit');
    $route->put('/stok/totalisator/riwayat/{standMeter}/file/{file}', [App\Http\Controllers\FileStandMeterController::class, 'riwayat_update'])->name('totalisator.file.riwayat.update');
    $route->delete('/stok/totalisator/riwayat/{standMeter}/file/{file}', [App\Http\Controllers\FileStandMeterController::class, 'riwayat_destroy'])->name('totalisator.file.riwayat.delete');

    // Tera
    $route->get('/tera', App\Http\Controllers\TeraController::class)->name('menu.tera');
    $route->get('/tera/stok-peminjaman', [App\Http\Controllers\StokPeminjamanController::class, 'index'])->name('tera.peminjaman');
    $route->get('/tera/stok-pengembalian', [App\Http\Controllers\StokPengembalianController::class, 'index'])->name('tera.pengembalian');

    //Setoran
    $route->get('/setoran', App\Http\Controllers\SetoranController::class)->name('menu.setoran');

    // Open penjualan
    $route->get('/setoran/open-penjualan', [App\Http\Controllers\OpenPenjualanController::class, 'index'])->name('setoran.open');
    $route->get('/setoran/open-penjualan/create', [App\Http\Controllers\OpenPenjualanController::class, 'create'])->name('setoran.open.create');
    $route->post('/setoran/open-penjualan', [App\Http\Controllers\OpenPenjualanController::class, 'store'])->name('setoran.open.store');
    $route->get('/setoran/open-penjualan/{penjualan}', [App\Http\Controllers\OpenPenjualanController::class, 'show'])->name('setoran.open.show');
    $route->put('/setoran/open-penjualan/{penjualan}/status_to', [App\Http\Controllers\OpenPenjualanController::class, 'update_status_to'])->name('setoran.open.update.status_to');
    $route->put('/setoran/open-penjualan/{penjualan}/status_from', [App\Http\Controllers\OpenPenjualanController::class, 'update_status_from'])->name('setoran.open.update.status_from');
    $route->delete('/setoran/open-penjualan/{penjualan}', [App\Http\Controllers\OpenPenjualanController::class, 'destroy'])->name('setoran.open.delete');

    $route->get('/setoran/open-penjualan/{penjualan}/detail', [App\Http\Controllers\OpenPenjualanDetailController::class, 'index'])->name('setoran.open.detail');
    $route->get('/setoran/open-penjualan/{penjualan}/detail/create', [App\Http\Controllers\OpenPenjualanDetailController::class, 'create'])->name('setoran.open.detail.create');
    $route->post('/setoran/open-penjualan/{penjualan}/detail', [App\Http\Controllers\OpenPenjualanDetailController::class, 'store'])->name('setoran.open.detail.store');
    $route->get('/setoran/open-penjualan/{penjualan}/detail/{detail}', [App\Http\Controllers\OpenPenjualanDetailController::class, 'show'])->name('setoran.open.detail.show');
    $route->delete('/setoran/open-penjualan/{penjualan}/detail/{detail}', [App\Http\Controllers\OpenPenjualanDetailController::class, 'destroy'])->name('setoran.open.detail.delete');

    $route->get('/setoran/open-penjualan/{penjualan}/pj', [App\Http\Controllers\PjOperasionalSetoranController::class, 'index'])->name('setoran.open.pj');
    $route->get('/setoran/open-penjualan/{penjualan}/pj/create', [App\Http\Controllers\PjOperasionalSetoranController::class, 'create'])->name('setoran.open.pj.create');
    $route->get('/setoran/open-penjualan/{penjualan}/pj/create/api', [App\Http\Controllers\PjOperasionalSetoranController::class, 'create_api'])->name('setoran.open.pj.create.api');
    $route->post('/setoran/open-penjualan/{penjualan}/pj', [App\Http\Controllers\PjOperasionalSetoranController::class, 'store'])->name('setoran.open.pj.store');
    $route->get('/setoran/open-penjualan/{penjualan}/pj/{jabatan}', [App\Http\Controllers\PjOperasionalSetoranController::class, 'show'])->name('setoran.open.pj.show');
    $route->delete('/setoran/open-penjualan/{penjualan}/pj/{jabatan}', [App\Http\Controllers\PjOperasionalSetoranController::class, 'destroy'])->name('setoran.open.pj.delete');

    // Penitipan Setoran
    $route->get('/setoran/penitipan-setoran', [App\Http\Controllers\PenitipanSetoranController::class, 'index_open_penjualan'])->name('setoran.penitipan');
    $route->get('/setoran/penitipan-setoran/{penjualan}', [App\Http\Controllers\PenitipanSetoranController::class, 'show_open_penjualan'])->name('setoran.penitipan.show');
    $route->put('/setoran/penitipan-setoran/{penjualan}/update_closing_to', [App\Http\Controllers\PenitipanSetoranController::class, 'update_closing_to'])->name('setoran.penitipan.update_closing_to');
    $route->put('/setoran/penitipan-setoran/{penjualan}/update_closing_from', [App\Http\Controllers\PenitipanSetoranController::class, 'update_closing_from'])->name('setoran.penitipan.update_closing_from');

    // Closing Penjualan
    $route->get('/setoran/closing-penjualan', [App\Http\Controllers\ClosingPenjualanController::class, 'index'])->name('setoran.closing');
    $route->get('/setoran/closing-penjualan/{penjualan}', [App\Http\Controllers\ClosingPenjualanController::class, 'show'])->name('setoran.closing.show');
    $route->put('/setoran/closing-penjualan/{penjualan}/confirmed_to/{detail}', [App\Http\Controllers\ClosingPenjualanController::class, 'confirmed_to'])->name('setoran.closing.update.confirmed_to');
    $route->put('/setoran/closing-penjualan/{penjualan}/confirmed_from/{detail}', [App\Http\Controllers\ClosingPenjualanController::class, 'confirmed_from'])->name('setoran.closing.update.confirmed_from');
    $route->put('/setoran/closing-penjualan/{penjualan}/confirmed_to_setoran', [App\Http\Controllers\ClosingPenjualanController::class, 'confirmed_to_setoran'])->name('setoran.closing.confirm.confirmed_to');
    $route->put('/setoran/closing-penjualan/{penjualan}/confirmed_from_setoran', [App\Http\Controllers\ClosingPenjualanController::class, 'confirmed_from_setoran'])->name('setoran.closing.confirm.confirmed_from');
    $route->delete('/setoran/closing-penjualan/{penjualan}', [App\Http\Controllers\ClosingPenjualanController::class, 'destroy'])->name('setoran.closing.delete');

    // Setor Ke Bank
    $route->get('/setoran/setoran-bank', [App\Http\Controllers\SetoranUangOperatorController::class, 'index_penjualan'])->name('setoran.setoran_bank.index_setoran_bank_penjualan');
    $route->get('/setoran/setoran-bank/{penjualan}', [App\Http\Controllers\SetoranUangOperatorController::class, 'show_penjualan'])->name('setoran.setoran_bank.show_setoran_bank_penjualan');
    $route->put('/setoran/setoran-bank/{penjualan}/check', [App\Http\Controllers\SetoranUangOperatorController::class, 'check'])->name('setoran.setoran_bank.check');
    $route->put('/setoran/setoran-bank/{penjualan}/update/status/top_0x002', [App\Http\Controllers\SetoranUangOperatorController::class, 'update_status_top_0x002'])->name('setoran.setoran_bank.update_status_top_0x002');
    $route->put('/setoran/setoran-bank/{penjualan}/update/status/top_0x003', [App\Http\Controllers\SetoranUangOperatorController::class, 'update_status_top_0x003'])->name('setoran.setoran_bank.update_status_top_0x003');
    $route->put('/setoran/setoran-bank/{penjualan}/update/status/admin_0x002', [App\Http\Controllers\SetoranUangOperatorController::class, 'update_status_admin_0x002'])->name('setoran.setoran_bank.update_status_admin_0x002');
    $route->put('/setoran/setoran-bank/{penjualan}/update/status/admin_0x003', [App\Http\Controllers\SetoranUangOperatorController::class, 'update_status_admin_0x003'])->name('setoran.setoran_bank.update_status_admin_0x003');

    // Bank
    $route->get('/setoran/setoran-bank/{penjualan}/bank', [App\Http\Controllers\SetoranUangOperatorController::class, 'index'])->name('setoran.setoran_bank.bank.index');
    $route->get('/setoran/setoran-bank/{penjualan}/bank/create', [App\Http\Controllers\SetoranUangOperatorController::class, 'create'])->name('setoran.setoran_bank.bank.create');
    $route->post('/setoran/setoran-bank/{penjualan}/bank', [App\Http\Controllers\SetoranUangOperatorController::class, 'store'])->name('setoran.setoran_bank.bank.store');
    $route->get('/setoran/setoran-bank/{penjualan}/bank/{penitipan}', [App\Http\Controllers\SetoranUangOperatorController::class, 'show'])->name('setoran.setoran_bank.bank.show');
    $route->get('/setoran/setoran-bank/{penjualan}/bank/{penitipan}/edit', [App\Http\Controllers\SetoranUangOperatorController::class, 'edit'])->name('setoran.setoran_bank.bank.edit');
    $route->put('/setoran/setoran-bank/{penjualan}/bank/{penitipan}/update_confirmation_0x002', [App\Http\Controllers\SetoranUangOperatorController::class, 'update_confirmation_0x002'])->name('setoran.setoran_bank.bank.update_confirmation_0x002');
    $route->put('/setoran/setoran-bank/{penjualan}/bank/{penitipan}/update_confirmation_0x003', [App\Http\Controllers\SetoranUangOperatorController::class, 'update_confirmation_0x003'])->name('setoran.setoran_bank.bank.update_confirmation_0x003');
    $route->put('/setoran/setoran-bank/{penjualan}/bank/{penitipan}', [App\Http\Controllers\SetoranUangOperatorController::class, 'update'])->name('setoran.setoran_bank.bank.update');
    $route->delete('/setoran/setoran-bank/{penjualan}/bank/{penitipan}', [App\Http\Controllers\SetoranUangOperatorController::class, 'destroy'])->name('setoran.setoran_bank.bank.destroy');

    // File Bank
    $route->get('/setoran/setoran-bank/{penjualan}/bank/{penitipan}/file', [App\Http\Controllers\FilePenitipanSetoranController::class, 'index'])->name('setoran.setoran_bank.file.index');
    $route->get('/setoran/setoran-bank/{penjualan}/bank/{penitipan}/file/create', [App\Http\Controllers\FilePenitipanSetoranController::class, 'create'])->name('setoran.setoran_bank.file.create');
    $route->post('/setoran/setoran-bank/{penjualan}/bank/{penitipan}/file', [App\Http\Controllers\FilePenitipanSetoranController::class, 'store'])->name('setoran.setoran_bank.file.store');
    $route->delete('/setoran/setoran-bank/{penjualan}/bank/{penitipan}/file/{file}', [App\Http\Controllers\FilePenitipanSetoranController::class, 'destroy'])->name('setoran.setoran_bank.file.delete');
    $route->get('/setoran/setoran-bank/{penjualan}/bank/{penitipan}/file/{file}', [App\Http\Controllers\FilePenitipanSetoranController::class, 'download'])->name('setoran.setoran_bank.file.download');

    // Artikel
    $route->get('/artikel', App\Http\Controllers\ArtikelController::class)->name('menu.artikel');

    $route->get('/artikel/tag', [App\Http\Controllers\TagController::class, 'index'])->name('tag.index');
    $route->get('/artikel/tag/create', [App\Http\Controllers\TagController::class, 'create'])->name('tag.create');
    $route->post('/artikel/tag', [App\Http\Controllers\TagController::class, 'store'])->name('tag.store');
    $route->get('/artikel/tag/{tag}', [App\Http\Controllers\TagController::class, 'show'])->name('tag.show');
    $route->get('/artikel/tag/{tag}/edit', [App\Http\Controllers\TagController::class, 'edit'])->name('tag.edit');
    $route->put('/artikel/tag/{tag}', [App\Http\Controllers\TagController::class, 'update'])->name('tag.update');
    $route->delete('/artikel/tag/{tag}', [App\Http\Controllers\TagController::class, 'destroy'])->name('tag.delete');

    $route->get('/artikel/kategori', [App\Http\Controllers\KategoriController::class, 'index'])->name('kategori.index');
    $route->get('/artikel/kategori/create', [App\Http\Controllers\KategoriController::class, 'create'])->name('kategori.create');
    $route->post('/artikel/kategori', [App\Http\Controllers\KategoriController::class, 'store'])->name('kategori.store');
    $route->get('/artikel/kategori/{kategori}', [App\Http\Controllers\KategoriController::class, 'show'])->name('kategori.show');
    $route->get('/artikel/kategori/{kategori}/edit', [App\Http\Controllers\KategoriController::class, 'edit'])->name('kategori.edit');
    $route->put('/artikel/kategori/{kategori}', [App\Http\Controllers\KategoriController::class, 'update'])->name('kategori.update');
    $route->delete('/artikel/kategori/{kategori}', [App\Http\Controllers\KategoriController::class, 'destroy'])->name('kategori.delete');

    $route->get('/artikel/post', [App\Http\Controllers\PostController::class, 'index'])->name('post.index');
    $route->get('/artikel/post/create', [App\Http\Controllers\PostController::class, 'create'])->name('post.create');
    $route->post('/artikel/post', [App\Http\Controllers\PostController::class, 'store'])->name('post.store');
    $route->get('/artikel/post/{post}', [App\Http\Controllers\PostController::class, 'show'])->name('post.show');
    $route->get('/artikel/post/{post}/edit', [App\Http\Controllers\PostController::class, 'edit'])->name('post.edit');
    $route->put('/artikel/post/{post}', [App\Http\Controllers\PostController::class, 'update'])->name('post.update');
    $route->delete('/artikel/post/{post}', [App\Http\Controllers\PostController::class, 'destroy'])->name('post.delete');

    $route->get('/artikel/post/{post}/file', [App\Http\Controllers\FilePostController::class, 'index'])->name('file.post.index');
    $route->get('/artikel/post/{post}/file/create', [App\Http\Controllers\FilePostController::class, 'create'])->name('file.post.create');
    $route->post('/artikel/post/{post}/file', [App\Http\Controllers\FilePostController::class, 'store'])->name('file.post.store');
    $route->get('/artikel/post/{post}/file/{filePost}/download', [App\Http\Controllers\FilePostController::class, 'download'])->name('file.post.download');
    $route->get('/artikel/post/{post}/file/{filePost}', [App\Http\Controllers\FilePostController::class, 'show'])->name('file.post.show');
    $route->get('/artikel/post/{post}/file/{filePost}/edit', [App\Http\Controllers\FilePostController::class, 'edit'])->name('file.post.edit');
    $route->put('/artikel/post/{post}/file/{filePost}', [App\Http\Controllers\FilePostController::class, 'update'])->name('file.post.update');
    $route->delete('/artikel/post/{post}/file/{filePost}', [App\Http\Controllers\FilePostController::class, 'destroy'])->name('file.post.delete');

    // Financial
    $route->get('/financial', App\Http\Controllers\FinancialController::class)->name('menu.financial');

    $route->get('/financial/petty-cash', [App\Http\Controllers\PettyCashController::class, 'index'])->name('financial.petty-cash');
    $route->get('/financial/petty-cash/create', [App\Http\Controllers\PettyCashController::class, 'create'])->name('petty-cash.create');
    $route->post('/financial/petty-cash', [App\Http\Controllers\PettyCashController::class, 'store'])->name('petty-cash.store');
    $route->get('/financial/petty-cash/{pettyCash}', [App\Http\Controllers\PettyCashController::class, 'show'])->name('petty-cash.show');
    $route->get('/financial/petty-cash/{pettyCash}/edit', [App\Http\Controllers\PettyCashController::class, 'edit'])->name('petty-cash.edit');
    $route->put('/financial/petty-cash/{pettyCash}', [App\Http\Controllers\PettyCashController::class, 'update'])->name('petty-cash.update');
    $route->delete('/financial/petty-cash/{pettyCash}', [App\Http\Controllers\PettyCashController::class, 'destroy'])->name('petty-cash.delete');

    $route->get('/financial/petty-cash/{pettyCash}/file', [App\Http\Controllers\FilePettyCashController::class, 'index'])->name('financial.petty-cash.file');
    $route->get('/financial/petty-cash/{pettyCash}/file/create', [App\Http\Controllers\FilePettyCashController::class, 'create'])->name('petty-cash.file.create');
    $route->post('/financial/petty-cash/{pettyCash}/file', [App\Http\Controllers\FilePettyCashController::class, 'store'])->name('petty-cash.file.store');
    $route->get('/financial/petty-cash/{pettyCash}/file/{file}', [App\Http\Controllers\FilePettyCashController::class, 'show'])->name('petty-cash.file.show');
    $route->get('/financial/petty-cash/{pettyCash}/file/{file}/download', [App\Http\Controllers\FilePettyCashController::class, 'download'])->name('petty-cash.file.download');
    $route->get('/financial/petty-cash/{pettyCash}/file/{file}/edit', [App\Http\Controllers\FilePettyCashController::class, 'edit'])->name('petty-cash.file.edit');
    $route->put('/financial/petty-cash/{pettyCash}/file/{file}', [App\Http\Controllers\FilePettyCashController::class, 'update'])->name('petty-cash.file.update');
    $route->delete('/financial/petty-cash/{pettyCash}/file/{file}', [App\Http\Controllers\FilePettyCashController::class, 'destroy'])->name('petty-cash.file.delete');

    $route->get('/financial/rekap-pajak', [App\Http\Controllers\RekapPajakController::class, 'index'])->name('rekap-pajak.index');
    $route->post('/financial/rekap-pajak/export', [App\Http\Controllers\RekapPajakController::class, 'export'])->name('rekap-pajak.export');
    $route->get('/financial/rekap-pajak/{pajakPembelian}', [App\Http\Controllers\RekapPajakController::class, 'show'])->name('rekap-pajak.show');

    $route->get('/financial/margin', [App\Http\Controllers\MarginController::class, 'index'])->name('margin.index');

    $route->get('/financial/laba-rugi', [App\Http\Controllers\LabaRugiController::class, 'index'])->name('laba-rugi.index');
    $route->get('/financial/neraca', [App\Http\Controllers\NeracaController::class, 'index'])->name('neraca.index');
    $route->get('/financial/buku-besar', [App\Http\Controllers\BukuBesarController::class, 'index'])->name('buku-besar.index');
    $route->get('/financial/neraca-saldo', [App\Http\Controllers\NeracaSaldoController::class, 'index'])->name('neraca-saldo.index');
    $route->get('/financial/arus-kas', [App\Http\Controllers\ArusKasController::class, 'index'])->name('arus-kas.index');
    $route->get('/financial/perubahan-modal', [App\Http\Controllers\PerubahanModalController::class, 'index'])->name('perubahan-modal.index');

    $route->get('/financial/coa', [App\Http\Controllers\CoaController::class, 'index'])->name('coa.index');
    $route->get('/financial/coa/create', [App\Http\Controllers\CoaController::class, 'create'])->name('coa.create');
    $route->post('/financial/coa', [App\Http\Controllers\CoaController::class, 'store'])->name('coa.store');
    $route->get('/financial/coa/{coa}', [App\Http\Controllers\CoaController::class, 'show'])->name('coa.show');
    $route->get('/financial/coa/{coa}/edit', [App\Http\Controllers\CoaController::class, 'edit'])->name('coa.edit');
    $route->put('/financial/coa/{coa}', [App\Http\Controllers\CoaController::class, 'update'])->name('coa.update');
    $route->delete('/financial/coa/{coa}', [App\Http\Controllers\CoaController::class, 'destroy'])->name('coa.destroy');

    $route->get('/financial/jurnal', [App\Http\Controllers\JurnalController::class, 'index'])->name('jurnal.index');
    $route->get('/financial/jurnal/{jurnal}', [App\Http\Controllers\JurnalController::class, 'show'])->name('jurnal.show');

    $route->get('/financial/saldo-awal', [App\Http\Controllers\SaldoAwalController::class, 'index'])->name('saldo-awal.index');
    $route->get('/financial/saldo-awal/{spbu}', [App\Http\Controllers\SaldoAwalController::class, 'form'])->name('saldo-awal.form');
    $route->put('/financial/saldo-awal/{spbu}', [App\Http\Controllers\SaldoAwalController::class, 'store'])->name('saldo-awal.store');

    // Absensi
    $route->get('/jadwal_absensi', App\Http\Controllers\JadwalAbsensiController::class)->name('menu.jadwal_absensi');

    $route->get('/jadwal_absensi/jadwal', [\App\Http\Controllers\JadwalKerjaController::class, 'index'])->name('jadwal-kerja.index');
    $route->get('/jadwal_absensi/jadwal/template', [\App\Http\Controllers\JadwalKerjaController::class, 'template'])->name('jadwal-kerja.upload_file.template');
    $route->get('/jadwal_absensi/jadwal/upload', [\App\Http\Controllers\JadwalKerjaController::class, 'upload_file'])->name('jadwal-kerja.upload_file');
    $route->post('/jadwal_absensi/jadwal/upload', [\App\Http\Controllers\JadwalKerjaController::class, 'upload_file_store'])->name('jadwal-kerja.upload_file.store');
    $route->delete('/jadwal_absensi/jadwal/delete', [\App\Http\Controllers\JadwalKerjaController::class, 'delete'])->name('jadwal-kerja.upload_file.delete');
    $route->get('/jadwal_absensi/jadwal/qr', [\App\Http\Controllers\JadwalKerjaController::class, 'qrcode'])->name('jadwal-kerja.qr');

    $route->get('/jadwal_absensi/absensi', [\App\Http\Controllers\AbsensiController::class, 'index'])->name('absensi.index');
    $route->get('/jadwal_absensi/absensi/{penjadwalan}', [\App\Http\Controllers\AbsensiController::class, 'show'])->name('absensi.show');
    $route->get('/jadwal_absensi/absensi/{penjadwalan}/edit', [\App\Http\Controllers\AbsensiController::class, 'edit'])->name('absensi.edit');
    $route->put('/jadwal_absensi/absensi/{penjadwalan}', [\App\Http\Controllers\AbsensiController::class, 'update'])->name('absensi.update');
    $route->put('/jadwal_absensi/absensi/{penjadwalan}/confirm', [\App\Http\Controllers\AbsensiController::class, 'confirm'])->name('absensi.confirm');
    $route->put('/jadwal_absensi/absensi/{penjadwalan}/unconfirm', [\App\Http\Controllers\AbsensiController::class, 'unconfirm'])->name('absensi.unconfirm');

    $route->get('/jadwal_absensi/laporan_absensi', [\App\Http\Controllers\LaporanAbsensiController::class, 'index'])->name('laporan-absensi.index');


    // Konfigurasi
    $route->get('/konfigurasi', App\Http\Controllers\KonfigurasiController::class)->name('menu.konfigurasi');

    $route->get('/konfigurasi/spbu', [App\Http\Controllers\SpbuController::class, 'index'])->name('spbu.index');
    $route->get('/konfigurasi/spbu/create', [App\Http\Controllers\SpbuController::class, 'create'])->name('spbu.create');
    $route->post('/konfigurasi/spbu', [App\Http\Controllers\SpbuController::class, 'store'])->name('spbu.store');
    $route->get('/konfigurasi/spbu/{spbu}', [App\Http\Controllers\SpbuController::class, 'show'])->name('spbu.show');
    $route->get('/konfigurasi/spbu/{spbu}/edit', [App\Http\Controllers\SpbuController::class, 'edit'])->name('spbu.edit');
    $route->put('/konfigurasi/spbu/{spbu}', [App\Http\Controllers\SpbuController::class, 'update'])->name('spbu.update');
    $route->delete('/konfigurasi/spbu/{spbu}', [App\Http\Controllers\SpbuController::class, 'destroy'])->name('spbu.destroy');

    $route->get('/konfigurasi/divisi', [App\Http\Controllers\DivisiController::class, 'index'])->name('divisi.index');
    $route->get('/konfigurasi/divisi/create', [App\Http\Controllers\DivisiController::class, 'create'])->name('divisi.create');
    $route->post('/konfigurasi/divisi', [App\Http\Controllers\DivisiController::class, 'store'])->name('divisi.store');
    $route->get('/konfigurasi/divisi/{divisi}', [App\Http\Controllers\DivisiController::class, 'show'])->name('divisi.show');
    $route->get('/konfigurasi/divisi/{divisi}/edit', [App\Http\Controllers\DivisiController::class, 'edit'])->name('divisi.edit');
    $route->put('/konfigurasi/divisi/{divisi}', [App\Http\Controllers\DivisiController::class, 'update'])->name('divisi.update');
    $route->delete('/konfigurasi/divisi/{divisi}', [App\Http\Controllers\DivisiController::class, 'destroy'])->name('divisi.destroy');

    $route->get('/konfigurasi/level', [App\Http\Controllers\LevelController::class, 'index'])->name('level.index');
    $route->get('/konfigurasi/level/create', [App\Http\Controllers\LevelController::class, 'create'])->name('level.create');
    $route->post('/konfigurasi/level', [App\Http\Controllers\LevelController::class, 'store'])->name('level.store');
    $route->get('/konfigurasi/level/{level}', [App\Http\Controllers\LevelController::class, 'show'])->name('level.show');
    $route->get('/konfigurasi/level/{level}/edit', [App\Http\Controllers\LevelController::class, 'edit'])->name('level.edit');
    $route->put('/konfigurasi/level/{level}', [App\Http\Controllers\LevelController::class, 'update'])->name('level.update');
    $route->delete('/konfigurasi/level/{level}', [App\Http\Controllers\LevelController::class, 'destroy'])->name('level.destroy');

    $route->get('/konfigurasi/jabatan', [App\Http\Controllers\JabatanController::class, 'index'])->name('jabatan.index');
    $route->get('/konfigurasi/jabatan/create', [App\Http\Controllers\JabatanController::class, 'create'])->name('jabatan.create');
    $route->post('/konfigurasi/jabatan', [App\Http\Controllers\JabatanController::class, 'store'])->name('jabatan.store');
    $route->get('/konfigurasi/jabatan/{jabatan}', [App\Http\Controllers\JabatanController::class, 'show'])->name('jabatan.show');
    $route->get('/konfigurasi/jabatan/{jabatan}/edit', [App\Http\Controllers\JabatanController::class, 'edit'])->name('jabatan.edit');
    $route->put('/konfigurasi/jabatan/{jabatan}', [App\Http\Controllers\JabatanController::class, 'update'])->name('jabatan.update');
    $route->delete('/konfigurasi/jabatan/{jabatan}', [App\Http\Controllers\JabatanController::class, 'destroy'])->name('jabatan.destroy');

    $route->get('/konfigurasi/hari', [App\Http\Controllers\HariController::class, 'index'])->name('hari.index');
    $route->get('/konfigurasi/hari/create', [App\Http\Controllers\HariController::class, 'create'])->name('hari.create');
    $route->post('/konfigurasi/hari', [App\Http\Controllers\HariController::class, 'store'])->name('hari.store');
    $route->get('/konfigurasi/hari/{hari}', [App\Http\Controllers\HariController::class, 'show'])->name('hari.show');
    $route->get('/konfigurasi/hari/{hari}/edit', [App\Http\Controllers\HariController::class, 'edit'])->name('hari.edit');
    $route->put('/konfigurasi/hari/{hari}', [App\Http\Controllers\HariController::class, 'update'])->name('hari.update');
    $route->delete('/konfigurasi/hari/{hari}', [App\Http\Controllers\HariController::class, 'destroy'])->name('hari.destroy');

    $route->get('/konfigurasi/pajak', [App\Http\Controllers\PajakPembelianLibController::class, 'index'])->name('pajak_pembelian_lib.index');
    $route->get('/konfigurasi/pajak/create', [App\Http\Controllers\PajakPembelianLibController::class, 'create'])->name('pajak_pembelian_lib.create');
    $route->post('/konfigurasi/pajak', [App\Http\Controllers\PajakPembelianLibController::class, 'store'])->name('pajak_pembelian_lib.store');
    $route->get('/konfigurasi/pajak/{pajak}', [App\Http\Controllers\PajakPembelianLibController::class, 'show'])->name('pajak_pembelian_lib.show');
    $route->get('/konfigurasi/pajak/{pajak}/edit', [App\Http\Controllers\PajakPembelianLibController::class, 'edit'])->name('pajak_pembelian_lib.edit');
    $route->put('/konfigurasi/pajak/{pajak}', [App\Http\Controllers\PajakPembelianLibController::class, 'update'])->name('pajak_pembelian_lib.update');
    $route->delete('/konfigurasi/pajak/{pajak}', [App\Http\Controllers\PajakPembelianLibController::class, 'destroy'])->name('pajak_pembelian_lib.destroy');

    $route->get('/konfigurasi/jam_kerja', [App\Http\Controllers\JamKerjaController::class, 'index'])->name('jam_kerja.index');
    $route->get('/konfigurasi/jam_kerja/create', [App\Http\Controllers\JamKerjaController::class, 'create'])->name('jam_kerja.create');
    $route->post('/konfigurasi/jam_kerja', [App\Http\Controllers\JamKerjaController::class, 'store'])->name('jam_kerja.store');
    $route->get('/konfigurasi/jam_kerja/{jamKerja}', [App\Http\Controllers\JamKerjaController::class, 'show'])->name('jam_kerja.show');
    $route->get('/konfigurasi/jam_kerja/{jamKerja}/edit', [App\Http\Controllers\JamKerjaController::class, 'edit'])->name('jam_kerja.edit');
    $route->put('/konfigurasi/jam_kerja/{jamKerja}', [App\Http\Controllers\JamKerjaController::class, 'update'])->name('jam_kerja.update');
    $route->delete('/konfigurasi/jam_kerja/{jamKerja}', [App\Http\Controllers\JamKerjaController::class, 'destroy'])->name('jam_kerja.destroy');

    $route->get('/konfigurasi/import', [App\Http\Controllers\ImportController::class, 'index'])->name('import.index');
    $route->get('/konfigurasi/import/template', [App\Http\Controllers\ImportController::class, 'template'])->name('import.template');
    $route->post('/konfigurasi/import', [App\Http\Controllers\ImportController::class, 'store'])->name('import.store');

    $route->get('/kehadiran', [App\Http\Controllers\KehadiranOperatorController::class, 'index'])->name('menu.kehadiran');
    $route->get('/kehadiran/{penjadwalan}', [App\Http\Controllers\KehadiranOperatorController::class, 'show'])->name('kehadiran.show');
    $route->put('/kehadiran/{penjadwalan}/api', [App\Http\Controllers\KehadiranOperatorController::class, 'store'])->name('kehadiran.store');
    $route->put('/kehadiran/{penjadwalan}/api/out', [App\Http\Controllers\KehadiranOperatorController::class, 'close'])->name('kehadiran.close');
});

Route::get('/', [App\Http\Controllers\WelcomeController::class, 'index'])->name('welcome');
Route::get('/kategori/{kategori}', [App\Http\Controllers\WelcomeController::class, 'kategori'])->name('welcome.kategori');
Route::get('/tag/{tag}', [App\Http\Controllers\WelcomeController::class, 'tag'])->name('welcome.tag');
Route::get('/user/{member}', [App\Http\Controllers\WelcomeController::class, 'user'])->name('welcome.user');
Route::get('/{post}', [App\Http\Controllers\WelcomeController::class, 'show'])->name('welcome.show');
