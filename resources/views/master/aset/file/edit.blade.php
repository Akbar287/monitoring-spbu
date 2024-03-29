@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Aset</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.index') }}">aset</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.show', $aset->aset_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.file.index', $aset->aset_id) }}">File</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.file.show', [$aset->aset_id, $fileAset->file_aset_id]) }}">Detail File</a></div>
        <div class="breadcrumb-item">Ubah File</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah File Aset</h2>
    <p class="section-lead">
        Ubah File Aset anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah File Aset') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('aset.file.update', [$aset->aset_id, $fileAset->file_aset_id]) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
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
    
                                        <img alt="gambar" src="{{ asset('storage/aset/'.$fileAset->nama_file) }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto"> 
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal_upload" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Digunakan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="tanggal_upload" type="date"
                                            class="form-control @error('tanggal_upload') is-invalid @enderror" name="tanggal_upload"
                                            value="{{ $fileAset->tanggal_upload }}" autocomplete="name"
                                            autofocus>
            
                                        @error('tanggal_upload')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('fasilitas.file.show', [$aset->aset_id, $fileAset->file_aset_id]) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
