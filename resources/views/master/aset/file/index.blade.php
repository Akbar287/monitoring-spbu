@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Aset</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.index') }}">Aset</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.show', $aset->aset_id) }}">Detail</a></div>
        <div class="breadcrumb-item">File Aset</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File Aset {{$aset->nama_aset}}</h2>
    <p class="section-lead">
        Kelola File Aset di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File Aset ' .  $aset->nama_aset) }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('aset.file.create', $aset->aset_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-file-aset">
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
