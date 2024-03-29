@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Jam Kerja</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('jam_kerja.index') }}">Jam Kerja</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Jam Kerja</h2>
    <p class="section-lead">
        Detail data Jam Kerja anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Jam Kerja') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>

                                    <div class="col-md-6">
                                        <input readonly id="spbu_id" type="text"
                                            class="form-control @error('spbu_id') is-invalid @enderror" name="spbu_id"
                                            value="{{ $jamKerja->spbu()->first()->nama_spbu }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="nama_jam_kerja" class="col-md-4 col-form-label text-md-end">{{ __('Nama Jam Kerja')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input readonly id="nama_jam_kerja" type="text"
                                            class="form-control @error('nama_jam_kerja') is-invalid @enderror" name="nama_jam_kerja"
                                            value="{{ $jamKerja->nama_jam_kerja }}" autocomplete="name"
                                            autofocus>
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
                                            <input id="batas_awal_in" type="time" readonly
                                                class="form-control timepicker @error('batas_awal_in') is-invalid @enderror" name="batas_awal_in"
                                                value="{{ $jamKerja->batas_awal_in }}"
                                                autofocus>
                                        </div>
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
                                            <input id="batas_akhir_in" type="time" readonly
                                                class="form-control timepicker @error('batas_akhir_in') is-invalid @enderror" name="batas_akhir_in"
                                                value="{{ $jamKerja->batas_akhir_in }}"
                                                autofocus>
                                        </div>
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
                                            <input id="jam_datang_in" type="time" readonly
                                                class="form-control timepicker @error('jam_datang_in') is-invalid @enderror" name="jam_datang_in"
                                                value="{{ $jamKerja->jam_datang_in }}"
                                                autofocus>
                                        </div>
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
                                            <input id="jam_pulang_out" type="time" readonly
                                                class="form-control timepicker @error('jam_pulang_out') is-invalid @enderror" name="jam_pulang_out"
                                                value="{{ $jamKerja->jam_pulang_out }}"
                                                autofocus>
                                        </div>
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
                                            <input id="akhir_out" type="time" readonly
                                                class="form-control timepicker @error('akhir_out') is-invalid @enderror" name="akhir_out"
                                                value="{{ $jamKerja->akhir_out }}"
                                                autofocus>
                                        </div>
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
                                            <input id="jam_istirahat" type="time" readonly
                                                class="form-control timepicker @error('jam_istirahat') is-invalid @enderror" name="jam_istirahat"
                                                value="{{ $jamKerja->jam_istirahat }}"
                                                autofocus>
                                        </div>
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
                                            <input id="masuk_istirahat" type="time" readonly
                                                class="form-control timepicker @error('masuk_istirahat') is-invalid @enderror" name="masuk_istirahat"
                                                value="{{ $jamKerja->masuk_istirahat }}"
                                                autofocus>
                                        </div>
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
                                            <input id="batas_telat" type="time" readonly
                                                class="form-control timepicker @error('batas_telat') is-invalid @enderror" name="batas_telat"
                                                value="{{ $jamKerja->batas_telat }}"
                                                autofocus>
                                        </div>
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
                                            <input id="urutan" type="number" min="1" readonly
                                                class="form-control @error('urutan') is-invalid @enderror" name="urutan"
                                                value="{{ $jamKerja->urutan }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('jam_kerja.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <a type="button" href="{{ route('jam_kerja.edit', $jamKerja->jam_kerja_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                        <form action="{{ route('jam_kerja.destroy', $jamKerja->jam_kerja_id) }}" method="POST">@csrf @method('delete')
                                            <button type="submit" onclick="return confirm('Jam Kerja akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
