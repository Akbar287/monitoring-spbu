@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Payung</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('payung.index') }}">Payung</a></div>
        <div class="breadcrumb-item"><a href="{{ route('payung.show', [$payung->payung_id]) }}">Detail</a></div>
        <div class="breadcrumb-item">Dombak</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Dombak</h2>
    <p class="section-lead">
        Ini Adalah DOmbak yang menyuplai ke Payung {{ $payung->nama_payung }}.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Supply Payung') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('payung.dombak.create', $payung->payung_id) }}" class="btn btn-primary">
                            Tambah Dombak
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-payung-dombak">
                                <thead>
                                    <tr>
                                        <th>Nama Payung</th>
                                        <th>Nama Dombak</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-12"><a href="{{ route('payung.show', $payung->payung_id) }}" class="btn btn-primary">Kembali</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
