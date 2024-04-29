@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Ubah Totalisator</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('totalisator.index') }}">Totalisator</a></div>
        <div class="breadcrumb-item"><a href="{{ route('totalisator.show', $standMeter->stand_meter_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Ubah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah Totalisator</h2>
    <p class="section-lead">
        Ubah data Totalisator anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah Totalisator') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('totalisator.update', $standMeter->stand_meter_id) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                @if(session('msg')){!! session('msg') !!} @endif
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-danger">Pastikan anda tau apa yang anda lakukan</div>
                            </div>
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
                                        <input id="stand_meter_awal" type="text"
                                            class="form-control thousand-style @error('stand_meter_awal') is-invalid @enderror" readonly name="stand_meter_awal"
                                            value="{{ $standMeter->stand_meter_awal }}" >
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="stand_meter_akhir" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Akhir')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="stand_meter_akhir" type="text"
                                            class="form-control thousand-style @error('stand_meter_akhir') is-invalid @enderror" name="stand_meter_akhir"
                                            value="{{ $standMeter->stand_meter_akhir }}" >

                                        @error('stand_meter_akhir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="selisih" class="col-md-4 col-form-label text-md-end">{{ __('Selisih')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="selisih" type="text" readonly
                                            class="form-control thousand-style @error('selisih') is-invalid @enderror" name="selisih"
                                            value="{{ $standMeter->stand_meter_akhir - $standMeter->stand_meter_awal }}" >
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('totalisator.show', $standMeter->stand_meter_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
