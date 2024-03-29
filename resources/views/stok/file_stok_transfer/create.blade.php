@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Stok Transfer</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-transfer.index') }}">Stok Transfer</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-transfer.show', $dombakTransfer->dombak_transfer_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-transfer.file.index', [$dombakTransfer->dombak_transfer_id, $dokumenStok->dokumen_stok_id]) }}">File</a></div>
        <div class="breadcrumb-item">Tambah File</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah File Stok Transfer</h2>
    <p class="section-lead">
        Tambahkan data File Stok Transfer anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah File Stok Transfer') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('stok-transfer.file.store', [$dombakTransfer->dombak_transfer_id, $dokumenStok->dokumen_stok_id]) }}" method="post" enctype="multipart/form-data">@csrf
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

                                        <img src="" alt="{{ old("gambar") }}" class="img img-thumbnail img-temporary d-none" style="width: 800px; height: auto">
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('stok-transfer.file.index', [$dombakTransfer->dombak_transfer_id, $dokumenStok->dokumen_stok_id]) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
