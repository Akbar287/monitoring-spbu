@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Hari</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item">Hari</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Hari</h2>
    <p class="section-lead">
        Kelola Hari.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Hari') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('hari.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-hari">
                            <thead>
                                <tr>
                                    <th>Nama Hari</th>
                                    <th>Hari Kerja</th>
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
