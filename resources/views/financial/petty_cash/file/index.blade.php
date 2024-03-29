@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Petty Cash</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.financial') }}">Financial</a></div>
        <div class="breadcrumb-item"><a href="{{ route('financial.petty-cash') }}">Petty Cash</a></div>
        <div class="breadcrumb-item"><a href="{{ route('petty-cash.show', $pettyCash->petty_cash_id) }}">Detail</a></div>
        <div class="breadcrumb-item">File Petty Cash</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File Petty Cash</h2>
    <p class="section-lead">
        Kelola File Petty Cash di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File Petty Cash') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('petty-cash.file.create', $pettyCash->petty_cash_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-petty-cash">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Dokumen</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('petty-cash.show', $pettyCash->petty_cash_id) }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
