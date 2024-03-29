@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Pajak</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pajak_pembelian_lib.index') }}">Pajak</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Konfigurasi Pajak Pembelian</h2>
    <p class="section-lead">
        Tambah data Konfigurasi Pajak Pembelian anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Pajak') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('pajak_pembelian_lib.store') }}" method="post" enctype="multipart/form-data">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="ppn" class="col-md-4 col-form-label text-md-end">{{ __('PPN')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="ppn" type="text"
                                        class="form-control @error('ppn') is-invalid @enderror thousand-style" name="ppn"
                                        value="{{ old("ppn") }}" />

                                        @error('ppn')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="pph" class="col-md-4 col-form-label text-md-end">{{ __('PPH')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="pph" type="text"
                                            class="form-control @error('pph') is-invalid @enderror thousand-style" name="pph"
                                            value="{{ old("pph") }}" />

                                        @error('pph')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="ppbkb" class="col-md-4 col-form-label text-md-end">{{ __('PPBKB')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="ppbkb" type="text"
                                            class="form-control @error('ppbkb') is-invalid @enderror thousand-style" name="ppbkb"
                                            value="{{ old("ppbkb") }}" />

                                        @error('ppbkb')
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

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('pajak_pembelian_lib.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
