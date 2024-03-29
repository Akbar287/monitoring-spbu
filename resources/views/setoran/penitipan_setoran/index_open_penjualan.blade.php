@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Penitipan Setoran</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item">Penitipan Setoran</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Penitipan Setoran</h2>
    <p class="section-lead">
        Kelola Penitipan Setoran.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Penitipan Setoran') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('setoran.open.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('setoran.open') }}" method="get"> @csrf
                        <div class="row mb-4">
                            <div class="col-12 col-sm-12 col-md-5">
                                <input type="date" name="waktu_mulai" id="waktuMulai" class="form-control" value="{{ $waktuMulai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-5">
                                <input type="date" name="waktu_selesai" id="waktuSelesai" class="form-control" value="{{ $waktuSelesai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-2">
                                <button type="submit" class="btn btn-success btn-block">Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-penitipan-setoran">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Dispenser</th>
                                            <th>Tanggal</th>
                                            <th>Jam Kerja</th>
                                            <th>Total Penjualan</th>
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
    </div>
</div>
@endsection
