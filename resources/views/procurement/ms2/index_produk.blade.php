@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>MS2</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item">MS2</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">MS2</h2>
    <p class="section-lead">
        Kelola MS2.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List MS2') }}</h4>
                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                    <div class="card-header-action">
                        <a href="{{ route('ms2.pengiriman') }}" class="btn btn-primary">
                            Atur Pengiriman
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-ms2">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Stok (Liter)</th>
                                            <th>Total pembelian</th>
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
    </div>
</div>
@endsection
