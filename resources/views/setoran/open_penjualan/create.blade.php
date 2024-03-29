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
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Open Penjualan') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('setoran.open.store') }}" method="post" enctype="multipart/form-data">@csrf
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
                                    <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('produk_id') is-invalid @enderror" name="produk_id" id="produk_id">
                                            @foreach($produk as $s)
                                            <option {{ old('produk_id') == $s->produk_id ? 'selected' : '' }} value="{{ $s->produk_id }}">{{ $s->nama_produk }}</option>
                                            @endforeach
                                        </select>

                                        @error('produk_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="dispenser_id" class="col-md-4 col-form-label text-md-end">{{ __('Dispenser')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('dispenser_id') is-invalid @enderror" name="dispenser_id" id="dispenser_id">
                                            @foreach($dispenser as $s)
                                            <option {{ old('dispenser_id') == $s->dispenser_id ? 'selected' : '' }} value="{{ $s->dispenser_id }}">{{ $s->nama_dispenser }}</option>
                                            @endforeach
                                        </select>

                                        @error('dispenser_id')
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

                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tanggal" type="date"
                                            class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                            value="{{ is_null(old("tanggal")) ? date('Y-m-d') : old("tanggal") }}" autocomplete="name"
                                            autofocus>

                                        @error('tanggal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="total_uang_kotak" class="col-md-4 col-form-label text-md-end">{{ __('Total Uang Kotak')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    Rp
                                                </div>
                                            </div>
                                            <input id="total_uang_kotak" type="text"
                                                class="form-control thousand-style @error('total_uang_kotak') is-invalid @enderror" name="total_uang_kotak"
                                                value="{{ old("total_uang_kotak") }}" autocomplete="name"
                                                autofocus>
                                        </div>

                                        @error('total_uang_kotak')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="total_penjualan" class="col-md-4 col-form-label text-md-end">{{ __('Total Penjualan')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    Rp
                                                </div>
                                            </div>
                                            <input id="total_penjualan" type="text" readonly
                                                class="form-control thousand-style @error('total_penjualan') is-invalid @enderror" name="total_penjualan"
                                                value="{{ '0' }}" autocomplete="name"
                                                autofocus>
                                        </div>

                                        @error('total_penjualan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('setoran.open') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button type="submit" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Simpan</button>
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
