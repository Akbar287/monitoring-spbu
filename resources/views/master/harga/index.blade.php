@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Harga</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item">Harga</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Harga</h2>
    <p class="section-lead">
        Kelola Harga di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Harga Aktif') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('harga.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                        <a href="{{ route('harga.riwayat.index') }}" class="btn btn-info">
                            Riwayat
                        </a>
                    </div>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
