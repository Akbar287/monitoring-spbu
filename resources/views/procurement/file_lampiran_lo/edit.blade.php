@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Lampiran LO</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.produk.index') }}">MS2</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman') }}">Pengiriman MS2</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman.show', $pengiriman->pengiriman_id) }}">Detail Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman.file_lo_show', [$pengiriman->pengiriman_id, $file_lo->file_lo_id]) }}">Detail File LO</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman.file_lo_show.lampiran.index', [$pengiriman->pengiriman_id, $file_lo->file_lo_id]) }}">Lampiran LO</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman.file_lo_show.lampiran.show', [$pengiriman->pengiriman_id, $file_lo->file_lo_id, $fileLampiranLo->file_lampiran_lo_id]) }}">Detail Lampiran LO</a></div>
        <div class="breadcrumb-item">Ubah Lampiran LO</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah File Lampiran LO</h2>
    <p class="section-lead">
        Ubah File Lampiran LO anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah File Lampiran LO') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('ms2.pengiriman.file_lo_show.lampiran.update', [$pengiriman->pengiriman_id, $file_lo->file_lo_id, $fileLampiranLo->file_lampiran_lo_id]) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="file" class="col-md-4 col-form-label text-md-end">{{
                                        __('File')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="custom-file">
                                            <input type="file" name="file" class="custom-file-input" id="file">
                                            <label class="custom-file-label" for="file">Pilih File</label>
                                        </div>

                                        @error('file')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror

                                        <img src="{{ asset('storage/file_lampiran_lo/' . $fileLampiranLo->nama_file) }}" alt="{{ $fileLampiranLo->nama_dokumen }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto">
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('ms2.pengiriman.file_lo_show.lampiran.show', [$pengiriman->pengiriman_id, $file_lo->file_lo_id, $fileLampiranLo->file_lampiran_lo_id]) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
