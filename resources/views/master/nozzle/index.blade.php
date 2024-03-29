@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Nozzle</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item">Nozzle</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Nozzle</h2>
    <p class="section-lead">
        Kelola Nozzle di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Nozzle') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('nozzle.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nozzle">
                            <thead>
                                <tr>
                                    <th>Nama Payung</th>
                                    <th>Nama Dispenser</th>
                                    <th>Nama Produk</th>
                                    <th>Nama Nozzle</th>
                                    <th>Aktif</th>
                                    <th>Keterangan</th>
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
