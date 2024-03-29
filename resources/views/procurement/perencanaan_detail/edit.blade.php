@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Rencana Pembelian</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.index') }}">Rencana Pembelian</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.show', $rencana->rencana_pembelian_id) }}">Detail Rencana Pembelian</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.detail.index', $rencana->rencana_pembelian_id) }}">Detail Produk</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.detail.show', [$rencana->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]) }}">Detail Produk</a></div>
        <div class="breadcrumb-item"> Ubah Detail Produk</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah Detail Rencana Pembelian</h2>
    <p class="section-lead">
        Ubah data Detail Rencana Pembelian anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah Detail Rencana Pembelian') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('perencanaan.detail.update', [$rencana->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('produk_id') is-invalid @enderror" name="produk_id" id="produk_id">
                                            @foreach($produk as $s)
                                            <option {{ $detail->produk()->first()->produk_id == $s->produk_id ? 'selected' : '' }} value="{{ $s->produk_id }}">{{ $s->nama_produk }}</option>
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
                                    <label for="jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Jumlah')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="jumlah" type="text"
                                            class="form-control thousand-style @error('jumlah') is-invalid @enderror" name="jumlah"
                                            value="{{ $detail->jumlah }}" autocomplete="name"
                                            autofocus>
                                        <span class="text-muted">Gunakan titik sebagai 2 angka dibelakang koma (contoh: 8000.00 liter) dan tidak perlu menggunakan koma tapi titik </span>
            
                                        @error('jumlah')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="satuan_jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Satuan Jumlah')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('satuan_jumlah') is-invalid @enderror" name="satuan_jumlah" id="satuan_jumlah">
                                            <option value="liter">Liter</option>
                                        </select>
            
                                        @error('satuan_jumlah')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('perencanaan.detail.show', [$rencana->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
