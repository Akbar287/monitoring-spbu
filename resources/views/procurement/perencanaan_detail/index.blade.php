@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Produk</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.index') }}">Rencana Pembelian</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.show', $rencana->rencana_pembelian_id) }}">Detail Rencana Pembelian</a></div>
        <div class="breadcrumb-item"> Detail Produk</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title"> Detail Produk</h2>
    <p class="section-lead">
        Kelola Detail Produk.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Rencana Pembelian') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('perencanaan.detail.create', $rencana->rencana_pembelian_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-detail-rencana">
                            <thead>
                                <tr>
                                    <th>Id Produk</th>
                                    <th>Nama Produk</th>
                                    <th>Jumlah</th>
                                    <th>Satuan Jumlah</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <a href="{{ route('perencanaan.show', $rencana->rencana_pembelian_id) }}" class="btn btn-primary">Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
