@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Produk</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('produk.index') }}">Produk</a></div>
        <div class="breadcrumb-item"><a href="{{ route('produk.show', $produk->produk_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Ubah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah Produk</h2>
    <p class="section-lead">
        Ubah data Produk anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah Produk') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('produk.update', $produk->produk_id) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('spbu_id') is-invalid @enderror" name="spbu_id" id="spbu_id">
                                            @foreach($spbu as $s)
                                            <option {{ $produk->spbu_id == $s->spbu_id ? 'selected' : '' }} value="{{ $s->spbu_id }}">{{ $s->nama_spbu . ' ('.$s->nomor_spbu.')' }}</option>
                                            @endforeach
                                        </select>
            
                                        @error('spbu_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="nama_produk" class="col-md-4 col-form-label text-md-end">{{ __('Nama Produk')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="nama_produk" type="text"
                                            class="form-control @error('nama_produk') is-invalid @enderror" name="nama_produk"
                                            value="{{ $produk->nama_produk }}" autocomplete="name"
                                            autofocus>
            
                                        @error('nama_produk')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="nama_jenis" class="col-md-4 col-form-label text-md-end">{{ __('Nama Jenis Produk')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="nama_jenis" type="text"
                                            class="form-control @error('nama_jenis') is-invalid @enderror" name="nama_jenis"
                                            value="{{ $produk->detail_produk()->first()->nama_jenis }}" autocomplete="name"
                                            autofocus>
            
                                        @error('nama_jenis')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="oktan" class="col-md-4 col-form-label text-md-end">{{ __('Oktan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="oktan" type="text"
                                            class="form-control @error('oktan') is-invalid @enderror" name="oktan"
                                            value="{{ $produk->detail_produk()->first()->oktan }}" autocomplete="name"
                                            autofocus>
            
                                        @error('oktan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="is_aktif" class="col-md-4 col-form-label text-md-end">{{ __('Aktifkan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('is_aktif') is-invalid @enderror" name="is_aktif" id="is_aktif">
                                            <option {{ $produk->is_aktif == 'false' ? 'selected' : '' }} value="false">Jangan Aktifkan</option>
                                            <option {{ $produk->is_aktif == 'true' ? 'selected' : '' }} value="true">Aktifkan</option>
                                        </select>
            
                                        @error('is_aktif')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('produk.show', $produk->produk_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
