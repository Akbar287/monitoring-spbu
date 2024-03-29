@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Konfirmasi Rencana Pembelian</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.index') }}">Rencana Pembelian</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.show', $rencana->rencana_pembelian_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Konfirmasi</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Konfirmasi Rencana Pembelian</h2>
    <p class="section-lead">
        Konfirmasi data Rencana Pembelian anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Konfirmasi Rencana Pembelian') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('perencanaan.konfirmasi.update', $rencana->rencana_pembelian_id) }}" method="post">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="jenis" class="col-md-4 col-form-label text-md-end">{{ __('Jenis Konfirmasi')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="jenis" value="true" class="selectgroup-input">
                                                <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-check"></i></span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="jenis" value="false" class="selectgroup-input" checked="">
                                                <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-times"></i></span>
                                            </label>
                                        </div>
            
                                        @error('jenis')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Catatan')
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
                                        <a type="button" href="{{ route('perencanaan.show', $rencana->rencana_pembelian_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button type="submit" class="btn btn-success">Konfirmasi</button>
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
