@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Stok Transfer</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item">Stok Transfer</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Stok Transfer</h2>
    <p class="section-lead">
        Stok Transfer dapat dilakukan bila terdapat produk di dombak yang berbeda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Stok Transfer Aktif') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('stok-transfer.riwayat') }}" class="btn btn-info">
                            Riwayat
                        </a>
                        <a href="{{ route('stok-transfer.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('stok-transfer.index') }}" method="get"> @csrf
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
                                <table class="table table-hover table-stok-transfer">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Jam Kerja</th>
                                            <th>Dombak Sebelum</th>
                                            <th>Dombak Setelah</th>
                                            <th>Nama Produk</th>
                                            <th>Stok Berpindah</th>
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
