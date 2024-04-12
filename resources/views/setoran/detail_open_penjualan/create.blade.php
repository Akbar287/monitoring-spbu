@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Open Penjualan</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open') }}">Open Penjualan</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open.show', $penjualan->penjualan_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open.detail', $penjualan->penjualan_id) }}">Detail Open Penjualan</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Open Penjualan</h2>
    <p class="section-lead">
        Tambahkan data Detail Open Penjualan anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Detail Open Penjualan') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('setoran.open.detail.store', $penjualan->penjualan_id) }}" method="post" enctype="multipart/form-data">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="nozzle_id" class="col-md-4 col-form-label text-md-end">{{ __('Nama Nozzle')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('nozzle_id') is-invalid @enderror" name="nozzle_id" id="nozzle_id">
                                            <option value="0">Silakan Pilih Nozzle</option>
                                            @foreach($nozzle as $s)
                                            <option {{ old('nozzle_id') == $s->nozzle_id ? 'selected' : '' }} data-smaw="{{ number_format($s->stand_meter_awal, 0, ".", ",") }}" data-smak="{{ number_format($s->stand_meter_akhir, 0, ".", ",") }}" data-hj="{{ number_format($s->harga_jual, 2, ".", ",") }}" data-thj="{{ number_format(($s->stand_meter_akhir - $s->stand_meter_awal) * $s->harga_jual, 2, ".", ",") }}" value="{{ $s->nozzle_id }}">{{ $s->nama_nozzle . ' (' . $s->stand_meter_awal . ' - '.$s->stand_meter_akhir .')' }}</option>
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
                                    <label for="stand_meter_awal" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Awal')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input type="text" readonly name="stand_meter_awal" id="stand_meter_awal" class="thousand-style form-control @error('stand_meter_awal') is-invalid @enderror" value="0" />

                                        @error('stand_meter_awal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="stand_meter_akhir" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Akhir')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input type="text" readonly name="stand_meter_akhir" id="stand_meter_akhir" class="thousand-style form-control @error('stand_meter_akhir') is-invalid @enderror" value="0" />

                                        @error('stand_meter_akhir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="harga_jual" class="col-md-4 col-form-label text-md-end">{{ __('Harga Jual')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    Rp
                                                </div>
                                            </div>
                                            <input type="text" readonly name="harga_jual" id="harga_jual" class="form-control thousand-style @error('harga_jual') is-invalid @enderror" value="0" />
                                        </div>

                                        @error('harga_jual')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="total_detail_jual" class="col-md-4 col-form-label text-md-end">{{ __('Total Penjualan')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    Rp
                                                </div>
                                            </div>
                                            <input type="text" readonly name="total_detail_jual" id="total_detail_jual" class="thousand-style form-control @error('total_detail_jual') is-invalid @enderror" value="0" />
                                        </div>

                                        @error('total_detail_jual')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('setoran.open.detail', $penjualan->penjualan_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
