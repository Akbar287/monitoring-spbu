@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Ubah Stok Taking</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-taking.index') }}">Stok Taking</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-taking.show', $stok->dokumen_stok_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Ubah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah Stok Taking</h2>
    <p class="section-lead">
        Ubah data Stok Taking anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah Stok Taking') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('stok-taking.update', $stok->dokumen_stok_id) }}" method="post" enctype="multipart/form-data">@csrf @method('put') 
                        <div class="row">
                            <div class="col-md-12">
                                @if(session('msg')){!! session('msg') !!} @endif
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-danger">Pastikan anda tau apa yang anda lakukan</div>
                            </div>
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="type_dokumen_id" class="col-md-4 col-form-label text-md-end">{{ __('Tipe Dokumen')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('type_dokumen_id') is-invalid @enderror" name="type_dokumen_id" id="type_dokumen_id">
                                            @foreach($typeDokumen as $s)
                                            <option {{ old('type_dokumen_id') == $s->type_dokumen_stok_id ? 'selected' : '' }} value="{{ $s->type_dokumen_stok_id }}">{{ $s->type_movement }}</option>
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
                                    <label for="dombak_id" class="col-md-4 col-form-label text-md-end">{{ __('Dombak')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('dombak_id') is-invalid @enderror" name="dombak_id" id="dombak_id">
                                            @foreach($dombak as $s)
                                            <option {{ old('dombak_id') == $s->dombak_id ? 'selected' : '' }} value="{{ $s->dombak_id }}">{{ $s->nama_dombak . ' ('. $s->stok_inventory_dombak()->first()->stok_inventory()->first()->produk()->first()->nama_produk . '-' . $s->stok_inventory_dombak()->first()->stok .'Liter)' }}</option>
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
                                    <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="tanggal" type="date"
                                            class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                            value="{{ old("tanggal") != null ? old("tanggal") : date('Y-m-d') }}" autocomplete="name"
                                            autofocus>
            
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
                                            <option {{ old('jam_kerja_id') == $s->jam_kerja_id ? 'selected' : '' }} value="{{ $s->jam_kerja_id }}">{{ $s->nama_jam_kerja . ' (' . $s->batas_awal_in . '-' . $s->batas_akhir_in . ')' }}</option>
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
                                    <label for="tinggi_dombak" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm)')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tinggi_dombak" type="number" step=".1"
                                            class="form-control thousand-style @error('tinggi_dombak') is-invalid @enderror" name="tinggi_dombak"
                                            value="{{ $stok->konversi()->first()->tinggi }}" autocomplete="name"
                                            autofocus>

                                        @error('tinggi_dombak')
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
                                        <input id="volume_sebelumnya" type="text"
                                            class="form-control thousand-style @error('volume_sebelumnya') is-invalid @enderror" name="volume_sebelumnya"
                                            value="{{ $stok->stok_akhir - ($stok->stok_awal + is_null($stok->losses()->first()) ? 0 : ($stok->losses()->first()->simbol == 'kurang' ?  $stok->losses()->first()->jumlah : -1 * $stok->losses()->first()->jumlah)) }}" autocomplete="name" readonly
                                            autofocus>
            
                                        @error('volume_sebelumnya')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="volume" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Aktual')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume" type="text"
                                            class="form-control thousand-style @error('volume') is-invalid @enderror" name="volume"
                                            value="{{ $stok->stok_akhir }}" autocomplete="name" readonly
                                            autofocus>
            
                                        @error('volume')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="volume_selisih" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) (Loss/Profit)')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_selisih" type="text"
                                            class="form-control thousand-style @error('volume_selisih') is-invalid @enderror" name="volume_selisih"
                                            value="{{ is_null($stok->losses()->first()) ? 0 : $stok->losses()->first()->jumlah }}" autocomplete="name" readonly
                                            autofocus>
            
                                        @error('volume_selisih')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('stok-taking.show', $stok->dokumen_stok_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
