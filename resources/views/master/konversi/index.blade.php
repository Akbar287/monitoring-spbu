@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Konversi</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('dombak.index') }}">Dombak</a></div>
        <div class="breadcrumb-item"><a href="{{ route('dombak.show', $dombak->dombak_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Konversi</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Konversi</h2>
    <p class="section-lead">
        Kelola Konversi di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Konversi untuk Dombak ' . $dombak->nama_dombak) }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('konversi.create', $dombak->dombak_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                        <a href="{{ route('konversi.upload_show', $dombak->dombak_id) }}" class="btn btn-info">
                            Upload
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-konversi">
                            <thead>
                                <tr>
                                    <th>Tinggi</th>
                                    <th>Volume</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <a href="{{ route('dombak.show', $dombak->dombak_id) }}" class="btn mr-2 btn-primary">Kembali</a>
                        <form action="{{ route('konversi.destroy.all', $dombak->dombak_id) }}" method="POST">@csrf @method('delete')
                            <button type="submit" onclick="return confirm('Semua Data Konversi akan dihapus!\n Lanjutkan?')" title="Hapus Semua Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus Semua Data</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
