@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Penerimaan BBM</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item">Penerimaan BBM</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Penerimaan BBM</h2>
    <p class="section-lead">
        Pilih Nomor LO yang mau di tambahkan ke stok Dombak
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Penerimaan BBM') }}</h4>
                    <div class="card-header-action">
                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level != 'Operator')
                        <a href="{{ route('penerimaan.verifikasi') }}" class="btn btn-info mr-2">
                            Verifikasi
                        </a>
                        @endif
                        <a href="{{ route('penerimaan.riwayat') }}" class="btn btn-success">
                            Riwayat
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-penerimaan">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No Faktur</th>
                                    <th>No LO</th>
                                    <th>Nama Produk</th>
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
