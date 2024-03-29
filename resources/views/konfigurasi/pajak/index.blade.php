@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Pajak</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item">Pajak</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Pajak</h2>
    <p class="section-lead">
        Konfigurasi Pajak Pembelian.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Konfigurasi Pajak') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('pajak_pembelian_lib.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-pajak-lib">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>PPN</th>
                                    <th>PPH</th>
                                    <th>PPBKB</th>
                                    <th>Aktif</th>
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
