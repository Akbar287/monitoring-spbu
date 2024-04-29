@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Totalisator</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('totalisator.index') }}">Totalisator</a></div>
        <div class="breadcrumb-item"><a href="{{ route('totalisator.riwayat') }}">Riwayat</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Totalisator</h2>
    <p class="section-lead">
        Detail data Totalisator anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Totalisator') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('totalisator.file.riwayat.index', $standMeter->stand_meter_id) }}" class="btn btn-primary">
                            File
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control" name="tanggal"
                                        value="{{ $standMeter->tanggal }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jam_kerja" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="jam_kerja" type="text" readonly
                                        class="form-control" name="jam_kerja"
                                        value="{{ $standMeter->jam_kerja()->first()->nama_jam_kerja }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="dombak_id" class="col-md-4 col-form-label text-md-end">{{ __('Dombak')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="dombak_id" type="text" readonly
                                        class="form-control" name="dombak_id"
                                        value="{{ $standMeter->dombak()->first()->nama_dombak }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nozzle_id" class="col-md-4 col-form-label text-md-end">{{ __('Nozzle')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nozzle_id" type="text" readonly
                                        class="form-control" name="nozzle_id"
                                        value="{{ $standMeter->nozzle()->first()->nama_nozzle }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="stand_meter_awal" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Awal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="stand_meter_awal" type="text" readonly
                                        class="form-control" name="stand_meter_awal"
                                        value="{{ number_format($standMeter->stand_meter_awal, 0, ".", ",") }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="stand_meter_akhir" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Akhir')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="stand_meter_akhir" type="text" readonly
                                        class="form-control" name="stand_meter_akhir"
                                        value="{{ number_format($standMeter->stand_meter_akhir, 0, ".", ",") }}" />
                                </div>
                            </div>

                            @if($standMeter->stand_meter_akhir - $standMeter->stand_meter_awal > 0)
                            <div class="row mb-3 justify-content-center">
                                <label for="selisih" class="col-md-4 col-form-label text-md-end">{{ __('Selisih')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="selisih" type="text" readonly
                                        class="form-control thousand-style" name="selisih"
                                        value="{{ number_format($standMeter->stand_meter_akhir - $standMeter->stand_meter_awal, 0, ".", ",") }}" />
                                </div>
                            </div>
                            @endif

                            @if(!$standMeter->is_confirmation)
                            <div class="row mb-3 justify-content-center">
                                <div class="alert alert-danger">Konfirmasi Jika Datanya sudah benar dan Fixed. dikonfirmasi akan mengubah stok produk dan stok dombak dan tidak bisa di undo.</div>
                            </div>
                            @endif

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('totalisator.riwayat') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    @if(!$standMeter->is_confirmation)
                                    <form action="{{ route('totalisator.confirmation', $standMeter->stand_meter_id) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Stand Meter akan dikonfirmasi!\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-success"><i class="fas fa-check"></i> Konfirmasi</button>
                                    </form>
                                    @endif
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
