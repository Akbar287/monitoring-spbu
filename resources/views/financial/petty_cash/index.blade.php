@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Petty Cash</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.financial') }}">Financial</a></div>
        <div class="breadcrumb-item">Petty Cash</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Petty Cash</h2>
    <p class="section-lead">
        Kelola Petty Cash di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Petty Cash') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('petty-cash.create') }}" class="btn btn-primary">
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
                                    <th>No. Kode</th>
                                    <th>Jenis</th>
                                    <th>Jumlah</th>
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
