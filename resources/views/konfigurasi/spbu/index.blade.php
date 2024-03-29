@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>SPBU</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item">SPBU</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">SPBU</h2>
    <p class="section-lead">
        Kelola SPBU yang ingin anda monitoring.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List SPBU') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('spbu.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="table-responsive">
                        <table class="table table-hover table-spbu">
                            <thead>
                                <tr>
                                    <th>Nama SPBU</th>
                                    <th>Nomor SPBU</th>
                                    <th>Tanggal Berdiri</th>
                                    <th>Alamat</th>
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
