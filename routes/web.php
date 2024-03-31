<?php

use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\ArusKasController;
use App\Http\Controllers\AsetController;
use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\ClosingPenjualanController;
use App\Http\Controllers\DetailPembelianController;
use App\Http\Controllers\DetailRencanaPembelianController;
use App\Http\Controllers\DispenserController;
use App\Http\Controllers\DivisiController;
use App\Http\Controllers\DombakController;
use App\Http\Controllers\FasilitasController;
use App\Http\Controllers\FileAsetController;
use App\Http\Controllers\FileDokumenStokController;
use App\Http\Controllers\FileFasilitasController;
use App\Http\Controllers\FileLampiranLoController;
use App\Http\Controllers\FilePenerimaanController;
use App\Http\Controllers\FilePenitipanSetoranController;
use App\Http\Controllers\FilePettyCashController;
use App\Http\Controllers\FilePostController;
use App\Http\Controllers\FileStandMeterController;
use App\Http\Controllers\FileStokTransferController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\HargaController;
use App\Http\Controllers\HariController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\JamKerjaController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KehadiranOperatorController;
use App\Http\Controllers\KonfigurasiController;
use App\Http\Controllers\KonversiController;
use App\Http\Controllers\LabaRugiController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MarginController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\Ms2Controller;
use App\Http\Controllers\NeracaController;
use App\Http\Controllers\NozzleController;
use App\Http\Controllers\OpenPenjualanController;
use App\Http\Controllers\OpenPenjualanDetailController;
use App\Http\Controllers\PajakPembelianLibController;
use App\Http\Controllers\PayungController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PengirimanController;
use App\Http\Controllers\PenitipanSetoranController;
use App\Http\Controllers\PerubahanModalController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\RekapPajakController;
use App\Http\Controllers\RencanaPembelianController;
use App\Http\Controllers\SetoranController;
use App\Http\Controllers\SetoranUangOperatorController;
use App\Http\Controllers\SpbuController;
use App\Http\Controllers\StandMeterController;
use App\Http\Controllers\StandMeterOperatorController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\StokPeminjamanController;
use App\Http\Controllers\StokPengembalianController;
use App\Http\Controllers\StokTakingController;
use App\Http\Controllers\StokTransferController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TeraController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false
]);

// Route::get('/hash', function () { return Hash::make('Admin123'); })->name('uploadTest');
// Route::get('/upload', [App\Http\Controllers\WelcomeController::class, 'uploadTest'])->name('uploadTest');

