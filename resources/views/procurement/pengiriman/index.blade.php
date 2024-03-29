@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Pengiriman</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item">Pengiriman</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Pengiriman</h2>
    <p class="section-lead">
        Kelola Pengiriman.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Pengiriman') }}</h4>
                    <div class="card-header-action">
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('pengiriman.index') }}" method="get"> @csrf
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
                    <div class="table-responsive">
                        <table class="table table-hover table-pengiriman">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No Pol</th>
                                    <th>No Do</th>
                                    <th>Selesai</th>
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
