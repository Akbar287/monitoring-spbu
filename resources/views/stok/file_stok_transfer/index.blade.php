@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Stok Transfer</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-transfer.index') }}">Stok Transfer</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-transfer.show', $dombakTransfer->dombak_transfer_id) }}">Detail</a></div>
        <div class="breadcrumb-item">File</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File Stok Transfer</h2>
    <p class="section-lead">
        Kelola File Stok Transfer.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File Stok Transfer') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('stok-transfer.file.create', [$dombakTransfer->dombak_transfer_id, $dokumenStok->dokumen_stok_id]) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-stok-transfer-file">
                            <thead>
                                <tr>
                                    <th>Nama Dokumen</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('stok-transfer.show', $dombakTransfer->dombak_transfer_id) }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
