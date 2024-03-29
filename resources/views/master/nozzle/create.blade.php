@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Nozzle</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('nozzle.index') }}">Nozzle</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Nozzle</h2>
    <p class="section-lead">
        Tambahkan data Nozzle anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Nozzle') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('nozzle.store') }}" method="post" enctype="multipart/form-data">@csrf
                        <div class="row">
                            <div class="col-md-12">
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
                                    <label for="nama_nozzle" class="col-md-4 col-form-label text-md-end">{{ __('Nama Nozzle')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="nama_nozzle" type="text"
                                            class="form-control @error('nama_nozzle') is-invalid @enderror" name="nama_nozzle"
                                            value="{{ old("nama_nozzle") }}" autocomplete="name"
                                            autofocus>

                                        @error('nama_nozzle')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="is_aktif" class="col-md-4 col-form-label text-md-end">{{ __('Aktifkan')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('is_aktif') is-invalid @enderror" name="is_aktif" id="is_aktif">
                                            <option {{ old('is_aktif') == 'false' ? 'selected' : '' }} value="false">Jangan Aktifkan</option>
                                            <option {{ old('is_aktif') == 'true' ? 'selected' : '' }} value="true">Aktifkan</option>
                                        </select>

                                        @error('is_aktif')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                        }}</label>

                                    <div class="col-md-6">
                                        <textarea id="keterangan" type="text" rows="3" cols="3"
                                            class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                            value="" autocomplete="name"
                                            autofocus>{{ old("keterangan") }}</textarea>

                                        @error('keterangan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('nozzle.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
