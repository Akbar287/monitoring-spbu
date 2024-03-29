@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File LO</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.rencana.index') }}">Rencana Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.rencana.show', $rencana->rencana_pembelian_id) }}">Detail Rencana</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.index', $rencana->rencana_pembelian_id) }}">Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.show', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id]) }}">Detail Pengiriman</a></div>
        <div class="breadcrumb-item">File LO</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File LO</h2>
    <p class="section-lead">
        Kelola File LO.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File LO') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('file_lo.create', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id]) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-file-lo">
                            <thead>
                                <tr>
                                    <th>No. Lo</th>
                                    <th>Nama Produk</th>
                                    <th>Jumlah</th>
                                    <th>No. Faktur</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('pengiriman.show', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id]) }}" type="button" title="Kembali" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
