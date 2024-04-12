@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Setoran Ke Bank</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item">Setoran Ke Bank</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Setoran Ke Bank</h2>
    <p class="section-lead">
        Kelola Setoran Ke Bank.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Setoran Ke Bank') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('setoran.setoran_bank.index_setoran_bank_penjualan', $penjualan->penjualan_id) }}" method="get"> @csrf
                        <div class="row mb-4">
                            <div class="col-12 col-sm-12 col-md-6 col-lg-3">
                                <select name="status_setoran_id" id="status_setoran_id" class="custom-select">
                                    <option value="all">Semua Status</option>
                                    @foreach($statusSetoran as $ss)
                                    <option value="{{ $ss->status_setoran_id }}" {{ $ss->status_setoran_id == $statusSetoranSelected ? 'selected' : '' }} >{{ $ss->nama_status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-3">
                                <input type="date" name="waktu_mulai" id="waktuMulai" class="form-control" value="{{ $waktuMulai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-3">
                                <input type="date" name="waktu_selesai" id="waktuSelesai" class="form-control" value="{{ $waktuSelesai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-3">
                                <button type="submit" class="btn btn-success btn-block">Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-setoran-bank">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th>Jam Kerja</th>
                                            <th>Stand Meter</th>
                                            <th>Total Uang Penjualan</th>
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
