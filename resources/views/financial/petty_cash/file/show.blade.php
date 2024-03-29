@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Petty Cash</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.financial') }}">Financial</a></div>
        <div class="breadcrumb-item"><a href="{{ route('financial.petty-cash') }}">Petty Cash</a></div>
        <div class="breadcrumb-item"><a href="{{ route('petty-cash.show', $pettyCash->petty_cash_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('financial.petty-cash.file', $pettyCash->petty_cash_id) }}">File</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Petty Cash</h2>
    <p class="section-lead">
        Detail data Petty Cash anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Petty Cash') }}</h4>
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
                                        value="{{ $file->file_petty_cash_id }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_file" class="col-md-4 col-form-label text-md-end">{{ __('Nama File')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="nama_file" type="text" readonly
                                        class="form-control @error('nama_file') is-invalid @enderror" name="nama_file"
                                        value="{{ $file->nama_file }}" >
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="nama_dokumen" class="col-md-4 col-form-label text-md-end">{{ __('Nama Dokumen')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="nama_dokumen" type="text" readonly
                                        class="form-control @error('nama_dokumen') is-invalid @enderror" name="nama_dokumen"
                                        value="{{ $file->nama_dokumen }}" >
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal_upload" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Upload')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="tanggal_upload" type="date" readonly
                                        class="form-control @error('tanggal_upload') is-invalid @enderror" name="tanggal_upload"
                                        value="{{ $file->tanggal_upload }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="gambar" class="col-md-4 col-form-label text-md-end">{{
                                    __('File')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <img src="{{ asset('storage/file_petty_cash/' . $file->nama_file) }}" alt="{{ $file->nama_dokumen }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto"> 
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('financial.petty-cash.file', $pettyCash->petty_cash_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('petty-cash.file.edit', [$pettyCash->petty_cash_id, $file->file_petty_cash_id]) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <a type="button" target="_blank" href="{{ route('petty-cash.file.download', [$pettyCash->petty_cash_id, $file->file_petty_cash_id]) }}" class="btn mr-2 btn-info"><i class="fas fa-pen"></i> Unduh File</a>
                                    <form action="{{ route('petty-cash.file.delete', [$pettyCash->petty_cash_id, $file->file_petty_cash_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Petty Cash akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
