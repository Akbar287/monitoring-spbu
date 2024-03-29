@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Harga</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('harga.index') }}">Harga</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Harga</h2>
    <p class="section-lead">
        Detail data Harga anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Harga') }}</h4>
                    <div class="card-header-action">
                        @if($harga->is_default)
                        <div class="badge badge-success">Aktif</div>
                        @else 
                        <div class="badge badge-danger">Tidak Aktif</div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="jam_kerja_id" type="text" readonly
                                        class="form-control" name="jam_kerja_id"
                                        value="{{ $harga->produk()->first()->nama_produk }}" />
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="jam_kerja_id" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="jam_kerja_id" type="text" readonly
                                        class="form-control" name="jam_kerja_id"
                                        value="{{ $harga->jam_kerja()->first()->nama_jam_kerja }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control" name="tanggal"
                                        value="{{ $harga->tanggal }}" />
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="harga_jual" class="col-md-4 col-form-label text-md-end">{{ __('Harga Jual')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Rp
                                            </div>
                                        </div>
                                        <input id="harga_jual" type="text" readonly
                                            class="form-control thousand-style" name="harga_jual"
                                            value="{{ $harga->harga_jual }}" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="harga_beli" class="col-md-4 col-form-label text-md-end">{{ __('Harga Beli')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Rp
                                            </div>
                                        </div>
                                        <input id="harga_beli" type="text" readonly
                                            class="form-control thousand-style" name="harga_beli"
                                            value="{{ $harga->harga_beli }}" />
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('harga.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('harga.edit', $harga->harga_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('harga.destroy', $harga->harga_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Harga akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                    </form>
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
