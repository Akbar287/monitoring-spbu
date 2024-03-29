@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Riwayat Penerimaan BBM</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('penerimaan.index') }}">Penerimaan BBM</a></div>
        <div class="breadcrumb-item">Riwayat Penerimaan BBM</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Riwayat Penerimaan BBM</h2>
    <p class="section-lead">
        Riwayat Penerimaaan BBM sebelumnya yang sudah dilakukan
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Riwayat Penerimaan BBM') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('penerimaan.index') }}" class="btn btn-info mr-2">
                            Penerimaan
                        </a>
                        <a href="{{ route('penerimaan.verifikasi') }}" class="btn btn-success">
                            Verifikasi
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-penerimaan-riwayat">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Dombak</th>
                                    <th>No Faktur</th>
                                    <th>No LO</th>
                                    <th>Nama Produk</th>
                                    <th>Jumlah</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('penerimaan.index') }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
