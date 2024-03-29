@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Penerimaan</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('penerimaan.index') }}">Penerimaan BBM</a></div>
        <div class="breadcrumb-item"><a href="{{ route('penerimaan.riwayat') }}">Riwayat Penerimaan BBM</a></div>
        <div class="breadcrumb-item"><a href="{{ route('penerimaan.riwayat.show', $penerimaan->penerimaan_id) }}">Detail</a></div>
        <div class="breadcrumb-item">File</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File Penerimaan</h2>
    <p class="section-lead">
        Kelola File Penerimaan.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File Penerimaan') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-penerimaan">
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
                    <a href="{{ route('penerimaan.riwayat.show', $penerimaan->penerimaan_id) }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
