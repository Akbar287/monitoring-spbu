@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Pembayaran</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Rencana Pembayaran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembayaran.show', $rencana->rencana_pembelian_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Pembayaran</h2>
    <p class="section-lead">
        Tambahkan data Pembayaran anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Pembayaran') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('pembayaran.store', $rencana->rencana_pembelian_id) }}" method="post" enctype="multipart/form-data">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Penyetor')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('member_id') is-invalid @enderror" name="member_id" id="member_id">
                                            @foreach($member as $s)
                                            <option {{ old('member_id') == $s->member_id ? 'selected' : '' }} value="{{ $s->member_id }}">{{ $s->ktp()->first()->nama  }}</option>
                                            @endforeach
                                        </select>

                                        @error('member_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_cek_bg" class="col-md-4 col-form-label text-md-end">{{ __('No Cek BG')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_cek_bg" type="text"
                                            class="form-control @error('no_cek_bg') is-invalid @enderror" name="no_cek_bg"
                                            value="{{ old("no_cek_bg") }}" autocomplete="name"
                                            autofocus>

                                        @error('no_cek_bg')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_rekening" class="col-md-4 col-form-label text-md-end">{{ __('No Rekening')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_rekening" type="text"
                                            class="form-control @error('no_rekening') is-invalid @enderror" name="no_rekening"
                                            value="{{ old("no_rekening") }}" autocomplete="name"
                                            autofocus>

                                        @error('no_rekening')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="nama_rekening" class="col-md-4 col-form-label text-md-end">{{ __('Nama Rekening')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="nama_rekening" type="text"
                                            class="form-control @error('nama_rekening') is-invalid @enderror" name="nama_rekening"
                                            value="{{ old("nama_rekening") }}" autocomplete="name"
                                            autofocus>

                                        @error('nama_rekening')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Bayar')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tanggal" type="date"
                                            class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                            value="{{ is_null(old("tanggal")) ? date('Y-m-d') : old("tanggal") }}" autocomplete="name"
                                            autofocus>

                                        @error('tanggal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="total_setoran" class="col-md-4 col-form-label text-md-end">{{ __('Total Setor Ke Bank')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">Rp. </div>
                                            </div>
                                            <input id="total_setoran" type="text"
                                                class="form-control thousand-style @error('total_setoran') is-invalid @enderror" name="total_setoran"
                                                value="{{ old("total_setoran") }}" autocomplete="name"
                                                autofocus>
                                        </div>

                                        @error('total_setoran')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('pembayaran.show', $rencana->rencana_pembelian_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
