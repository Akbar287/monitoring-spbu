@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Produk Pembelian</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembelian.index') }}">Pembelian</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembelian.show', $pembelian->rencana_pembelian_id) }}">Detail Pembelian</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembelian.detail.index', $pembelian->rencana_pembelian_id) }}">Detail Produk</a></div>
        <div class="breadcrumb-item"> Lihat Detail Produk</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Pembelian</h2>
    <p class="section-lead">
        Detail data Pembelian anda.
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
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="rit" class="col-md-4 col-form-label text-md-end">{{ __('RIT')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="rit" type="text" readonly
                                        class="form-control @error('rit') is-invalid @enderror" name="rit"
                                        value="{{ number_format($detail->rit, 6, ".", ',') }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Jumlah / Quantity')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="jumlah" type="text" readonly
                                        class="form-control @error('jumlah') is-invalid @enderror" name="jumlah"
                                        value="{{ number_format($detail->jumlah, 6, ".", ',') }}" autocomplete="name"
                                        autofocus>
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

                            <div class="row mb-3 justify-content-center">
                                <label for="harga" class="col-md-4 col-form-label text-md-end">{{ __('Harga')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                          <div class="input-group-text">Rp</div>
                                        </div>
                                        <input id="harga" type="text" readonly
                                            class="form-control" name="harga"
                                            value="{{ number_format($detail->harga, 6, ".", ",") }}" autocomplete="name"
                                            autofocus>
                                      </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="total_harga" class="col-md-4 col-form-label text-md-end">{{ __('Total Harga')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                          <div class="input-group-text">Rp</div>
                                        </div>
                                        <input id="total_harga" type="text" readonly
                                            class="form-control" name="total_harga"
                                            value="{{ number_format($detail->total_harga, 6, ".", ",") }}" autocomplete="name"
                                            autofocus>

                                      </div>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('pembelian.detail.index', [$pembelian->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('pembelian.detail.edit', [$pembelian->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
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
