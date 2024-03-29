@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Jam Kerja</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('jam_kerja.index') }}">Jam Kerja</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Jam Kerja</h2>
    <p class="section-lead">
        Tambahkan data Jam Kerja anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Jam Kerja') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('jam_kerja.store') }}" method="post" enctype="multipart/form-data">@csrf
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
                                    <label for="nama_jam_kerja" class="col-md-4 col-form-label text-md-end">{{ __('Nama Jam Kerja')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="nama_jam_kerja" type="text"
                                            class="form-control @error('nama_jam_kerja') is-invalid @enderror" name="nama_jam_kerja"
                                            value="{{ old("nama_jam_kerja") }}" autocomplete="name"
                                            autofocus>

                                        @error('nama_jam_kerja')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="batas_awal_in" class="col-md-4 col-form-label text-md-end">{{ __('Batas Awal')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <input id="batas_awal_in" type="time"
                                                class="form-control timepicker @error('batas_awal_in') is-invalid @enderror" name="batas_awal_in"
                                                value="{{ old("batas_awal_in") }}"
                                                autofocus>
                                        </div>

                                        @error('batas_awal_in')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="batas_akhir_in" class="col-md-4 col-form-label text-md-end">{{ __('Batas Akhir')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <input id="batas_akhir_in" type="time"
                                                class="form-control timepicker @error('batas_akhir_in') is-invalid @enderror" name="batas_akhir_in"
                                                value="{{ old("batas_akhir_in") }}"
                                                autofocus>
                                        </div>

                                        @error('batas_akhir_in')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="jam_datang_in" class="col-md-4 col-form-label text-md-end">{{ __('Jam Datang')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <input id="jam_datang_in" type="time"
                                                class="form-control timepicker @error('jam_datang_in') is-invalid @enderror" name="jam_datang_in"
                                                value="{{ old("jam_datang_in") }}"
                                                autofocus>
                                        </div>

                                        @error('jam_datang_in')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="jam_pulang_out" class="col-md-4 col-form-label text-md-end">{{ __('Jam Pulang')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <input id="jam_pulang_out" type="time"
                                                class="form-control timepicker @error('jam_pulang_out') is-invalid @enderror" name="jam_pulang_out"
                                                value="{{ old("jam_pulang_out") }}"
                                                autofocus>
                                        </div>

                                        @error('jam_pulang_out')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="akhir_out" class="col-md-4 col-form-label text-md-end">{{ __('Akhir Out')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <input id="akhir_out" type="time"
                                                class="form-control timepicker @error('akhir_out') is-invalid @enderror" name="akhir_out"
                                                value="{{ old("akhir_out") }}"
                                                autofocus>
                                        </div>

                                        @error('akhir_out')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="jam_istirahat" class="col-md-4 col-form-label text-md-end">{{ __('Jam Istirahat')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <input id="jam_istirahat" type="time"
                                                class="form-control timepicker @error('jam_istirahat') is-invalid @enderror" name="jam_istirahat"
                                                value="{{ old("jam_istirahat") }}"
                                                autofocus>
                                        </div>

                                        @error('jam_istirahat')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="masuk_istirahat" class="col-md-4 col-form-label text-md-end">{{ __('Selesai Istirahat')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <input id="masuk_istirahat" type="time"
                                                class="form-control timepicker @error('masuk_istirahat') is-invalid @enderror" name="masuk_istirahat"
                                                value="{{ old("masuk_istirahat") }}"
                                                autofocus>
                                        </div>

                                        @error('masuk_istirahat')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="batas_telat" class="col-md-4 col-form-label text-md-end">{{ __('Batas Telat')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <input id="batas_telat" type="time"
                                                class="form-control timepicker @error('batas_telat') is-invalid @enderror" name="batas_telat"
                                                value="{{ old("batas_telat") }}"
                                                autofocus>
                                        </div>

                                        @error('batas_telat')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="urutan" class="col-md-4 col-form-label text-md-end">{{ __('Urutan')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-list-ol"></i>
                                                </div>
                                            </div>
                                            <input id="urutan" type="number" min="1"
                                                class="form-control @error('urutan') is-invalid @enderror" name="urutan"
                                                value="{{ old("urutan") }}"
                                                autofocus>
                                        </div>

                                        @error('urutan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('spbu.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
