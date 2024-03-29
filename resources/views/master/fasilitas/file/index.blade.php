@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Fasilitas</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('fasilitas.index') }}">Fasilitas</a></div>
        <div class="breadcrumb-item"><a href="{{ route('fasilitas.show', $fasilitas->fasilitas_id) }}">Detail</a></div>
        <div class="breadcrumb-item">File Fasilitas</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File Fasilitas {{$fasilitas->nama_fasilitas}}</h2>
    <p class="section-lead">
        Kelola File Fasilitas di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File Fasilitas ' .  $fasilitas->nama_fasilitas) }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('fasilitas.file.create', $fasilitas->fasilitas_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-file-fasilitas">
                            <thead>
                                <tr>
                                    <th>Nama File</th>
                                    <th>Nama Dokumen</th>
                                    <th>Tanggal Upload</th>
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
