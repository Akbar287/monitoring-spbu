@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Open Penjualan</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open') }}">Open Penjualan</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Open Penjualan</h2>
    <p class="section-lead">
        Tambahkan data Open Penjualan anda.
    </p>
    <form action="{{ route('setoran.open.store') }}" method="post" enctype="multipart/form-data">@csrf
        <div class="row justify-content-center">
            <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Tambah Open Penjualan') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('spbu_id') is-invalid @enderror" name="spbu_id" id="spbu_id">
                                            @foreach($spbu as $s)
                                            <option {{ old('spbu_id') == $s->spbu_id ? 'selected' : '' }} value="{{ $s->spbu_id }}">{{ $s->nama_spbu . ' ('.$s->nomor_spbu.')' }}</option>
                                            @endforeach
                                        </select>

                                        @error('spbu_id')
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
                                        <input id="tanggal" type="date"
                                            class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                            value="{{ is_null(old("tanggal")) ? date('Y-m-d') : old("tanggal") }}"/>

                                        @error('tanggal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="jam_kerja_id" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('jam_kerja_id') is-invalid @enderror" name="jam_kerja_id" id="jam_kerja_id">
                                            @foreach($jamKerja as $s)
                                            <option {{ old('jam_kerja_id') == $s->jam_kerja_id ? 'selected' : '' }} value="{{ $s->jam_kerja_id }}">{{ $s->nama_jam_kerja }}</option>
                                            @endforeach
                                        </select>

                                        @error('jam_kerja_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="nozzle" value="@foreach($nozzle as $s) {{$s->nozzle_id . '|' .$s->nama_nozzle . ' ('. $s->produk()->first()->nama_produk .')' . '||'}} @endforeach" />
            <div class="col-md-12 que-main">
                <div class="card que-1">
                    <div class="card-header">
                        <h4>{{ __('Stand Meter #1') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-success alert-sm-1 que-1 d-none"> Stand Meter Sudah Dicatat </div>
                                <div class="alert alert-danger alert-sm-1 que-1 d-none"> Stand Meter Belum Dicatat. Silakan anda buat dan konfirmasi di menu Totalisator</div>
                                <div class="alert alert-danger alert-sm-2 que-1 d-none"> Nozzle Tidak ditemukan.</div>
                            </div>
                        </div>
                        <div class="row mb-3 justify-content-center">
                            <label for="nozzle_id" class="col-md-4 col-form-label text-md-end">{{ __('Nozzle')
                                }}</label>

                            <div class="col-md-6">
                                <select class="custom-select @error('nozzle_id') is-invalid @enderror nozzle_id" name="nozzle_id[]" id="nozzle_id" data-que="1">
                                    <option value="0">Silakan Pilih Nozzle</option>
                                    @foreach($nozzle as $s)
                                    <option {{ old('nozzle_id') == $s->nozzle_id ? 'selected' : '' }} value="{{ $s->nozzle_id }}">{{ $s->nama_nozzle . ' ('. $s->produk()->first()->nama_produk .')' }}</option>
                                    @endforeach
                                </select>

                                @error('nozzle_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3 justify-content-center sm-1">
                            <label for="stand_meter_awal" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Awal')
                                }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="stand_meter_awal" type="text" readonly class="form-control stand_meter_awal thousand-style que-1" name="stand_meter_awal" value="{{ "0" }}" />
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 justify-content-center sm-2">
                            <label for="stand_meter_akhir" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Akhir')
                                }}</label>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <input id="stand_meter_akhir" type="text" readonly class="form-control stand_meter_akhir thousand-style que-1" name="stand_meter_akhir" value="{{ '0' }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Pilihan') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                <a type="button" href="{{ route('setoran.open') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                <button type="button" class="btn mx-2 btn-info sm-plus"><i class="fas fa-plus"></i> Tambah Stand meter</button>
                                <button type="submit" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
