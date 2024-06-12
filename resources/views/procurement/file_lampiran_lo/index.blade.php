@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Lampiran LO</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.index') }}">Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.show', $pengiriman->pengiriman_id) }}">Detail Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.file_lo.show', [$pengiriman->pengiriman_id, $file_lo->file_lo_id]) }}">Detail File LO</a></div>
        <div class="breadcrumb-item">File Lampiran LO</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File Lampiran LO</h2>
    <p class="section-lead">
        Kelola File Lampiran LO di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File Lampiran LO') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('pengiriman.file_lo_show.lampiran.create', [$pengiriman->pengiriman_id, $file_lo->file_lo_id]) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="table-responsive">
                        <table class="table table-hover table-file-lampiran-lo">
                            <thead>
                                <tr>
                                    <th>Nama File</th>
                                    <th>Nama Dokumen</th>
                                    <th>Tanggal Upload</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('pengiriman.file_lo.show', [$pengiriman->pengiriman_id, $file_lo->file_lo_id]) }}" class="btn btn-primary"><i class="fas fa fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
