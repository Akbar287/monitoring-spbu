@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Open Penjualan</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open') }}">Open Penjualan</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open.show', $penjualan->penjualan_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open.detail', $penjualan->penjualan_id) }}">Detail Open Penjualan</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Open Penjualan</h2>
    <p class="section-lead">
        Detail data Open Penjualan anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Open Penjualan') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="stand_meter_id" class="col-md-4 col-form-label text-md-end">{{ __('Nama Nozzle')
                                    }}</label>

                                <div class="col-md-6">
                                    <input type="text" readonly name="stand_meter_id" id="stand_meter_id" class=" form-control @error('stand_meter_id') is-invalid @enderror" value="{{ $detail->stand_meter()->first()->nozzle()->first()->nama_nozzle . ' ('. $detail->stand_meter()->first()->stand_meter_awal . ' - '.$detail->stand_meter()->first()->stand_meter_akhir  .')' }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="stand_meter_awal" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Awal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input type="text" readonly name="stand_meter_awal" id="stand_meter_awal" class="thousand-style form-control @error('stand_meter_awal') is-invalid @enderror" value="{{ number_format($detail->stand_meter()->first()->stand_meter_awal, 0, ".", ",") }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="stand_meter_akhir" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Akhir')
                                    }}</label>

                                <div class="col-md-6">
                                    <input type="text" readonly name="stand_meter_akhir" id="stand_meter_akhir" class="thousand-style form-control @error('stand_meter_akhir') is-invalid @enderror" value="{{ number_format($detail->stand_meter()->first()->stand_meter_akhir, 0, ".", ",") }}" />
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
                                        <input type="text" readonly name="harga_jual" id="harga_jual" class="form-control thousand-style @error('harga_jual') is-invalid @enderror" value="{{ number_format($detail->harga()->first()->harga_jual, 0, ".", ",") }}" />
                                    </div>
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
                                        <input type="text" readonly name="total_detail_jual" id="total_detail_jual" class="thousand-style form-control @error('total_detail_jual') is-invalid @enderror" value="{{ number_format(($detail->stand_meter()->first()->stand_meter_akhir - $detail->stand_meter()->first()->stand_meter_awal) * $detail->harga()->first()->harga_jual, 0, ".", ",") }}" />
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('setoran.open.detail', $penjualan->penjualan_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <form action="{{ route('setoran.open.detail.delete', [$penjualan->penjualan_id, $detail->detail_penjualan_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Detail Open Penjualan akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