Route::middleware(['auth', 'cache.header'])->group(function ($route) {
    $route->get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    $route->get('/profil', [App\Http\Controllers\HomeController::class, 'profil'])->name('profil');
    $route->get('/profil/edit', [App\Http\Controllers\HomeController::class, 'profil_edit'])->name('profil.edit');
    $route->post('/profil', [App\Http\Controllers\HomeController::class, 'profil_store'])->name('profil.store');
    $route->get('/profil/password', [App\Http\Controllers\HomeController::class, 'profil_edit_password'])->name('profil.edit_password');
    $route->post('/profil/password', [App\Http\Controllers\HomeController::class, 'profil_store_password'])->name('profil.store_password');

    $route->get('/notifikasi', [App\Http\Controllers\HomeController::class, 'notifikasi'])->name('notifikasi');

    // Master Data
    $route->get('/master', MasterController::class)->name('menu.master');

    $route->get('/master/aset', [AsetController::class, 'index'])->name('aset.index');
    $route->get('/master/aset/create', [AsetController::class, 'create'])->name('aset.create');
    $route->post('/master/aset', [AsetController::class, 'store'])->name('aset.store');
    $route->get('/master/aset/{aset}', [AsetController::class, 'show'])->name('aset.show');
    $route->get('/master/aset/{aset}/edit', [AsetController::class, 'edit'])->name('aset.edit');
    $route->put('/master/aset/{aset}', [AsetController::class, 'update'])->name('aset.update');
    $route->delete('/master/aset/{aset}', [AsetController::class, 'destroy'])->name('aset.destroy');

    $route->get('/master/aset/{aset}/file', [FileAsetController::class, 'index'])->name('aset.file.index');
    $route->get('/master/aset/{aset}/file/create', [FileAsetController::class, 'create'])->name('aset.file.create');
    $route->post('/master/aset/{aset}/file', [FileAsetController::class, 'store'])->name('aset.file.store');
    $route->get('/master/aset/{aset}/file/{fileAset}', [FileAsetController::class, 'show'])->name('aset.file.show');
    $route->get('/master/aset/{aset}/file/{fileAset}/unduh', [FileAsetController::class, 'download'])->name('aset.file.download');
    $route->get('/master/aset/{aset}/file/{fileAset}/edit', [FileAsetController::class, 'edit'])->name('aset.file.edit');
    $route->put('/master/aset/{aset}/file/{fileAset}', [FileAsetController::class, 'update'])->name('aset.file.update');
    $route->delete('/master/aset/{aset}/file/{fileAset}', [FileAsetController::class, 'destroy'])->name('aset.file.delete');

    $route->get('/master/fasilitas', [FasilitasController::class, 'index'])->name('fasilitas.index');
    $route->get('/master/fasilitas/create', [FasilitasController::class, 'create'])->name('fasilitas.create');
    $route->post('/master/fasilitas', [FasilitasController::class, 'store'])->name('fasilitas.store');
    $route->get('/master/fasilitas/{fasilitas}', [FasilitasController::class, 'show'])->name('fasilitas.show');
    $route->get('/master/fasilitas/{fasilitas}/edit', [FasilitasController::class, 'edit'])->name('fasilitas.edit');
    $route->put('/master/fasilitas/{fasilitas}', [FasilitasController::class, 'update'])->name('fasilitas.update');
    $route->delete('/master/fasilitas/{fasilitas}', [FasilitasController::class, 'destroy'])->name('fasilitas.destroy');

    $route->get('/master/fasilitas/{fasilitas}/file', [FileFasilitasController::class, 'index'])->name('fasilitas.file.index');
    $route->get('/master/fasilitas/{fasilitas}/file/create', [FileFasilitasController::class, 'create'])->name('fasilitas.file.create');
    $route->post('/master/fasilitas/{fasilitas}/file', [FileFasilitasController::class, 'store'])->name('fasilitas.file.store');
    $route->get('/master/fasilitas/{fasilitas}/file/{fileFasilitas}', [FileFasilitasController::class, 'show'])->name('fasilitas.file.show');
    $route->get('/master/fasilitas/{fasilitas}/file/{fileFasilitas}/unduh', [FileFasilitasController::class, 'download'])->name('fasilitas.file.download');
    $route->get('/master/fasilitas/{fasilitas}/file/{fileFasilitas}/edit', [FileFasilitasController::class, 'edit'])->name('fasilitas.file.edit');
    $route->put('/master/fasilitas/{fasilitas}/file/{fileFasilitas}', [FileFasilitasController::class, 'update'])->name('fasilitas.file.update');
    $route->delete('/master/fasilitas/{fasilitas}/file/{fileFasilitas}', [FileFasilitasController::class, 'destroy'])->name('fasilitas.file.delete');

    $route->get('/master/produk', [ProdukController::class, 'index'])->name('produk.index');
    $route->get('/master/produk/create', [ProdukController::class, 'create'])->name('produk.create');
    $route->post('/master/produk', [ProdukController::class, 'store'])->name('produk.store');
    $route->get('/master/produk/{produk}', [ProdukController::class, 'show'])->name('produk.show');
    $route->get('/master/produk/{produk}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
    $route->put('/master/produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
    $route->delete('/master/produk/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');

    $route->get('/master/harga/riwayat', [HargaController::class, 'riwayat_index'])->name('harga.riwayat.index');
    $route->get('/master/harga/riwayat/{harga}', [HargaController::class, 'riwayat_show'])->name('harga.riwayat.show');

    $route->get('/master/harga', [HargaController::class, 'index'])->name('harga.index');
    $route->get('/master/harga/create', [HargaController::class, 'create'])->name('harga.create');
    $route->post('/master/harga', [HargaController::class, 'store'])->name('harga.store');
    $route->get('/master/harga/{harga}', [HargaController::class, 'show'])->name('harga.show');
    $route->get('/master/harga/{harga}/edit', [HargaController::class, 'edit'])->name('harga.edit');
    $route->put('/master/harga/{harga}', [HargaController::class, 'update'])->name('harga.update');
    $route->delete('/master/harga/{harga}', [HargaController::class, 'destroy'])->name('harga.destroy');

    $route->get('/master/member', [MemberController::class, 'index'])->name('member.index');
    $route->get('/master/member/create', [MemberController::class, 'create'])->name('member.create');
    $route->post('/master/member', [MemberController::class, 'store'])->name('member.store');
    $route->get('/master/member/{member}', [MemberController::class, 'show'])->name('member.show');
    $route->get('/master/member/{member}/edit', [MemberController::class, 'edit'])->name('member.edit');
    $route->put('/master/member/{member}', [MemberController::class, 'update'])->name('member.update');
    $route->put('/master/member/{member}/reset', [MemberController::class, 'reset_pw'])->name('member.reset_pw');
    $route->delete('/master/member/{member}', [MemberController::class, 'destroy'])->name('member.destroy');

    $route->get('/master/member/{member}/jabatan', [MemberController::class, 'member_jabatan_index'])->name('member.jabatan.index');
    $route->get('/master/member/{member}/jabatan/create', [MemberController::class, 'member_jabatan_create'])->name('member.jabatan.create');
    $route->post('/master/member/{member}/jabatan/{jabatan}', [MemberController::class, 'member_jabatan_store'])->name('member.jabatan.store');
    $route->delete('/master/member/{member}/jabatan/{jabatan}', [MemberController::class, 'member_jabatan_destroy'])->name('member.jabatan.delete');

    $route->get('/master/dombak', [DombakController::class, 'index'])->name('dombak.index');
    $route->get('/master/dombak/create', [DombakController::class, 'create'])->name('dombak.create');
    $route->get('/master/dombak/api', [DombakController::class, 'api'])->name('dombak.api');
    $route->post('/master/dombak', [DombakController::class, 'store'])->name('dombak.store');
    $route->get('/master/dombak/{dombak}', [DombakController::class, 'show'])->name('dombak.show');
    $route->get('/master/dombak/{dombak}/edit', [DombakController::class, 'edit'])->name('dombak.edit');
    $route->put('/master/dombak/{dombak}', [DombakController::class, 'update'])->name('dombak.update');
    $route->delete('/master/dombak/{dombak}', [DombakController::class, 'destroy'])->name('dombak.destroy');

    $route->get('/master/dombak/konversi/api', [KonversiController::class, 'api'])->name('konversi.api');
    $route->get('/master/dombak/{dombak}/konversi/upload', [KonversiController::class, 'upload_show'])->name('konversi.upload_show');
    $route->post('/master/dombak/{dombak}/konversi/upload', [KonversiController::class, 'upload_store'])->name('konversi.upload_store');

    $route->get('/master/dombak/{dombak}/konversi', [KonversiController::class, 'index'])->name('konversi.index');
    $route->get('/master/dombak/{dombak}/konversi/create', [KonversiController::class, 'create'])->name('konversi.create');
    $route->delete('/master/dombak/{dombak}/konversi/delete', [KonversiController::class, 'destroy_all'])->name('konversi.destroy.all');
    $route->post('/master/dombak/{dombak}/konversi', [KonversiController::class, 'store'])->name('konversi.store');
    $route->get('/master/dombak/{dombak}/konversi/{konversi}', [KonversiController::class, 'show'])->name('konversi.show');
    $route->get('/master/dombak/{dombak}/konversi/{konversi}/edit', [KonversiController::class, 'edit'])->name('konversi.edit');
    $route->put('/master/dombak/{dombak}/konversi/{konversi}', [KonversiController::class, 'update'])->name('konversi.update');
    $route->delete('/master/dombak/{dombak}/konversi/{konversi}', [KonversiController::class, 'destroy'])->name('konversi.destroy');

    $route->get('/master/payung/{payung}/dombak', [PayungController::class, 'dombak'])->name('payung.dombak');
    $route->get('/master/payung/{payung}/dombak/create', [PayungController::class, 'dombak_create'])->name('payung.dombak.create');
    $route->post('/master/payung/{payung}/dombak/{dombak}', [PayungController::class, 'dombak_store'])->name('payung.dombak.store');
    $route->delete('/master/payung/{payung}/dombak/{dombak}', [PayungController::class, 'dombak_destroy'])->name('payung.dombak.delete');

    $route->get('/master/payung', [PayungController::class, 'index'])->name('payung.index');
    $route->get('/master/payung/create', [PayungController::class, 'create'])->name('payung.create');
    $route->post('/master/payung', [PayungController::class, 'store'])->name('payung.store');
    $route->get('/master/payung/{payung}', [PayungController::class, 'show'])->name('payung.show');
    $route->get('/master/payung/{payung}/edit', [PayungController::class, 'edit'])->name('payung.edit');
    $route->put('/master/payung/{payung}', [PayungController::class, 'update'])->name('payung.update');
    $route->delete('/master/payung/{payung}', [PayungController::class, 'destroy'])->name('payung.destroy');

    $route->get('/master/dispenser', [DispenserController::class, 'index'])->name('dispenser.index');
    $route->get('/master/dispenser/create', [DispenserController::class, 'create'])->name('dispenser.create');
    $route->post('/master/dispenser', [DispenserController::class, 'store'])->name('dispenser.store');
    $route->get('/master/dispenser/{dispenser}', [DispenserController::class, 'show'])->name('dispenser.show');
    $route->get('/master/dispenser/{dispenser}/edit', [DispenserController::class, 'edit'])->name('dispenser.edit');
    $route->put('/master/dispenser/{dispenser}', [DispenserController::class, 'update'])->name('dispenser.update');
    $route->delete('/master/dispenser/{dispenser}', [DispenserController::class, 'destroy'])->name('dispenser.destroy');

    $route->get('/master/nozzle', [NozzleController::class, 'index'])->name('nozzle.index');
    $route->get('/master/nozzle/create', [NozzleController::class, 'create'])->name('nozzle.create');
    $route->post('/master/nozzle', [NozzleController::class, 'store'])->name('nozzle.store');
    $route->get('/master/nozzle/{nozzle}', [NozzleController::class, 'show'])->name('nozzle.show');
    $route->get('/master/nozzle/{nozzle}/edit', [NozzleController::class, 'edit'])->name('nozzle.edit');
    $route->put('/master/nozzle/{nozzle}', [NozzleController::class, 'update'])->name('nozzle.update');
    $route->delete('/master/nozzle/{nozzle}', [NozzleController::class, 'destroy'])->name('nozzle.destroy');

    //Procurement
    $route->get('/procurement', ProcurementController::class)->name('menu.procurement');

    $route->get('/procurement/perencanaan', [RencanaPembelianController::class, 'index'])->name('perencanaan.index');
    $route->get('/procurement/perencanaan/create', [RencanaPembelianController::class, 'create'])->name('perencanaan.create');
    $route->get('/procurement/perencanaan/ditolak', [RencanaPembelianController::class, 'index_ditolak'])->name('perencanaan.ditolak');
    $route->get('/procurement/perencanaan/{rencana}/ditolak', [RencanaPembelianController::class, 'show_ditolak'])->name('perencanaan.ditolak.show');
    $route->post('/procurement/perencanaan', [RencanaPembelianController::class, 'store'])->name('perencanaan.store');
    $route->get('/procurement/perencanaan/{rencana}', [RencanaPembelianController::class, 'show'])->name('perencanaan.show');
    $route->get('/procurement/perencanaan/{rencana}/edit', [RencanaPembelianController::class, 'edit'])->name('perencanaan.edit');
    $route->put('/procurement/perencanaan/{rencana}', [RencanaPembelianController::class, 'update'])->name('perencanaan.update');
    $route->delete('/procurement/perencanaan/{rencana}', [RencanaPembelianController::class, 'destroy'])->name('perencanaan.destroy');
    $route->get('/procurement/perencanaan/{rencana}/konfirmasi', [RencanaPembelianController::class, 'konfirmasi'])->name('perencanaan.konfirmasi');
    $route->put('/procurement/perencanaan/{rencana}/konfirmasi', [RencanaPembelianController::class, 'konfirmasi_update'])->name('perencanaan.konfirmasi.update');

    $route->get('/procurement/perencanaan/{rencana}/produk', [DetailRencanaPembelianController::class, 'index'])->name('perencanaan.detail.index');
    $route->get('/procurement/perencanaan/{rencana}/produk/create', [DetailRencanaPembelianController::class, 'create'])->name('perencanaan.detail.create');
    $route->post('/procurement/perencanaan/{rencana}/produk', [DetailRencanaPembelianController::class, 'store'])->name('perencanaan.detail.store');
    $route->get('/procurement/perencanaan/{rencana}/produk/{detail}', [DetailRencanaPembelianController::class, 'show'])->name('perencanaan.detail.show');
    $route->get('/procurement/perencanaan/{rencana}/produk/{detail}/edit', [DetailRencanaPembelianController::class, 'edit'])->name('perencanaan.detail.edit');
    $route->put('/procurement/perencanaan/{rencana}/produk/{detail}', [DetailRencanaPembelianController::class, 'update'])->name('perencanaan.detail.update');
    $route->delete('/procurement/perencanaan/{rencana}/produk/{detail}', [DetailRencanaPembelianController::class, 'destroy'])->name('perencanaan.detail.destroy');

    $route->get('/procurement/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
    $route->get('/procurement/pembelian/{pembelian}/harga', [PembelianController::class, 'konfirmasi_harga'])->name('pembelian.konfirmasi');
    $route->get('/procurement/pembelian/{pembelian}/ulang', [PembelianController::class, 'konfirmasi_ulang'])->name('pembelian.perencanaan');
    $route->get('/procurement/pembelian/{pembelian}/detail', [PembelianController::class, 'show'])->name('pembelian.show');

    $route->get('/procurement/pembelian/{pembelian}/produk/pajak', [DetailPembelianController::class, 'edit_pajak'])->name('pembelian.detail.edit_pajak');
    $route->put('/procurement/pembelian/{pembelian}/produk/pajak', [DetailPembelianController::class, 'update_pajak'])->name('pembelian.detail.update_pajak');

    $route->get('/procurement/pembelian/{pembelian}/produk', [DetailPembelianController::class, 'index'])->name('pembelian.detail.index');
    $route->get('/procurement/pembelian/{pembelian}/produk/{detail}', [DetailPembelianController::class, 'show'])->name('pembelian.detail.show');
    $route->get('/procurement/pembelian/{pembelian}/produk/{detail}/edit', [DetailPembelianController::class, 'edit'])->name('pembelian.detail.edit');
    $route->put('/procurement/pembelian/{pembelian}/produk/{detail}', [DetailPembelianController::class, 'update'])->name('pembelian.detail.update');

    $route->get('/procurement/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    $route->get('/procurement/pembayaran/{rencana}', [PembayaranController::class, 'show'])->name('pembayaran.show');
    $route->put('/procurement/pembayaran/{rencana}/delivery', [PembayaranController::class, 'konfirmasi_delivery'])->name('pembayaran.delivery');
    $route->put('/procurement/pembayaran/{rencana}/ditolak', [PembayaranController::class, 'konfirmasi_ditolak'])->name('pembayaran.ditolak');

    $route->get('/procurement/pembayaran/{rencana}/pajak', [PembayaranController::class, 'pajak_edit'])->name('pembayaran.pajak_edit');
    $route->put('/procurement/pembayaran/{rencana}/pajak', [PembayaranController::class, 'pajak_update'])->name('pembayaran.pajak_update');

    $route->get('/procurement/pembayaran/{rencana}/tambah', [PembayaranController::class, 'create'])->name('pembayaran.create');
    $route->post('/procurement/pembayaran/{rencana}', [PembayaranController::class, 'store'])->name('pembayaran.store');
    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}', [PembayaranController::class, 'detail'])->name('pembayaran.detail');
    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}/edit', [PembayaranController::class, 'edit'])->name('pembayaran.edit');
    $route->put('/procurement/pembayaran/{rencana}/{pembayaran}', [PembayaranController::class, 'update'])->name('pembayaran.update');
    $route->put('/procurement/pembayaran/{rencana}/{pembayaran}/confirmation', [PembayaranController::class, 'update_confirmation'])->name('pembayaran.update.confirmation');
    $route->delete('/procurement/pembayaran/{rencana}/{pembayaran}', [PembayaranController::class, 'destroy'])->name('pembayaran.destroy');

    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}/tambah', [PembayaranController::class, 'file_create'])->name('pembayaran.file.create');
    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}/{file}/unduh', [PembayaranController::class, 'file_unduh'])->name('pembayaran.file.unduh');
    $route->post('/procurement/pembayaran/{rencana}/{pembayaran}', [PembayaranController::class, 'file_store'])->name('pembayaran.file.store');
    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}/{file}', [PembayaranController::class, 'file_show'])->name('pembayaran.file.show');
    $route->get('/procurement/pembayaran/{rencana}/{pembayaran}/{file}/edit', [PembayaranController::class, 'file_edit'])->name('pembayaran.file.edit');
    $route->put('/procurement/pembayaran/{rencana}/{pembayaran}/{file}/', [PembayaranController::class, 'file_update'])->name('pembayaran.file.update');
    $route->delete('/procurement/pembayaran/{rencana}/{pembayaran}/{file}', [PembayaranController::class, 'file_destroy'])->name('pembayaran.file.destroy');

    $route->get('/procurement/ms2/pengiriman/{pengiriman}/file_lo_show/{file_lo}/lampiran', [FileLampiranLoController::class, 'index'])->name('ms2.pengiriman.file_lo_show.lampiran.index');
    $route->get('/procurement/ms2/pengiriman/{pengiriman}/file_lo_show/{file_lo}/lampiran/create', [FileLampiranLoController::class, 'create'])->name('ms2.pengiriman.file_lo_show.lampiran.create');
    $route->post('/procurement/ms2/pengiriman/{pengiriman}/file_lo_show/{file_lo}/lampiran', [FileLampiranLoController::class, 'store'])->name('ms2.pengiriman.file_lo_show.lampiran.store');
    $route->get('/procurement/ms2/pengiriman/{pengiriman}/file_lo_show/{file_lo}/lampiran/{fileLampiranLo}/unduh', [FileLampiranLoController::class, 'unduh'])->name('ms2.pengiriman.file_lo_show.lampiran.unduh');
    $route->get('/procurement/ms2/pengiriman/{pengiriman}/file_lo_show/{file_lo}/lampiran/{fileLampiranLo}', [FileLampiranLoController::class, 'show'])->name('ms2.pengiriman.file_lo_show.lampiran.show');
    $route->get('/procurement/ms2/pengiriman/{pengiriman}/file_lo_show/{file_lo}/lampiran/{fileLampiranLo}/edit', [FileLampiranLoController::class, 'edit'])->name('ms2.pengiriman.file_lo_show.lampiran.edit');
    $route->put('/procurement/ms2/pengiriman/{pengiriman}/file_lo_show/{file_lo}/lampiran/{fileLampiranLo}', [FileLampiranLoController::class, 'update'])->name('ms2.pengiriman.file_lo_show.lampiran.update');
    $route->delete('/procurement/ms2/pengiriman/{pengiriman}/file_lo_show/{file_lo}/lampiran/{fileLampiranLo}', [FileLampiranLoController::class, 'destroy'])->name('ms2.pengiriman.file_lo_show.lampiran.destroy');

    $route->get('/procurement/ms2/pengiriman/{pengiriman}/file_lo_create', [Ms2Controller::class, 'file_lo_create'])->name('ms2.pengiriman.file_lo_create');
    $route->post('/procurement/ms2/pengiriman/{pengiriman}/file_lo_store', [Ms2Controller::class, 'file_lo_store'])->name('ms2.pengiriman.file_lo_store');
    $route->get('/procurement/ms2/pengiriman/{pengiriman}/file_lo_show/{file_lo}', [Ms2Controller::class, 'file_lo_show'])->name('ms2.pengiriman.file_lo_show');
    $route->delete('/procurement/ms2/pengiriman/{pengiriman}/file_lo_delete/{file_lo}', [Ms2Controller::class, 'file_lo_delete'])->name('ms2.pengiriman.file_lo_delete');

    $route->get('/procurement/ms2/pengiriman', [Ms2Controller::class, 'index_pengiriman'])->name('ms2.pengiriman');
    $route->get('/procurement/ms2/pengiriman/tambah', [Ms2Controller::class, 'create_pengiriman'])->name('ms2.pengiriman.create');
    $route->post('/procurement/ms2/pengiriman/', [Ms2Controller::class, 'store_pengiriman'])->name('ms2.pengiriman.store');
    $route->get('/procurement/ms2/pengiriman/{pengiriman}', [Ms2Controller::class, 'show_pengiriman'])->name('ms2.pengiriman.show');
    $route->get('/procurement/ms2/pengiriman/{pengiriman}/edit', [Ms2Controller::class, 'edit_pengiriman'])->name('ms2.pengiriman.edit');
    $route->put('/procurement/ms2/pengiriman/{pengiriman}', [Ms2Controller::class, 'update_pengiriman'])->name('ms2.pengiriman.update');
    $route->delete('/procurement/ms2/pengiriman/{pengiriman}', [Ms2Controller::class, 'destroy_pengiriman'])->name('ms2.pengiriman.delete');
    $route->put('/procurement/ms2/pengiriman/{pengiriman}/update-pengiriman', [Ms2Controller::class, 'update_pengiriman_to_delivery'])->name('ms2.pengiriman.update_pengiriman_to_delivery');

    $route->get('/procurement/ms2', [Ms2Controller::class, 'index_produk'])->name('ms2.produk.index');
    $route->get('/procurement/ms2/{produk}', [Ms2Controller::class, 'show_produk'])->name('ms2.produk.show');
    $route->get('/procurement/ms2/{produk}/{detail}', [Ms2Controller::class, 'show_detail'])->name('ms2.detail.show');

    $route->get('/procurement/delivery/{pengiriman}/{file_lo}', [PengirimanController::class, 'show_file_lo'])->name('pengiriman.file_lo.show');

    $route->get('/procurement/delivery', [PengirimanController::class, 'index'])->name('pengiriman.index');
    $route->get('/procurement/delivery/{pengiriman}', [PengirimanController::class, 'show'])->name('pengiriman.show');
    $route->put('/procurement/delivery/{pengiriman}/done', [PengirimanController::class, 'done'])->name('pengiriman.done');
    $route->delete('/procurement/delivery/{pengiriman}', [PengirimanController::class, 'destroy'])->name('pengiriman.destroy');

    // Manajemen Persediaan
    $route->get('/stok', StokController::class)->name('menu.stok');

    $route->get('/stok/monitoring/{stok}/riwayat', [MonitoringController::class, 'show_riwayat'])->name('monitoring.show_riwayat');
    $route->get('/stok/monitoring/{stok}/riwayat/{dokumenStok}', [MonitoringController::class, 'show_detail_riwayat'])->name('monitoring.show_detail_riwayat');

    $route->get('/stok/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    $route->get('/stok/monitoring/create', [MonitoringController::class, 'create'])->name('monitoring.create');
    $route->post('/stok/monitoring', [MonitoringController::class, 'store'])->name('monitoring.store');
    $route->get('/stok/monitoring/{stok}', [MonitoringController::class, 'show'])->name('monitoring.show');
    $route->get('/stok/monitoring/{stok}/edit', [MonitoringController::class, 'edit'])->name('monitoring.edit');
    $route->put('/stok/monitoring/{stok}', [MonitoringController::class, 'update'])->name('monitoring.update');
    $route->delete('/stok/monitoring/{stok}', [MonitoringController::class, 'destroy'])->name('monitoring.delete');

    $route->get('/stok/monitoring/{stok}/dombak', [MonitoringController::class, 'dombak_index'])->name('monitoring.dombak.index');
    $route->get('/stok/monitoring/{stok}/dombak/create', [MonitoringController::class, 'dombak_create'])->name('monitoring.dombak.create');
    $route->post('/stok/monitoring/{stok}/dombak/{dombak}', [MonitoringController::class, 'dombak_store'])->name('monitoring.dombak.store');
    $route->delete('/stok/monitoring/{stok}/dombak/{dombak}', [MonitoringController::class, 'dombak_destroy'])->name('monitoring.dombak.delete');

    $route->get('/stok/penerimaan/riwayat', [PenerimaanController::class, 'riwayat'])->name('penerimaan.riwayat');
    $route->get('/stok/penerimaan/riwayat/{penerimaan}', [PenerimaanController::class, 'riwayat_show'])->name('penerimaan.riwayat.show');

    $route->get('/stok/penerimaan/verifikasi', [PenerimaanController::class, 'verifikasi'])->name('penerimaan.verifikasi');
    $route->get('/stok/penerimaan/verifikasi/{penerimaan}', [PenerimaanController::class, 'verifikasi_show'])->name('penerimaan.verifikasi.show');

    $route->get('/stok/penerimaan', [PenerimaanController::class, 'index'])->name('penerimaan.index');
    $route->get('/stok/penerimaan/{fileLo}', [PenerimaanController::class, 'show'])->name('penerimaan.show');
    $route->post('/stok/penerimaan/{fileLo}', [PenerimaanController::class, 'store'])->name('penerimaan.store');
    $route->get('/stok/penerimaan/{penerimaan}/show', [PenerimaanController::class, 'show_penerimaan'])->name('penerimaan.show_penerimaan');
    $route->put('/stok/penerimaan/{penerimaan}/show', [PenerimaanController::class, 'confirmation'])->name('penerimaan.show_confirmation');
    $route->delete('/stok/penerimaan/{penerimaan}', [PenerimaanController::class, 'destroy'])->name('penerimaan.delete');

    $route->get('/stok/penerimaan/riwayat/{penerimaan}/file', [FilePenerimaanController::class, 'index_riwayat'])->name('penerimaan.file.riwayat.index');
    $route->get('/stok/penerimaan/riwayat/{penerimaan}/file/{file}', [FilePenerimaanController::class, 'show_riwayat'])->name('penerimaan.file.riwayat.show');

    $route->get('/stok/penerimaan/verifikasi/{penerimaan}/file', [FilePenerimaanController::class, 'index'])->name('penerimaan.file.index');
    $route->get('/stok/penerimaan/verifikasi/{penerimaan}/file/create', [FilePenerimaanController::class, 'create'])->name('penerimaan.file.create');
    $route->post('/stok/penerimaan/verifikasi/{penerimaan}/file', [FilePenerimaanController::class, 'store'])->name('penerimaan.file.store');
    $route->get('/stok/penerimaan/verifikasi/{penerimaan}/file/{file}', [FilePenerimaanController::class, 'show'])->name('penerimaan.file.show');
    $route->get('/stok/penerimaan/verifikasi/{penerimaan}/file/{file}/download', [FilePenerimaanController::class, 'download'])->name('penerimaan.file.download');
    $route->get('/stok/penerimaan/verifikasi/{penerimaan}/file/{file}/edit', [FilePenerimaanController::class, 'edit'])->name('penerimaan.file.edit');
    $route->put('/stok/penerimaan/verifikasi/{penerimaan}/file/{file}', [FilePenerimaanController::class, 'update'])->name('penerimaan.file.update');
    $route->delete('/stok/penerimaan/verifikasi/{penerimaan}/file/{file}', [FilePenerimaanController::class, 'destroy'])->name('penerimaan.file.delete');

    $route->get('/stok/stok-transfer/riwayat', [StokTransferController::class, 'riwayat'])->name('stok-transfer.riwayat');
    $route->get('/stok/stok-transfer/riwayat/{stok}', [StokTransferController::class, 'riwayat_show'])->name('stok-transfer.riwayat.show');

    $route->get('/stok/stok-transfer', [StokTransferController::class, 'index'])->name('stok-transfer.index');
    $route->get('/stok/stok-transfer/create', [StokTransferController::class, 'create'])->name('stok-transfer.create');
    $route->post('/stok/stok-transfer', [StokTransferController::class, 'store'])->name('stok-transfer.store');
    $route->get('/stok/stok-transfer/{dombakTransfer}', [StokTransferController::class, 'show'])->name('stok-transfer.show');
    $route->put('/stok/stok-transfer/{dombakTransfer}/confirmation', [StokTransferController::class, 'confirmation_stok_transfer'])->name('stok-transfer.confirmation');
    $route->get('/stok/stok-transfer/{dombakTransfer}/edit', [StokTransferController::class, 'edit'])->name('stok-transfer.edit');
    $route->put('/stok/stok-transfer/{dombakTransfer}', [StokTransferController::class, 'update'])->name('stok-transfer.update');
    $route->delete('/stok/stok-transfer/{dombakTransfer}', [StokTransferController::class, 'destroy'])->name('stok-transfer.delete');

    $route->get('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file', [FileStokTransferController::class, 'index'])->name('stok-transfer.file.index');
    $route->get('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/create', [FileStokTransferController::class, 'create'])->name('stok-transfer.file.create');
    $route->post('/stok/stok-transfer{dombakTransfer}/stok/{dokumenStok}/file', [FileStokTransferController::class, 'store'])->name('stok-transfer.file.store');
    $route->get('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/{fileDokumenStok}', [FileStokTransferController::class, 'show'])->name('stok-transfer.file.show');
    $route->get('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/{fileDokumenStok}/download', [FileStokTransferController::class, 'download'])->name('stok-transfer.file.download');
    $route->get('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/{fileDokumenStok}/edit', [FileStokTransferController::class, 'edit'])->name('stok-transfer.file.edit');
    $route->put('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/{fileDokumenStok}', [FileStokTransferController::class, 'update'])->name('stok-transfer.file.update');
    $route->delete('/stok/stok-transfer/{dombakTransfer}/stok/{dokumenStok}/file/{fileDokumenStok}', [FileStokTransferController::class, 'destroy'])->name('stok-transfer.file.delete');


    $route->get('/stok/stok-taking/riwayat', [StokTakingController::class, 'riwayat'])->name('stok-taking.riwayat');
    $route->get('/stok/stok-taking/riwayat/{stok}', [StokTakingController::class, 'riwayat_show'])->name('stok-taking.riwayat.show');

    $route->get('/stok/stok-taking', [StokTakingController::class, 'index'])->name('stok-taking.index');
    $route->get('/stok/stok-taking/create', [StokTakingController::class, 'create'])->name('stok-taking.create');
    $route->post('/stok/stok-taking', [StokTakingController::class, 'store'])->name('stok-taking.store');
    $route->get('/stok/stok-taking/{stok}', [StokTakingController::class, 'show'])->name('stok-taking.show');
    $route->put('/stok/stok-taking/{stok}/confirmation', [StokTakingController::class, 'confirmation_stok_taking'])->name('stok-taking.confirmation');
    $route->get('/stok/stok-taking/{stok}/edit', [StokTakingController::class, 'edit'])->name('stok-taking.edit');
    $route->put('/stok/stok-taking/{stok}', [StokTakingController::class, 'update'])->name('stok-taking.update');
    $route->delete('/stok/stok-taking/{stok}', [StokTakingController::class, 'destroy'])->name('stok-taking.delete');

    $route->get('/stok/stok-taking/{stok}/file', [FileDokumenStokController::class, 'index'])->name('stok-taking.file.index');
    $route->get('/stok/stok-taking/{stok}/file/create', [FileDokumenStokController::class, 'create'])->name('stok-taking.file.create');
    $route->post('/stok/stok-taking{stok}/file', [FileDokumenStokController::class, 'store'])->name('stok-taking.file.store');
    $route->get('/stok/stok-taking/{stok}/file/{file}', [FileDokumenStokController::class, 'show'])->name('stok-taking.file.show');
    $route->get('/stok/stok-taking/{stok}/file/{file}/download', [FileDokumenStokController::class, 'download'])->name('stok-taking.file.download');
    $route->get('/stok/stok-taking/{stok}/file/{file}/edit', [FileDokumenStokController::class, 'edit'])->name('stok-taking.file.edit');
    $route->put('/stok/stok-taking/{stok}/file/{file}', [FileDokumenStokController::class, 'update'])->name('stok-taking.file.update');
    $route->delete('/stok/stok-taking/{stok}/file/{file}', [FileDokumenStokController::class, 'destroy'])->name('stok-taking.file.delete');

    $route->get('/stok/totalisator/riwayat', [StandMeterController::class, 'riwayat'])->name('totalisator.riwayat');
    $route->get('/stok/totalisator/riwayat/{standMeter}', [StandMeterController::class, 'riwayat_show'])->name('totalisator.riwayat_show');

    $route->get('/stok/totalisator', [StandMeterController::class, 'index'])->name('totalisator.index');
    $route->get('/stok/totalisator/create', [StandMeterController::class, 'create'])->name('totalisator.create');
    $route->get('/stok/totalisator/create/api', [StandMeterController::class, 'create_api'])->name('totalisator.create_api');
    $route->post('/stok/totalisator', [StandMeterController::class, 'store'])->name('totalisator.store');
    $route->get('/stok/totalisator/{standMeter}', [StandMeterController::class, 'show'])->name('totalisator.show');
    $route->get('/stok/totalisator/{standMeter}/edit', [StandMeterController::class, 'edit'])->name('totalisator.edit');
    $route->get('/stok/totalisator/{standMeter}/final', [StandMeterController::class, 'final'])->name('totalisator.final');
    $route->put('/stok/totalisator/{standMeter}/final', [StandMeterController::class, 'final_update'])->name('totalisator.final_update');
    $route->put('/stok/totalisator/{standMeter}/confirmation', [StandMeterController::class, 'confirmation'])->name('totalisator.confirmation');
    $route->put('/stok/totalisator/{standMeter}', [StandMeterController::class, 'update'])->name('totalisator.update');
    $route->delete('/stok/totalisator/{standMeter}', [StandMeterController::class, 'destroy'])->name('totalisator.delete');

    $route->get('/stok/totalisator/{standMeter}/file/{file}/download', [FileStandMeterController::class, 'download'])->name('totalisator.file.download');
    $route->get('/stok/totalisator/{standMeter}/file', [FileStandMeterController::class, 'index'])->name('totalisator.file.index');
    $route->get('/stok/totalisator/{standMeter}/file/create', [FileStandMeterController::class, 'create'])->name('totalisator.file.create');
    $route->post('/stok/totalisator/{standMeter}/file', [FileStandMeterController::class, 'store'])->name('totalisator.file.store');
    $route->get('/stok/totalisator/{standMeter}/file/{file}', [FileStandMeterController::class, 'show'])->name('totalisator.file.show');
    $route->get('/stok/totalisator/{standMeter}/file/{file}/edit', [FileStandMeterController::class, 'edit'])->name('totalisator.file.edit');
    $route->put('/stok/totalisator/{standMeter}/file/{file}', [FileStandMeterController::class, 'update'])->name('totalisator.file.update');
    $route->delete('/stok/totalisator/{standMeter}/file/{file}', [FileStandMeterController::class, 'destroy'])->name('totalisator.file.delete');

    $route->get('/stok/totalisator/riwayat/{standMeter}/file', [FileStandMeterController::class, 'riwayat_index'])->name('totalisator.file.riwayat.index');
    $route->get('/stok/totalisator/riwayat/{standMeter}/file/create', [FileStandMeterController::class, 'riwayat_create'])->name('totalisator.file.riwayat.create');
    $route->post('/stok/totalisator/riwayat/{standMeter}/file', [FileStandMeterController::class, 'riwayat_store'])->name('totalisator.file.riwayat.store');
    $route->get('/stok/totalisator/riwayat/{standMeter}/file/{file}', [FileStandMeterController::class, 'riwayat_show'])->name('totalisator.file.riwayat.show');
    $route->get('/stok/totalisator/riwayat/{standMeter}/file/{file}/edit', [FileStandMeterController::class, 'riwayat_edit'])->name('totalisator.file.riwayat.edit');
    $route->put('/stok/totalisator/riwayat/{standMeter}/file/{file}', [FileStandMeterController::class, 'riwayat_update'])->name('totalisator.file.riwayat.update');
    $route->delete('/stok/totalisator/riwayat/{standMeter}/file/{file}', [FileStandMeterController::class, 'riwayat_destroy'])->name('totalisator.file.riwayat.delete');

    // Tera
    $route->get('/tera', TeraController::class)->name('menu.tera');
    $route->get('/tera/stok-peminjaman', [StokPeminjamanController::class, 'index'])->name('stok-peminjaman.*');
    $route->get('/tera/stok-pengembalian', [StokPengembalianController::class, 'index'])->name('stok-pengembalian.*');

    //Setoran
    $route->get('/setoran', SetoranController::class)->name('menu.setoran');

    $route->get('/setoran/open-penjualan', [OpenPenjualanController::class, 'index'])->name('setoran.open');
    $route->get('/setoran/open-penjualan/create', [OpenPenjualanController::class, 'create'])->name('setoran.open.create');
    $route->post('/setoran/open-penjualan', [OpenPenjualanController::class, 'store'])->name('setoran.open.store');
    $route->get('/setoran/open-penjualan/{penjualan}', [OpenPenjualanController::class, 'show'])->name('setoran.open.show');
    $route->get('/setoran/open-penjualan/{penjualan}/edit', [OpenPenjualanController::class, 'edit'])->name('setoran.open.edit');
    $route->put('/setoran/open-penjualan/{penjualan}', [OpenPenjualanController::class, 'update'])->name('setoran.open.update');
    $route->put('/setoran/open-penjualan/{penjualan}/status', [OpenPenjualanController::class, 'update_status'])->name('setoran.open.update.status');
    $route->delete('/setoran/open-penjualan/{penjualan}', [OpenPenjualanController::class, 'destroy'])->name('setoran.open.delete');

    $route->get('/setoran/open-penjualan/{penjualan}/detail', [OpenPenjualanDetailController::class, 'index'])->name('setoran.open.detail');
    $route->get('/setoran/open-penjualan/{penjualan}/detail/create', [OpenPenjualanDetailController::class, 'create'])->name('setoran.open.detail.create');
    $route->get('/setoran/open-penjualan/{penjualan}/detail/create/api', [OpenPenjualanDetailController::class, 'create_api'])->name('setoran.open.detail.create.api');
    $route->post('/setoran/open-penjualan/{penjualan}/detail', [OpenPenjualanDetailController::class, 'store'])->name('setoran.open.detail.store');
    $route->get('/setoran/open-penjualan/{penjualan}/detail/{detail}', [OpenPenjualanDetailController::class, 'show'])->name('setoran.open.detail.show');
    $route->get('/setoran/open-penjualan/{penjualan}/detail/{detail}/edit', [OpenPenjualanDetailController::class, 'edit'])->name('setoran.open.detail.edit');
    $route->put('/setoran/open-penjualan/{penjualan}/detail/{detail}', [OpenPenjualanDetailController::class, 'update'])->name('setoran.open.detail.update');
    $route->delete('/setoran/open-penjualan/{penjualan}/detail/{detail}', [OpenPenjualanDetailController::class, 'destroy'])->name('setoran.open.detail.delete');

    $route->get('/setoran/penitipan-setoran', [PenitipanSetoranController::class, 'index_open_penjualan'])->name('setoran.penitipan');
    $route->get('/setoran/penitipan-setoran/{penjualan}', [PenitipanSetoranController::class, 'show_open_penjualan'])->name('setoran.penitipan.show');
    $route->put('/setoran/penitipan-setoran/{penjualan}', [PenitipanSetoranController::class, 'update_closing'])->name('setoran.penitipan.update_closing');

    $route->get('/setoran/penitipan-setoran/{penjualan}/penitipan', [PenitipanSetoranController::class, 'index'])->name('setoran.penitipan.index_penitipan');
    $route->get('/setoran/penitipan-setoran/{penjualan}/penitipan/create', [PenitipanSetoranController::class, 'create'])->name('setoran.penitipan.create');
    $route->post('/setoran/penitipan-setoran/{penjualan}/penitipan', [PenitipanSetoranController::class, 'store'])->name('setoran.penitipan.store');
    $route->get('/setoran/penitipan-setoran/{penjualan}/penitipan/{penitipan}', [PenitipanSetoranController::class, 'show'])->name('setoran.penitipan.show_penitipan');
    $route->get('/setoran/penitipan-setoran/{penjualan}/penitipan/{penitipan}/edit', [PenitipanSetoranController::class, 'edit'])->name('setoran.penitipan.edit');
    $route->put('/setoran/penitipan-setoran/{penjualan}/penitipan/{penitipan}', [PenitipanSetoranController::class, 'update'])->name('setoran.penitipan.update');
    $route->put('/setoran/penitipan-setoran/{penjualan}/penitipan/{penitipan}/confirmation', [PenitipanSetoranController::class, 'update_confirmation'])->name('setoran.penitipan.update_confirmation');
    $route->delete('/setoran/penitipan-setoran/{penjualan}/penitipan/{penitipan}', [PenitipanSetoranController::class, 'destroy'])->name('setoran.penitipan.delete');

    $route->get('/setoran/penitipan-setoran/{penjualan}/penitipan/{penitipan}/file', [FilePenitipanSetoranController::class, 'index'])->name('setoran.penitipan.file.index');
    $route->get('/setoran/penitipan-setoran/{penjualan}/penitipan/{penitipan}/file/create', [FilePenitipanSetoranController::class, 'create'])->name('setoran.penitipan.file.create');
    $route->post('/setoran/penitipan-setoran/{penjualan}/penitipan/{penitipan}/file', [FilePenitipanSetoranController::class, 'store'])->name('setoran.penitipan.file.store');
    $route->delete('/setoran/penitipan-setoran/{penjualan}/penitipan/{penitipan}/file/{file}', [FilePenitipanSetoranController::class, 'destroy'])->name('setoran.penitipan.file.delete');
    $route->get('/setoran/penitipan-setoran/{penjualan}/penitipan/{penitipan}/file/{file}', [FilePenitipanSetoranController::class, 'download'])->name('setoran.penitipan.file.download');

    $route->get('/setoran/closing-penjualan', [ClosingPenjualanController::class, 'index'])->name('setoran.closing');
    $route->get('/setoran/closing-penjualan/{penjualan}', [ClosingPenjualanController::class, 'show'])->name('setoran.closing.show');
    $route->put('/setoran/closing-penjualan/{penjualan}', [ClosingPenjualanController::class, 'update'])->name('setoran.closing.update');
    $route->delete('/setoran/closing-penjualan/{penjualan}', [ClosingPenjualanController::class, 'destroy'])->name('setoran.closing.delete');

    // Artikel
    $route->get('/artikel', ArtikelController::class)->name('menu.artikel');

    $route->get('/artikel/tag', [TagController::class, 'index'])->name('tag.index');
    $route->get('/artikel/tag/create', [TagController::class, 'create'])->name('tag.create');
    $route->post('/artikel/tag', [TagController::class, 'store'])->name('tag.store');
    $route->get('/artikel/tag/{tag}', [TagController::class, 'show'])->name('tag.show');
    $route->get('/artikel/tag/{tag}/edit', [TagController::class, 'edit'])->name('tag.edit');
    $route->put('/artikel/tag/{tag}', [TagController::class, 'update'])->name('tag.update');
    $route->delete('/artikel/tag/{tag}', [TagController::class, 'destroy'])->name('tag.delete');

    $route->get('/artikel/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    $route->get('/artikel/kategori/create', [KategoriController::class, 'create'])->name('kategori.create');
    $route->post('/artikel/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    $route->get('/artikel/kategori/{kategori}', [KategoriController::class, 'show'])->name('kategori.show');
    $route->get('/artikel/kategori/{kategori}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
    $route->put('/artikel/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
    $route->delete('/artikel/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.delete');

    $route->get('/artikel/post', [PostController::class, 'index'])->name('post.index');
    $route->get('/artikel/post/create', [PostController::class, 'create'])->name('post.create');
    $route->post('/artikel/post', [PostController::class, 'store'])->name('post.store');
    $route->get('/artikel/post/{post}', [PostController::class, 'show'])->name('post.show');
    $route->get('/artikel/post/{post}/edit', [PostController::class, 'edit'])->name('post.edit');
    $route->put('/artikel/post/{post}', [PostController::class, 'update'])->name('post.update');
    $route->delete('/artikel/post/{post}', [PostController::class, 'destroy'])->name('post.delete');

    $route->get('/artikel/post/{post}/file', [FilePostController::class, 'index'])->name('file.post.index');
    $route->get('/artikel/post/{post}/file/create', [FilePostController::class, 'create'])->name('file.post.create');
    $route->post('/artikel/post/{post}/file', [FilePostController::class, 'store'])->name('file.post.store');
    $route->get('/artikel/post/{post}/file/{filePost}/download', [FilePostController::class, 'download'])->name('file.post.download');
    $route->get('/artikel/post/{post}/file/{filePost}', [FilePostController::class, 'show'])->name('file.post.show');
    $route->get('/artikel/post/{post}/file/{filePost}/edit', [FilePostController::class, 'edit'])->name('file.post.edit');
    $route->put('/artikel/post/{post}/file/{filePost}', [FilePostController::class, 'update'])->name('file.post.update');
    $route->delete('/artikel/post/{post}/file/{filePost}', [FilePostController::class, 'destroy'])->name('file.post.delete');

    // Financial
    $route->get('/financial', FinancialController::class)->name('menu.financial');

    $route->get('/financial/petty-cash', [PettyCashController::class, 'index'])->name('financial.petty-cash');
    $route->get('/financial/petty-cash/create', [PettyCashController::class, 'create'])->name('petty-cash.create');
    $route->post('/financial/petty-cash', [PettyCashController::class, 'store'])->name('petty-cash.store');
    $route->get('/financial/petty-cash/{pettyCash}', [PettyCashController::class, 'show'])->name('petty-cash.show');
    $route->get('/financial/petty-cash/{pettyCash}/edit', [PettyCashController::class, 'edit'])->name('petty-cash.edit');
    $route->put('/financial/petty-cash/{pettyCash}', [PettyCashController::class, 'update'])->name('petty-cash.update');
    $route->delete('/financial/petty-cash/{pettyCash}', [PettyCashController::class, 'destroy'])->name('petty-cash.delete');

    $route->get('/financial/petty-cash/{pettyCash}/file', [FilePettyCashController::class, 'index'])->name('financial.petty-cash.file');
    $route->get('/financial/petty-cash/{pettyCash}/file/create', [FilePettyCashController::class, 'create'])->name('petty-cash.file.create');
    $route->post('/financial/petty-cash/{pettyCash}/file', [FilePettyCashController::class, 'store'])->name('petty-cash.file.store');
    $route->get('/financial/petty-cash/{pettyCash}/file/{file}', [FilePettyCashController::class, 'show'])->name('petty-cash.file.show');
    $route->get('/financial/petty-cash/{pettyCash}/file/{file}/download', [FilePettyCashController::class, 'download'])->name('petty-cash.file.download');
    $route->get('/financial/petty-cash/{pettyCash}/file/{file}/edit', [FilePettyCashController::class, 'edit'])->name('petty-cash.file.edit');
    $route->put('/financial/petty-cash/{pettyCash}/file/{file}', [FilePettyCashController::class, 'update'])->name('petty-cash.file.update');
    $route->delete('/financial/petty-cash/{pettyCash}/file/{file}', [FilePettyCashController::class, 'destroy'])->name('petty-cash.file.delete');

    $route->get('/financial/rekap-pajak', [RekapPajakController::class, 'index'])->name('rekap-pajak.index');
    $route->post('/financial/rekap-pajak/export', [RekapPajakController::class, 'export'])->name('rekap-pajak.export');
    $route->get('/financial/rekap-pajak/{pajakPembelian}', [RekapPajakController::class, 'show'])->name('rekap-pajak.show');

    $route->get('/financial/margin', [MarginController::class, 'index'])->name('margin.index');

    $route->get('/financial/laba-rugi', [LabaRugiController::class, 'index'])->name('laba-rugi.index');
    $route->get('/financial/neraca', [NeracaController::class, 'index'])->name('neraca.index');
    $route->get('/financial/buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
    $route->get('/financial/arus-kas', [ArusKasController::class, 'index'])->name('arus-kas.index');
    $route->get('/financial/perubahan-modal', [PerubahanModalController::class, 'index'])->name('perubahan-modal.index');

    // Konfigurasi
    $route->get('/konfigurasi', KonfigurasiController::class)->name('menu.konfigurasi');

    $route->get('/konfigurasi/spbu', [SpbuController::class, 'index'])->name('spbu.index');
    $route->get('/konfigurasi/spbu/create', [SpbuController::class, 'create'])->name('spbu.create');
    $route->post('/konfigurasi/spbu', [SpbuController::class, 'store'])->name('spbu.store');
    $route->get('/konfigurasi/spbu/{spbu}', [SpbuController::class, 'show'])->name('spbu.show');
    $route->get('/konfigurasi/spbu/{spbu}/edit', [SpbuController::class, 'edit'])->name('spbu.edit');
    $route->put('/konfigurasi/spbu/{spbu}', [SpbuController::class, 'update'])->name('spbu.update');
    $route->delete('/konfigurasi/spbu/{spbu}', [SpbuController::class, 'destroy'])->name('spbu.destroy');

    $route->get('/konfigurasi/divisi', [DivisiController::class, 'index'])->name('divisi.index');
    $route->get('/konfigurasi/divisi/create', [DivisiController::class, 'create'])->name('divisi.create');
    $route->post('/konfigurasi/divisi', [DivisiController::class, 'store'])->name('divisi.store');
    $route->get('/konfigurasi/divisi/{divisi}', [DivisiController::class, 'show'])->name('divisi.show');
    $route->get('/konfigurasi/divisi/{divisi}/edit', [DivisiController::class, 'edit'])->name('divisi.edit');
    $route->put('/konfigurasi/divisi/{divisi}', [DivisiController::class, 'update'])->name('divisi.update');
    $route->delete('/konfigurasi/divisi/{divisi}', [DivisiController::class, 'destroy'])->name('divisi.destroy');

    $route->get('/konfigurasi/level', [LevelController::class, 'index'])->name('level.index');
    $route->get('/konfigurasi/level/create', [LevelController::class, 'create'])->name('level.create');
    $route->post('/konfigurasi/level', [LevelController::class, 'store'])->name('level.store');
    $route->get('/konfigurasi/level/{level}', [LevelController::class, 'show'])->name('level.show');
    $route->get('/konfigurasi/level/{level}/edit', [LevelController::class, 'edit'])->name('level.edit');
    $route->put('/konfigurasi/level/{level}', [LevelController::class, 'update'])->name('level.update');
    $route->delete('/konfigurasi/level/{level}', [LevelController::class, 'destroy'])->name('level.destroy');

    $route->get('/konfigurasi/jabatan', [JabatanController::class, 'index'])->name('jabatan.index');
    $route->get('/konfigurasi/jabatan/create', [JabatanController::class, 'create'])->name('jabatan.create');
    $route->post('/konfigurasi/jabatan', [JabatanController::class, 'store'])->name('jabatan.store');
    $route->get('/konfigurasi/jabatan/{jabatan}', [JabatanController::class, 'show'])->name('jabatan.show');
    $route->get('/konfigurasi/jabatan/{jabatan}/edit', [JabatanController::class, 'edit'])->name('jabatan.edit');
    $route->put('/konfigurasi/jabatan/{jabatan}', [JabatanController::class, 'update'])->name('jabatan.update');
    $route->delete('/konfigurasi/jabatan/{jabatan}', [JabatanController::class, 'destroy'])->name('jabatan.destroy');

    $route->get('/konfigurasi/hari', [HariController::class, 'index'])->name('hari.index');
    $route->get('/konfigurasi/hari/create', [HariController::class, 'create'])->name('hari.create');
    $route->post('/konfigurasi/hari', [HariController::class, 'store'])->name('hari.store');
    $route->get('/konfigurasi/hari/{hari}', [HariController::class, 'show'])->name('hari.show');
    $route->get('/konfigurasi/hari/{hari}/edit', [HariController::class, 'edit'])->name('hari.edit');
    $route->put('/konfigurasi/hari/{hari}', [HariController::class, 'update'])->name('hari.update');
    $route->delete('/konfigurasi/hari/{hari}', [HariController::class, 'destroy'])->name('hari.destroy');

    $route->get('/konfigurasi/pajak', [PajakPembelianLibController::class, 'index'])->name('pajak_pembelian_lib.index');
    $route->get('/konfigurasi/pajak/create', [PajakPembelianLibController::class, 'create'])->name('pajak_pembelian_lib.create');
    $route->post('/konfigurasi/pajak', [PajakPembelianLibController::class, 'store'])->name('pajak_pembelian_lib.store');
    $route->get('/konfigurasi/pajak/{pajak}', [PajakPembelianLibController::class, 'show'])->name('pajak_pembelian_lib.show');
    $route->get('/konfigurasi/pajak/{pajak}/edit', [PajakPembelianLibController::class, 'edit'])->name('pajak_pembelian_lib.edit');
    $route->put('/konfigurasi/pajak/{pajak}', [PajakPembelianLibController::class, 'update'])->name('pajak_pembelian_lib.update');
    $route->delete('/konfigurasi/pajak/{pajak}', [PajakPembelianLibController::class, 'destroy'])->name('pajak_pembelian_lib.destroy');

    $route->get('/konfigurasi/jam_kerja', [JamKerjaController::class, 'index'])->name('jam_kerja.index');
    $route->get('/konfigurasi/jam_kerja/create', [JamKerjaController::class, 'create'])->name('jam_kerja.create');
    $route->post('/konfigurasi/jam_kerja', [JamKerjaController::class, 'store'])->name('jam_kerja.store');
    $route->get('/konfigurasi/jam_kerja/{jamKerja}', [JamKerjaController::class, 'show'])->name('jam_kerja.show');
    $route->get('/konfigurasi/jam_kerja/{jamKerja}/edit', [JamKerjaController::class, 'edit'])->name('jam_kerja.edit');
    $route->put('/konfigurasi/jam_kerja/{jamKerja}', [JamKerjaController::class, 'update'])->name('jam_kerja.update');
    $route->delete('/konfigurasi/jam_kerja/{jamKerja}', [JamKerjaController::class, 'destroy'])->name('jam_kerja.destroy');

    $route->get('/konfigurasi/import', [ImportController::class, 'index'])->name('import.index');
    $route->post('/konfigurasi/import', [ImportController::class, 'store'])->name('import.store');

    // Operator
    $route->get('/stand-meter', [StandMeterOperatorController::class, 'index'])->name('menu.stand-meter');

    $route->get('/setoran-uang', [SetoranUangOperatorController::class, 'index'])->name('menu.setoran-uang');

    $route->get('/kehadiran', [KehadiranOperatorController::class, 'index'])->name('menu.kehadiran');
});

Route::get('/', [App\Http\Controllers\WelcomeController::class, 'index'])->name('welcome');
Route::get('/kategori/{kategori}', [App\Http\Controllers\WelcomeController::class, 'kategori'])->name('welcome.kategori');
Route::get('/tag/{tag}', [App\Http\Controllers\WelcomeController::class, 'tag'])->name('welcome.tag');
Route::get('/user/{member}', [App\Http\Controllers\WelcomeController::class, 'user'])->name('welcome.user');
Route::get('/{post}', [App\Http\Controllers\WelcomeController::class, 'show'])->name('welcome.show');
