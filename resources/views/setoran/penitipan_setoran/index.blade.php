@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>List Penitipan Setoran</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.penitipan') }}">Penitipan Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.penitipan.show', $penjualan->penjualan_id) }}">Detail</a></div>
        <div class="breadcrumb-item">List Penitipan Setoran</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">List Penitipan Setoran</h2>
    <p class="section-lead">
        Kelola List Penitipan Setoran.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Penitipan Setoran') }}</h4>
                    <div class="card-header-action">
                        <div class="badge badge-success">
                            {{ 'Rp. ' . number_format($penjualan->total_penjualan + $penjualan->total_uang_kotak, 0, ".", ",") }}
                        </div>
                        <a href="{{ route('setoran.penitipan.create', $penjualan->penjualan_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-penitipan-setoran-list">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama Bank</th>
                                            <th>No. Ref</th>
                                            <th>Metode TF</th>
                                            <th>Total Uang</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <a href="{{ route('setoran.penitipan.show', $penjualan->penjualan_id) }}" class="btn btn-primary">Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
