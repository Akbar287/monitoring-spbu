@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Riwayat Monitoring Stok</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.index') }}">Monitoring Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.show', $stok->stok_inventory_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Riwayat Monitoring Stok</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Riwayat Monitoring Stok</h2>
    <p class="section-lead">
        Riwayat Monitoring Stok.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Riwayat Monitoring Stok') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-riwayat-monitoring">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tipe Dok. Stok</th>
                                    <th>Dombak</th>
                                    <th>Stok Awal</th>
                                    <th>Stok Akhir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('monitoring.show', $stok->stok_inventory_id) }}" class="btn btn-primary"><i class="fas fa-arow-left"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
