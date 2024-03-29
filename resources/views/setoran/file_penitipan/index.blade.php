@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Penarikan</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.penitipan') }}">Penitipan Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.penitipan.show', $penjualan->penjualan_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.penitipan.index_penitipan', $penjualan->penjualan_id) }}">List</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.penitipan.show_penitipan', [$penjualan->penjualan_id, $penitipan->penarikan_id]) }}">Detail List</a></div>
        <div class="breadcrumb-item">File Penarikan</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File Penarikan</h2>
    <p class="section-lead">
        Kelola File Penarikan.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File Penarikan') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('setoran.penitipan.file.create', [$penjualan->penjualan_id, $penitipan->penarikan_id]) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="table-responsive">
                        <table class="table table-hover table-file-penarikan">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Gambar</th>
                                    <th>Nama File</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('setoran.penitipan.show_penitipan', [$penjualan->penjualan_id, $penitipan->penarikan_id]) }}" type="button" title="Kembali" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
