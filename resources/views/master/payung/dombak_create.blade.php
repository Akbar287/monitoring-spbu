@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Payung</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('payung.index') }}">Payung</a></div>
        <div class="breadcrumb-item"><a href="{{ route('payung.show', [$payung->payung_id]) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('payung.dombak', [$payung->payung_id]) }}">Dombak</a></div>
        <div class="breadcrumb-item">Pilih Dombak</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Pilih Dombak</h2>
    <p class="section-lead">
        Pilih Dombak yang ingin di supply ke Payung {{ $payung->nama_payung }}.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Supply Payung ' . $payung->nama_payung) }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-payung-dombak-create">
                                <thead>
                                    <tr>
                                        <th>Id Dombak</th>
                                        <th>Nama Dombak</th>
                                        <th>Aktif</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <a href="{{ route('payung.dombak', $payung->payung_id) }}" class="btn btn-primary">Kembali</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
