@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Totalisator</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('totalisator.index') }}">Totalisator</a></div>
        <div class="breadcrumb-item">Riwayat</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Totalisator</h2>
    <p class="section-lead">
        Kelola Totalisator.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Riwayat Totalisator') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('totalisator.riwayat') }}" method="get"> @csrf
                        <div class="row mb-4">
                            <div class="col-12 col-sm-12 col-md-6 col-lg-3 mb-2">
                                <input type="date" name="waktu_mulai" id="waktuMulai" class="form-control" value="{{ $waktuMulai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-3 mb-2">
                                <input type="date" name="waktu_selesai" id="waktuSelesai" class="form-control" value="{{ $waktuSelesai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-3 mb-2">
                                <select name="produk_id" class="custom-select" id="produk_id">
                                    <option value="all" {{ 'all' === $produkSelected ? 'selected' : '' }}>Semua Produk</option>
                                    @foreach($produk as $p)
                                    <option value="{{ $p->produk_id }}" {{ $p->produk_id === $produkSelected ? 'selected' : '' }}>{{ $p->nama_produk }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-3 mb-2">
                                <button type="submit" class="btn btn-success btn-block">Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-totalisator">
                                    <thead>
                                        <tr>
                                            <th>Nozzle</th>
                                            <th>Nama Produk</th>
                                            <th>Tanggal</th>
                                            <th>Jam Kerja</th>
                                            <th>Stand Meter Awal</th>
                                            <th>Stand Meter Akhir</th>
                                            <th>Selisih</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('totalisator.index') }}" class="btn btn-primary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
