@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Riwayat</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('harga.index') }}">Harga</a></div>
        <div class="breadcrumb-item">Riwayat</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Riwayat Harga</h2>
    <p class="section-lead">
        Riwayat Harga.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Harga Non-Aktif') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-harga">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Tanggal</th>
                                    <th>Jam Kerja</th>
                                    <th>Harga Jual</th>
                                    <th>Harga Beli</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('harga.index') }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
