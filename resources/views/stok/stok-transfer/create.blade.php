@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Tambah Stok Transfer</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-transfer.index') }}">Stok Transfer</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Stok Transfer</h2>
    <p class="section-lead">
        Tambahkan data Stok Transfer anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            <form action="{{ route('stok-transfer.store') }}" method="post" enctype="multipart/form-data">@csrf
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Informasi Umum') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tanggal" type="date"
                                            class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                            value="{{ old("tanggal") != null ? old("tanggal") : date('Y-m-d') }}" >

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
                                            <option {{ old('jam_kerja_id') == $s->jam_kerja_id ? 'selected' : '' }} value="{{ $s->jam_kerja_id }}">{{ $s->nama_jam_kerja . ' (' . $s->jam_datang_in . '-' . $s->jam_pulang_out . ')' }}</option>
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
                                    <label for="waktu" class="col-md-4 col-form-label text-md-end">{{ __('Waktu')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input type="time" value="{{ old('waktu') ?? date('H:i') }}" class="form-control @error('waktu') is-invalid @enderror" name="waktu" id="waktu">

                                        @error('waktu')
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

                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Dombak Pertama') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="dombak_id" class="col-md-4 col-form-label text-md-end">{{ __('Dombak')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('dombak_id') is-invalid @enderror" name="dombak_id" id="dombak_id">
                                            <option value="">Pilih Dombak</option>
                                            @foreach($dombak as $s)
                                            <option {{ old('dombak_id') == $s->dombak_id ? 'selected' : '' }} value="{{ $s->dombak_id }}">{{ $s->nama_dombak . ' ('. $s->stok_inventory_dombak()->first()->stok_inventory()->first()->produk()->first()->nama_produk . ': ' . $s->stok_inventory_dombak()->first()->stok .'Liter)' }}</option>
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
                                    <label for="volume_tercatat" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Tercatat Terakhir')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_tercatat" type="text" class="form-control thousand-style @error('volume_tercatat') is-invalid @enderror" name="volume_tercatat" value="0" readonly />

                                        @error('volume_tercatat')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tinggi_dombak" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Sebelum')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tinggi_dombak" readonly type="number" step=".1" class="form-control thousand-style @error('tinggi_dombak') is-invalid @enderror" name="tinggi_dombak" value="{{ old("tinggi_dombak") }}" >

                                        @error('tinggi_dombak')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tinggi_dombak_setelahnya" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Setelah')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tinggi_dombak_setelahnya" readonly type="number" step=".1" class="form-control thousand-style @error('tinggi_dombak_setelahnya') is-invalid @enderror" name="tinggi_dombak_setelahnya" value="{{ old("tinggi_dombak_setelahnya") }}" >

                                        @error('tinggi_dombak_setelahnya')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="volume_sebelumnya" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Sebelumnya')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_sebelumnya" type="text" class="form-control thousand-style @error('volume_sebelumnya') is-invalid @enderror" name="volume_sebelumnya" value="0" readonly />

                                        @error('volume_sebelumnya')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="volume_setelah" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Setelah')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_setelah" type="text" class="form-control thousand-style @error('volume_setelah') is-invalid @enderror" name="volume_setelah" value="0" readonly />

                                        @error('volume_setelah')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="volume" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Dipindahkan')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume" type="text" class="form-control thousand-style @error('volume') is-invalid @enderror" name="volume" value="0" readonly />

                                        @error('volume')
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

                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Dombak Kedua') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="dombak_kedua_id" class="col-md-4 col-form-label text-md-end">{{ __('Dombak')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('dombak_kedua_id') is-invalid @enderror" name="dombak_kedua_id" disabled id="dombak_kedua_id">
                                            <option value="">Silahkan Pilih Dombak Kedua</option>
                                        </select>

                                        @error('dombak_kedua_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="volume_sebelumnya_kedua_terakhir" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Tercatat Terakhir')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_sebelumnya_kedua_terakhir" type="text"
                                            class="form-control thousand-style @error('volume_sebelumnya_kedua_terakhir') is-invalid @enderror" name="volume_sebelumnya_kedua_terakhir"
                                            value="0" readonly
                                            >

                                        @error('volume_sebelumnya_kedua_terakhir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tinggi_dombak_kedua" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Sebelum')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tinggi_dombak_kedua" type="number" step=".1" class="form-control thousand-style @error('tinggi_dombak_kedua') is-invalid @enderror" name="tinggi_dombak_kedua" readonly value="{{ old("tinggi_dombak_kedua") }}" >

                                        @error('tinggi_dombak_kedua')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tinggi_dombak_kedua_setelah" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Setelah')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tinggi_dombak_kedua_setelah" type="number" step=".1" class="form-control thousand-style @error('tinggi_dombak_kedua_setelah') is-invalid @enderror" name="tinggi_dombak_kedua_setelah" readonly value="{{ old("tinggi_dombak_kedua_setelah") }}" >

                                        @error('tinggi_dombak_kedua_setelah')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="volume_sebelumnya_kedua" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Sebelumnya')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_sebelumnya_kedua" type="text"
                                            class="form-control thousand-style @error('volume_sebelumnya_kedua') is-invalid @enderror" name="volume_sebelumnya_kedua"
                                            value="0" readonly
                                            >

                                        @error('volume_sebelumnya_kedua')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="volume_sebelumnya_kedua_setelah" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Setelah')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_sebelumnya_kedua_setelah" type="text"
                                            class="form-control thousand-style @error('volume_sebelumnya_kedua_setelah') is-invalid @enderror" name="volume_sebelumnya_kedua_setelah"
                                            value="0" readonly
                                            >

                                        @error('volume_sebelumnya_kedua_setelah')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="volume_kedua" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Terpindah')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_kedua" type="text"
                                            class="form-control thousand-style @error('volume_kedua') is-invalid @enderror" name="volume_kedua"
                                            value="0" readonly>

                                        @error('volume_kedua')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="volume_selisih_kedua" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) (Loss/Profit)')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_selisih_kedua" type="text"
                                            class="form-control thousand-style @error('volume_selisih_kedua') is-invalid @enderror" name="volume_selisih_kedua"
                                            value="0" readonly
                                            >

                                        @error('volume_selisih_kedua')
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

                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Aksi') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <div class="alert alert-danger">Pastikan sudah diisi dengan benar. Setiap kesalahan akan memengaruhi stok dombak dan TiDAK BISA DIHAPUS/DIUBAH</div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('stok-transfer.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button type="submit" class="btn mx-2 btn-success" disabled><i class="fas fa-pen"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
