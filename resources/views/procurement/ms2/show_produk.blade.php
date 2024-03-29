@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Pembelian MS2</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.produk.index') }}">MS2</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Detail Pembelian MS2</h2>
    <p class="section-lead">
        Detail data Detail Pembelian MS2 anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Detail Pembelian MS2') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-detail-produk">
                            <thead>
                                <tr>
                                    <th>Tanggal Pembelian</th>
                                    <th>Kode Pembelian</th>
                                    <th>Stok (Liter)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pilihan') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4 d-flex align-items-end">
                            <a type="button" href="{{ route('ms2.produk.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
