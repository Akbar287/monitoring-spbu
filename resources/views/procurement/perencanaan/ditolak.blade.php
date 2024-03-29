@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Riwayat Rencana Pembelian</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.index') }}">Rencana Pembelian</a></div>
        <div class="breadcrumb-item">Riwayat Rencana Pembelian</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Riwayat Rencana Pembelian</h2>
    <p class="section-lead">
        Kelola Riwayat Rencana Pembelian.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Riwayat Rencana Pembelian') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="table-responsive">
                        <table class="table table-hover table-perencanaan">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Tanggal</th>
                                    <th>Diajukan Oleh</th>
                                    <th>Total Produk</th>
                                    <th>Status</th>
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
