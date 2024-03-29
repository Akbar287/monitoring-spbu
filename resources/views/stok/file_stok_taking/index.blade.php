@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Stok Taking</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-taking.index') }}">Stok Taking</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-taking.show', $stok->dokumen_stok_id) }}">Detail</a></div>
        <div class="breadcrumb-item">File</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File Stok Taking</h2>
    <p class="section-lead">
        Kelola File Stok Taking.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File Stok Taking') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('stok-taking.file.create', $stok->dokumen_stok_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-stok-taking-file">
                            <thead>
                                <tr>
                                    <th>Id File</th>
                                    <th>Nama Dokumen</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ url()->previous() }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
