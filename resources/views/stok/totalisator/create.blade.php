@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Tambah Totalisator</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('totalisator.index') }}">Totalisator</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Totalisator</h2>
    <p class="section-lead">
        Tambahkan data Totalisator anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Totalisator') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('totalisator.store') }}" method="post">@csrf
                        <div class="row">
                            <div class="col-md-12 information">
                            </div>
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="dombak_id" class="col-md-4 col-form-label text-md-end">{{ __('Dombak')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('dombak_id') is-invalid @enderror" name="dombak_id" id="dombak_id">
                                            @foreach($dombak as $s)
                                            <option {{ old('dombak_id') == $s->dombak_id ? 'selected' : '' }} value="{{ $s->dombak_id }}">{{ $s->nama_dombak . ' ('. $s->stok_inventory_dombak()->first()->stok_inventory()->first()->produk()->first()->nama_produk . ': ' . number_format($s->stok_inventory_dombak()->first()->stok,6, ".", ",") .'Liter)' }}</option>
                                            @endforeach
                                        </select>

                                        @error('dombak_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="nozzle_id" class="col-md-4 col-form-label text-md-end">{{ __('Nozzle')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('nozzle_id') is-invalid @enderror" name="nozzle_id" id="nozzle_id">
                                            @foreach($nozzle as $s)
                                            <option {{ old('nozzle_id') == $s->nozzle_id ? 'selected' : '' }} value="{{ $s->nozzle_id }}">{{ $s->nama_nozzle . '-'. $s->nama_produk }}</option>
                                            @endforeach
                                        </select>

                                        @error('nozzle_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tanggal" type="date" class="form-control @error('tanggal') is-invalid @enderror" name="tanggal" readonly value="{{ old("tanggal") != null ? old("tanggal") : date('Y-m-d') }}" />
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="jam_kerja_id" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                        }}</label>

                                    <div class="col-md-6 jam_kerja_dynamic">
                                        <input id="jam_kerja_id" type="text" class="form-control @error('jam_kerja_id') is-invalid @enderror" name="jam_kerja_id" readonly value="" />
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="stand_meter_awal" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Awal')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="stand_meter_awal" type="text" class="form-control thousand-style @error('stand_meter_awal') is-invalid @enderror" name="stand_meter_awal" value="{{ old("stand_meter_awal") }}" readonly />
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('totalisator.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button disabled type="submit" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
