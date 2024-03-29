@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Harga</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('harga.index') }}">Harga</a></div>
        <div class="breadcrumb-item"><a href="{{ route('harga.show', $harga->harga_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Ubah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah Harga</h2>
    <p class="section-lead">
        Ubah data Harga anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah Harga') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('harga.update', $harga->harga_id) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('produk_id') is-invalid @enderror" name="produk_id" id="produk_id">
                                            @foreach($produk as $s)
                                            <option {{ $harga->produk()->first()->produk_id == $s->produk_id ? 'selected' : '' }} value="{{ $s->produk_id }}">{{ $s->nama_produk }}</option>
                                            @endforeach
                                        </select>
            
                                        @error('produk_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="jam_kerja_id" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('jam_kerja_id') is-invalid @enderror" name="jam_kerja_id" id="jam_kerja_id">
                                            @foreach($jamKerja as $s)
                                            <option {{ $harga->jam_kerja()->first()->jam_kerja_id == $s->jam_kerja_id ? 'selected' : '' }} value="{{ $s->jam_kerja_id }}">{{ $s->nama_jam_kerja }}</option>
                                            @endforeach
                                        </select>
            
                                        @error('jam_kerja_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="tanggal" type="date"
                                            class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                            value="{{ $harga->tanggal }}" autocomplete="name"
                                            autofocus>
            
                                        @error('tanggal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
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
                                            <input id="harga_jual" type="text"
                                                class="form-control thousand-style @error('harga_jual') is-invalid @enderror" name="harga_jual"
                                                value="{{ $harga->harga_jual }}" autocomplete="name"
                                                autofocus>
                                        </div>
            
                                        @error('harga_jual')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
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
                                            <input id="harga_beli" type="text"
                                                class="form-control thousand-style @error('harga_beli') is-invalid @enderror" name="harga_beli"
                                                value="{{ $harga->harga_beli }}" autocomplete="name"
                                                autofocus>
                                        </div>
            
                                        @error('harga_beli')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('harga.show', $harga->harga_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button type="submit" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
