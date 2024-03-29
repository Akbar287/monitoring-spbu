@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Totalisator</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('totalisator.index') }}">Totalisator</a></div>
        <div class="breadcrumb-item"><a href="{{ route('totalisator.riwayat') }}">Riwayat</a></div>
        <div class="breadcrumb-item"><a href="{{ route('totalisator.riwayat_show', $standMeter->stand_meter_id) }}">Detail</a></div>
        <div class="breadcrumb-item">File Totalisator</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File Totalisator</h2>
    <p class="section-lead">
        Kelola File Totalisator.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File Totalisator Aktif') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('totalisator.file.riwayat.create', $standMeter->stand_meter_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-stok-taking-file">
                            <thead>
                                <tr>
                                    <th>Id File</th>
                                    <th>Nama Dokumen</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('totalisator.riwayat_show', $standMeter->stand_meter_id) }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
