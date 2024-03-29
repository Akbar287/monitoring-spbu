@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Dokumen Stok</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-taking.index') }}">Stok Taking</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-taking.show', $stok->dokumen_stok_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-taking.file.index', $stok->dokumen_stok_id) }}">File</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-taking.file.show', [$stok->dokumen_stok_id, $file->file_dokumen_stok_id]) }}">Detail File</a></div>
        <div class="breadcrumb-item">Ubah File</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah File Dokumen Stok</h2>
    <p class="section-lead">
        Ubah File Dokumen Stok anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah File Dokumen Stok') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('stok-taking.file.update', [$stok->dokumen_stok_id, $file->file_dokumen_stok_id]) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="gambar" class="col-md-4 col-form-label text-md-end">{{
                                        __('File')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <div class="custom-file">
                                            <input type="file" name="gambar" class="custom-file-input" id="gambar">
                                            <label class="custom-file-label" for="gambar">Pilih File</label>
                                        </div>
            
                                        @error('gambar')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
    
                                        <img src="{{ asset('storage/file_dokumen_stok/'.$file->nama_file) }}" alt="{{ $file->nama_dokumen }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto"> 
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('stok-taking.file.show', [$stok->dokumen_stok_id, $file->file_dokumen_stok_id]) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
