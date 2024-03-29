@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Open Penjualan</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open') }}">Open Penjualan</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open.show', $penjualan->penjualan_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Detail Open Penjualan</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Open Penjualan</h2>
    <p class="section-lead">
        Kelola Detail Open Penjualan di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Detail Open Penjualan') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('setoran.open.detail.create', $penjualan->penjualan_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-open-detail-setoran">
                            <thead>
                                <tr>
                                    <th>Nama Nozzle</th>
                                    <th>Stand Meter Awal</th>
                                    <th>Stand Meter Akhir</th>
                                    <th>Harga</th>
                                    <th>Liter</th>
                                    <th>Total Detail Jual</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('setoran.open.show', $penjualan->penjualan_id) }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
