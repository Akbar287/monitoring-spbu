@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Jam Kerja</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item">Jam Kerja</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Jam Kerja</h2>
    <p class="section-lead">
        Kelola Jam Kerja yang ingin anda monitoring.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Jam Kerja') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('jam_kerja.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="table-responsive">
                        <table class="table table-hover table-jam-kerja">
                            <thead>
                                <tr>
                                    <th>Nama Jam Kerja</th>
                                    <th>Batas Awal</th>
                                    <th>Batas Akhir</th>
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
