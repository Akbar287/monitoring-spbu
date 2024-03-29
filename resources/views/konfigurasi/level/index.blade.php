@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Level</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item">Level</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Level</h2>
    <p class="section-lead">
        Kelola Level atau Jabatan di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Level') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('level.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-level">
                            <thead>
                                <tr>
                                    <th>Nama Level</th>
                                    <th>Nama Divisi</th>
                                    <th>Keterangan</th>
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
