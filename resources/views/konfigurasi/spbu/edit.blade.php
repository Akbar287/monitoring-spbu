@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>SPBU</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('spbu.index') }}">SPBU</a></div>
        <div class="breadcrumb-item"><a href="{{ route('spbu.show', $spbu->spbu_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Ubah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah SPBU</h2>
    <p class="section-lead">
        Ubah data SPBU anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah SPBU') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('spbu.update', $spbu->spbu_id) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="nama_spbu" class="col-md-4 col-form-label text-md-end">{{ __('Nama SPBU')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="nama_spbu" type="text"
                                            class="form-control @error('nama_spbu') is-invalid @enderror" name="nama_spbu"
                                            value="{{ $spbu->nama_spbu }}" autocomplete="name"
                                            autofocus>
            
                                        @error('nama_spbu')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="nomor_spbu" class="col-md-4 col-form-label text-md-end">{{ __('Nomor SPBU')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="nomor_spbu" type="text"
                                            class="form-control @error('nomor_spbu') is-invalid @enderror" name="nomor_spbu"
                                            value="{{ $spbu->nomor_spbu }}" autocomplete="name"
                                            autofocus>
            
                                        @error('nomor_spbu')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal_pendirian" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Berdiri')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="tanggal_pendirian" type="date"
                                            class="form-control @error('tanggal_pendirian') is-invalid @enderror" name="tanggal_pendirian"
                                            value="{{ $spbu->tanggal_pendirian }}" autocomplete="name"
                                            autofocus>
            
                                        @error('tanggal_pendirian')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="luas_lahan" class="col-md-4 col-form-label text-md-end">{{ __('Luas Lahan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="luas_lahan" type="text"
                                            class="form-control @error('luas_lahan') is-invalid @enderror" name="luas_lahan"
                                            value="{{ $spbu->luas_lahan }}" autocomplete="name"
                                            autofocus>
            
                                        @error('luas_lahan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="satuan_luas" class="col-md-4 col-form-label text-md-end">{{ __('Satuan Lahan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="satuan_luas" type="text" placeholder="Ha/Km2/m2"
                                            class="form-control @error('satuan_luas') is-invalid @enderror" name="satuan_luas"
                                            value="{{ $spbu->satuan_luas }}" autocomplete="name"
                                            autofocus>
            
                                        @error('satuan_luas')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="alamat" class="col-md-4 col-form-label text-md-end">{{ __('Alamat')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <textarea id="alamat" type="text" rows="3" cols="3"
                                            class="form-control @error('alamat') is-invalid @enderror" name="alamat"
                                            value="" autocomplete="name"
                                            autofocus>{{ $spbu->alamat }}</textarea>
            
                                        @error('alamat')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('spbu.show', $spbu->spbu_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
