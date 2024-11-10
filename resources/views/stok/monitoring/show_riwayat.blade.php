@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Riwayat Monitoring Stok</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.index') }}">Monitoring Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.show', $stok->stok_inventory_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Riwayat Monitoring Stok</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Riwayat Monitoring Stok</h2>
    <p class="section-lead">
        Riwayat Monitoring Stok.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Riwayat Monitoring Stok') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('monitoring.show_riwayat', $stok->stok_inventory_id) }}" method="get"> @csrf
                        <div class="row mb-4">
                            <div class="col-12 col-sm-12 col-md-6 pb-2">
                                <input type="date" name="waktu_mulai" id="waktuMulai" class="form-control" value="{{ $waktuMulai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 pb-2">
                                <input type="date" name="waktu_selesai" id="waktuSelesai" class="form-control" value="{{ $waktuSelesai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-4 pb-2">
                                <select name="type_dokumen_stok" id="type_dokumen_stok" class="custom-select">
                                    <option value="all">Semua Jenis Dokumen</option>
                                    @foreach($typeDokumenStok as $item)
                                    <option {{ $item->type_dokumen_stok_id == $typeDokumenStokSelected ? 'selected' : '' }} value="{{ $item->type_dokumen_stok_id }}">{{ $item->type_movement }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4 pb-2">
                                <select name="dombak" id="dombak" class="custom-select">
                                    <option value="all">Semua Dombak</option>
                                    @foreach($dombak as $item)
                                    <option {{ $item->dombak_id == $dombakSelected ? 'selected' : '' }} value="{{ $item->dombak_id }}">{{ $item->nama_dombak }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4 pb-2">
                                <button type="submit" class="btn btn-success btn-block">Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover table-riwayat-monitoring">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tipe Dok. Stok</th>
                                    <th>Dombak</th>
                                    <th>Stok Awal</th>
                                    <th>Stok Akhir</th>
                                    <th>Total Loss</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('monitoring.show', $stok->stok_inventory_id) }}" class="btn btn-primary"><i class="fas fa-arow-left"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
