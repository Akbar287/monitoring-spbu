@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Penerimaan BBM</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('penerimaan.index') }}">Penerimaan BBM</a></div>
        <div class="breadcrumb-item"><a href="{{ route('penerimaan.riwayat') }}">Riwayat Penerimaan BBM</a></div>
        <div class="breadcrumb-item"><a href="{{ route('penerimaan.riwayat.show', $penerimaan->penerimaan_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('penerimaan.file.index', $penerimaan->penerimaan_id) }}">File</a></div>
        <div class="breadcrumb-item">Detail File</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail File Penerimaan BBM</h2>
    <p class="section-lead">
        Detail data File Penerimaan BBM anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail File Penerimaan BBM') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="file_penerimaan_id" class="col-md-4 col-form-label text-md-end">{{ __('Id File Penerimaan BBM')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="file_penerimaan_id" type="text" readonly
                                        class="form-control" name="file_penerimaan_id"
                                        value="{{ $file->file_penerimaan_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_file" class="col-md-4 col-form-label text-md-end">{{ __('Nama File')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_file" type="text" readonly
                                        class="form-control" name="nama_file"
                                        value="{{ $file->nama_file }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_dokumen" class="col-md-4 col-form-label text-md-end">{{ __('Nama Dokumen')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_dokumen" type="text" readonly
                                        class="form-control" name="nama_dokumen"
                                        value="{{ $file->nama_dokumen }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal_upload" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Upload')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal_upload" type="date" readonly
                                        class="form-control" name="tanggal_upload"
                                        value="{{ $file->tanggal_upload }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="file_preview" class="col-md-4 col-form-label text-md-end">{{ __('File Preview')
                                    }}</label>

                                <div class="col-md-6">
                                    <img src="{{ asset('storage/file_penerimaan/'.$file->nama_file) }}" alt="{{ $file->nama_dokumen }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto">
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('penerimaan.file.riwayat.index', $penerimaan->penerimaan_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" target="_blank" href="{{ route('penerimaan.file.download', [$penerimaan->penerimaan_id, $file->file_penerimaan_id]) }}" class="btn btn-info mr-2"><i class="fas fa-download"></i> Unduh</a>
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
