@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Stok Taking</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-taking.index') }}">Stok Taking</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-taking.riwayat') }}">Riwayat</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Stok Taking</h2>
    <p class="section-lead">
        Detail data Stok Taking anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Stok Taking') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('stok-taking.file.index', $stok->dokumen_stok_id) }}" class="btn btn-primary">
                            File
                        </a>
                        <div class="badge badge-primary">{{ $stok->is_confirmation ? 'Terkofirmasi' : 'Belum Terkonfirmasi' }}</div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="type_dokumen_id" class="col-md-4 col-form-label text-md-end">{{ __('Tipe Dokumen')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="type_dokumen_id" type="text" readonly class="form-control"
                                        value="{{ $stok->type_dokumen_stok()->first()->type_movement }}" >
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="dombak_id" class="col-md-4 col-form-label text-md-end">{{ __('Dombak')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="dombak_id" type="text" readonly class="form-control"
                                        value="{{ $stok->stok_inventory()->first()->stok_inventory_dombak()->first()->dombak()->first()->nama_dombak . ' ' . $stok->stok_inventory()->first()->stok_inventory_dombak()->first()->nama_dombak . ' ('. $stok->stok_inventory()->first()->stok_inventory_dombak()->first()->stok_inventory()->first()->produk()->first()->nama_produk . '-' . $stok->stok_inventory()->first()->stok_inventory_dombak()->first()->stok .'Liter)' }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                        value="{{ old("tanggal") != null ? old("tanggal") : date('Y-m-d') }}" >
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="jam_kerja_id" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="jam_kerja_id" type="text" readonly class="form-control"
                                        value="{{ $stok->jam_kerja()->first()->nama_jam_kerja . ' (' . $stok->jam_kerja()->first()->batas_awal_in . '-' . $stok->jam_kerja()->first()->batas_akhir_in . ')' }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tinggi_dombak" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm)')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tinggi_dombak" type="number" step=".1" readonly
                                        class="form-control thousand-style" name="tinggi_dombak"
                                        value="{{ $stok->konversi()->first()->tinggi }}" >
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="volume_sebelumnya" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Sebelumnya')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_sebelumnya" type="text" readonly
                                        class="form-control thousand-style" name="volume_sebelumnya"
                                        value="{{ $stok->stok_akhir - ($stok->stok_awal + is_null($stok->losses()->first()) ? 0 : ($stok->losses()->first()->simbol == 'kurang' ?  $stok->losses()->first()->jumlah : -1 * $stok->losses()->first()->jumlah)) }}" readonly
                                        autofocus>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="volume" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Aktual')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume" type="text" readonly
                                        class="form-control thousand-style" name="volume"
                                        value="{{ $stok->stok_akhir }}" readonly>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="volume_selisih" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) (Loss/Profit)')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_selisih" type="text" readonly
                                        class="form-control thousand-style" name="volume_selisih"
                                        value="{{ is_null($stok->losses()->first()) ? 0 : $stok->losses()->first()->jumlah }}" readonly>
                                </div>
                            </div>

                            @if(!$stok->is_confirmation)
                            <div class="row mb-3 justify-content-center">
                                <div class="alert alert-warning">Status Belum Di konfirmasi Admin maka bisa diubah dan dihapus</div>
                            </div>
                            @endif

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('stok-taking.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    @if(!$stok->is_confirmation)
                                    <a type="button" href="{{ route('stok-taking.edit', $stok->dokumen_stok_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('stok-taking.delete', $stok->dokumen_stok_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Stok Inventory akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger mr-2"><i class="fas fa-trash"></i> Hapus</button>
                                    </form>
                                    <form action="{{ route('stok-taking.confirmation', $stok->dokumen_stok_id) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Stok Inventory akan dihapus!\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-info"><i class="fas fa-check"></i> Konfirmasi</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
