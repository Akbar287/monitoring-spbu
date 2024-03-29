@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Dokumen Stok</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-transfer.index') }}">Stok Transfer</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-transfer.show', $dombakTransfer->dombak_transfer_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-transfer.file.index', [$dombakTransfer->dombak_transfer_id, $dokumenStok->dokumen_stok_id]) }}">File</a></div>
        <div class="breadcrumb-item">Detail File</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail File Dokumen Stok Transfer</h2>
    <p class="section-lead">
        Detail data File Dokumen Stok anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail File Dokumen Stok Transfer') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="file_dokumen_stok_id" class="col-md-4 col-form-label text-md-end">{{ __('Id File Dokumen Stok')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="file_dokumen_stok_id" type="text" readonly
                                        class="form-control" name="file_dokumen_stok_id"
                                        value="{{ $fileDokumenStok->file_dokumen_stok_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_file" class="col-md-4 col-form-label text-md-end">{{ __('Nama File')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_file" type="text" readonly
                                        class="form-control" name="nama_file"
                                        value="{{ $fileDokumenStok->nama_file }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_dokumen" class="col-md-4 col-form-label text-md-end">{{ __('Nama Dokumen')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_dokumen" type="text" readonly
                                        class="form-control" name="nama_dokumen"
                                        value="{{ $fileDokumenStok->nama_dokumen }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal_upload" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Upload')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal_upload" type="date" readonly
                                        class="form-control" name="tanggal_upload"
                                        value="{{ $fileDokumenStok->tanggal_upload }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="file_preview" class="col-md-4 col-form-label text-md-end">{{ __('File Preview')
                                    }}</label>

                                <div class="col-md-6">
                                    <img src="{{ asset('storage/file_dokumen_stok/'.$fileDokumenStok->nama_file) }}" alt="{{ $fileDokumenStok->nama_dokumen }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto">
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('stok-transfer.file.index', [$dombakTransfer->dombak_transfer_id, $dokumenStok->dokumen_stok_id]) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" target="_blank" href="{{ route('stok-transfer.file.download', [$dombakTransfer->dombak_transfer_id, $dokumenStok->dokumen_stok_id, $fileDokumenStok->file_dokumen_stok_id]) }}" class="btn btn-info mr-2"><i class="fas fa-download"></i> Unduh</a>
                                    <a type="button" href="{{ route('stok-transfer.file.edit', [$dombakTransfer->dombak_transfer_id, $dokumenStok->dokumen_stok_id, $fileDokumenStok->file_dokumen_stok_id]) }}" class="btn mr-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('stok-transfer.file.delete', [$dombakTransfer->dombak_transfer_id, $dokumenStok->dokumen_stok_id, $fileDokumenStok->file_dokumen_stok_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('File Stok Transfer akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
