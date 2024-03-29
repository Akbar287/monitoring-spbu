@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Produk</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item">Produk</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Produk</h2>
    <p class="section-lead">
        Kelola Produk di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Produk') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('produk.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-produk">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Nama Jenis</th>
                                    <th>Oktan</th>
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
