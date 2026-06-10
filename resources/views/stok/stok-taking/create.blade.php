@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Tambah Stok Taking</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
            <div class="breadcrumb-item"><a href="{{ route('stok-taking.index') }}">Stok Taking</a></div>
            <div class="breadcrumb-item">Tambah</div>
        </div>
    </div>
    <div class="section-body">
        <h2 class="section-title">Tambah Stok Taking</h2>
        <p class="section-lead">
            Tambahkan data Stok Taking anda.
        </p>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Tambah Stok Taking') }}</h4>
                    </div>
                    <div class="card-body">
                        @if (session('msg'))
                            {!! session('msg') !!}
                        @endif
                        <form action="{{ route('stok-taking.store') }}" method="post" enctype="multipart/form-data">@csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-3 justify-content-center">
                                        <label for="type_dokumen_id"
                                            class="col-md-4 col-form-label text-md-end">{{ __('Tipe Dokumen') }}</label>

                                        <div class="col-md-6">
                                            <select class="custom-select @error('type_dokumen_id') is-invalid @enderror"
                                                name="type_dokumen_id" id="type_dokumen_id">
                                                @foreach ($typeDokumen as $s)
                                                    <option
                                                        {{ old('type_dokumen_id') == $s->type_dokumen_stok_id ? 'selected' : '' }}
                                                        value="{{ $s->type_dokumen_stok_id }}">{{ $s->type_movement }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @error('type_dokumen_id')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3 justify-content-center">
                                        <label for="dombak_id"
                                            class="col-md-4 col-form-label text-md-end">{{ __('Dombak') }}</label>

                                        <div class="col-md-6">
                                            <select class="custom-select @error('dombak_id') is-invalid @enderror"
                                                name="dombak_id" id="dombak_id">
                                                @foreach ($dombak as $s)
                                                    <option {{ old('dombak_id') == $s->dombak_id ? 'selected' : '' }}
                                                        value="{{ $s->dombak_id }}">
                                                        {{ $s->nama_dombak . ' (' . $s->stok_inventory_dombak()->first()->stok_inventory()->first()->produk()->first()->nama_produk . '-' . $s->stok_inventory_dombak()->first()->stok . 'Liter)' }}
                                                    </option>
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
                                        <label for="tanggal"
                                            class="col-md-4 col-form-label text-md-end">{{ __('Tanggal') }}</label>

                                        <div class="col-md-6">
                                            <input id="tanggal" type="date"
                                                class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                                value="{{ old('tanggal') != null ? old('tanggal') : date('Y-m-d') }}">

                                            @error('tanggal')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3 justify-content-center">
                                        <label for="jam_kerja_id"
                                            class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja') }}</label>

                                        <div class="col-md-6">
                                            <select class="custom-select @error('jam_kerja_id') is-invalid @enderror"
                                                name="jam_kerja_id" id="jam_kerja_id">
                                                @foreach ($jamKerja as $s)
                                                    <option {{ old('jam_kerja_id') == $s->jam_kerja_id ? 'selected' : '' }}
                                                        value="{{ $s->jam_kerja_id }}">
                                                        {{ $s->nama_jam_kerja . ' (' . $s->batas_awal_in . '-' . $s->batas_akhir_in . ')' }}
                                                    </option>
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
                                        <label for="tinggi_dombak"
                                            class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm)') }}</label>

                                        <div class="col-md-6">
                                            <input id="tinggi_dombak" type="number" step=".1"
                                                class="form-control thousand-style @error('tinggi_dombak') is-invalid @enderror"
                                                name="tinggi_dombak" value="{{ old('tinggi_dombak') }}">

                                            @error('tinggi_dombak')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3 justify-content-center">
                                        <label for="volume_sebelumnya"
                                            class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Sebelumnya') }}</label>

                                        <div class="col-md-6">
                                            <input id="volume_sebelumnya" type="text"
                                                class="form-control thousand-style @error('volume_sebelumnya') is-invalid @enderror"
                                                name="volume_sebelumnya" value="0" readonly>

                                            @error('volume_sebelumnya')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3 justify-content-center">
                                        <label for="volume"
                                            class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Aktual') }}</label>

                                        <div class="col-md-6">
                                            <input id="volume" type="text"
                                                class="form-control thousand-style @error('volume') is-invalid @enderror"
                                                name="volume" value="0" readonly>

                                            @error('volume')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3 justify-content-center">
                                        <label for="volume_selisih"
                                            class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) (Loss/Profit)') }}</label>

                                        <div class="col-md-6">
                                            <input id="volume_selisih" type="text"
                                                class="form-control thousand-style @error('volume_selisih') is-invalid @enderror"
                                                name="volume_selisih" value="0" readonly>

                                            @error('volume_selisih')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3 justify-content-center">
                                        <div class="alert alert-danger">Pastikan sudah diisi dengan benar. Setiap kesalahan
                                            akan memengaruhi stok dombak dan TiDAK BISA DIHAPUS/DIUBAH</div>
                                    </div>

                                    <div class="row mb-0">
                                        <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                            <a type="button" href="{{ route('stok-taking.index') }}"
                                                class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                            <button type="submit" class="btn mx-2 btn-success" disabled><i
                                                    class="fas fa-pen"></i> Simpan</button>
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
