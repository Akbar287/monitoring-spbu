@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Divisi</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item">Divisi</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Divisi</h2>
    <p class="section-lead">
        Kelola Divisi atau Jabatan di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Divisi') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('divisi.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-divisi">
                            <thead>
                                <tr>
                                    <th>Nama DIvisi</th>
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
