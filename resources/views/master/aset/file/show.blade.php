@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Aset</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.index') }}">Aset</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.show', $aset->aset_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.file.index', $aset->aset_id) }}">File</a></div>
        <div class="breadcrumb-item">Detail File</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail File Aset</h2>
    <p class="section-lead">
        Detail data File Aset anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail File Aset') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="file_aset_id" class="col-md-4 col-form-label text-md-end">{{ __('Id File Aset')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="file_aset_id" type="text" readonly
                                        class="form-control @error('file_aset_id') is-invalid @enderror" name="file_aset_id"
                                        value="{{ $fileAset->file_aset_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_file" class="col-md-4 col-form-label text-md-end">{{ __('Nama File')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_file" type="text" readonly
                                        class="form-control @error('nama_file') is-invalid @enderror" name="nama_file"
                                        value="{{ $fileAset->nama_file }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_dokumen" class="col-md-4 col-form-label text-md-end">{{ __('Nama Dokumen')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_dokumen" type="text" readonly
                                        class="form-control @error('nama_dokumen') is-invalid @enderror" name="nama_dokumen"
                                        value="{{ $fileAset->nama_dokumen }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal_upload" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Upload')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal_upload" type="date" readonly
                                        class="form-control @error('tanggal_upload') is-invalid @enderror" name="tanggal_upload"
                                        value="{{ $fileAset->tanggal_upload }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('aset.file.index', $aset->aset_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" target="_blank" href="{{ route('aset.file.download', [$aset->aset_id, $fileAset->file_aset_id]) }}" class="btn btn-info mr-2"><i class="fas fa-download"></i> Unduh</a>
                                    <a type="button" href="{{ route('aset.file.edit', [$aset->aset_id, $fileAset->file_aset_id]) }}" class="btn mr-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('aset.file.delete', [$aset->aset_id, $fileAset->file_aset_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('File Aset akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
