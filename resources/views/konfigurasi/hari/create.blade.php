@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Hari</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('hari.index') }}">Hari</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Hari</h2>
    <p class="section-lead">
        Tambahkan data Hari anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Hari') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('hari.store') }}" method="post" enctype="multipart/form-data">@csrf 
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="nama_hari" class="col-md-4 col-form-label text-md-end">{{ __('Nama Hari')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="nama_hari" type="text"
                                            class="form-control @error('nama_hari') is-invalid @enderror" name="nama_hari"
                                            value="{{ old("nama_hari") }}" autocomplete="name"
                                            autofocus>
            
                                        @error('nama_hari')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="hari_kerja" class="col-md-4 col-form-label text-md-end">{{ __('Hari Kerja')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('hari_kerja') is-invalid @enderror" name="hari_kerja" id="hari_kerja">
                                            <option {{ old('hari_kerja') == true ? 'selected' : '' }} value="true">Hari Kerja</option>
                                            <option {{ old('hari_kerja') == false ? 'selected' : '' }} value="false">Hari Libur</option>
                                        </select>
            
                                        @error('hari_kerja')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <textarea id="keterangan" type="text" rows="3" cols="3"
                                            class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                            value="" autocomplete="name"
                                            autofocus>{{ old("keterangan") }}</textarea>
            
                                        @error('keterangan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('hari.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
