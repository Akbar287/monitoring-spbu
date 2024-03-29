@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Monitoring Stok</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item">Monitoring Stok</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Monitoring Stok</h2>
    <p class="section-lead">
        Kelola Monitoring Stok.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Monitoring Stok') }}</h4>
                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                    <div class="card-header-action">
                        <a href="{{ route('monitoring.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-monitoring">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Stok</th>
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
