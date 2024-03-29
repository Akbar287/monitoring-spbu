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
        <div class="breadcrumb-item"> Lihat Detail Produk</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Rencana Pembelian</h2>
    <p class="section-lead">
        Detail data Rencana Pembelian anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Produk') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="produk_id" type="text" readonly
                                        class="form-control" name="produk_id"
                                        value="{{ $detail->produk()->first()->nama_produk }}" autocomplete="name"
                                        autofocus>

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
                                    <input id="jumlah" type="text" readonly
                                        class="form-control @error('jumlah') is-invalid @enderror" name="jumlah"
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
                                    <input id="satuan_jumlah" type="text" readonly
                                        class="form-control" name="satuan_jumlah"
                                        value="{{ $detail->satuan_jumlah }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('perencanaan.detail.index', [$rencana->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('perencanaan.detail.edit', [$rencana->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('perencanaan.detail.destroy', [$rencana->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Detail Rencana Pembelian akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
