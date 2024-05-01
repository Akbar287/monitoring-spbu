@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File LO</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.rencana.index') }}">Rencana Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.rencana.show', $rencana->rencana_pembelian_id) }}">Detail Rencana</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.index', $rencana->rencana_pembelian_id) }}">Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.show', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id]) }}">Detail Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('file_lo.index', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id]) }}">File LO</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah File LO</h2>
    <p class="section-lead">
        Tambahkan data File LO anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah File LO') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('file_lo.store', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id]) }}" method="post" enctype="multipart/form-data">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="detail_rencana_pembelian_id" class="col-md-4 col-form-label text-md-end">{{ __('Detail Rencana Pembelian')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('detail_rencana_pembelian_id') is-invalid @enderror" name="detail_rencana_pembelian_id" id="detail_rencana_pembelian_id">
                                            @foreach($detailRencana as $s)
                                            <option {{ old('detail_rencana_pembelian_id') == $s->detail_rencana_pembelian_id ? 'selected' : '' }} value="{{ $s->detail_rencana_pembelian_id }}">{{ $s->produk()->first()->nama_produk . ' (' . number_format($s->jumlah, 6, ",", ".") . ' ' . $s->satuan_jumlah .')' }}</option>
                                            @endforeach
                                        </select>

                                        @error('detail_rencana_pembelian_id')
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
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">Rp.</div>
                                            </div>
                                            <input id="jumlah" type="text"
                                                class="form-control @error('jumlah') is-invalid @enderror thousand-style" name="jumlah"
                                                value="{{ old("jumlah") }}">
                                        </div>


                                        @error('jumlah')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_faktur" class="col-md-4 col-form-label text-md-end">{{ __('No. Faktur')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_faktur" type="text"
                                            class="form-control @error('no_faktur') is-invalid @enderror" name="no_faktur"
                                            value="{{ old("no_faktur") }}">

                                        @error('no_faktur')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_lo" class="col-md-4 col-form-label text-md-end">{{ __('No. LO')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_lo" type="text"
                                            class="form-control @error('no_lo') is-invalid @enderror" name="no_lo"
                                            value="{{ old("no_lo") }}">

                                        @error('no_lo')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="gambar" class="col-md-4 col-form-label text-md-end">{{
                                        __('File')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="custom-file">
                                            <input type="file" name="gambar" class="custom-file-input" id="gambar">
                                            <label class="custom-file-label" for="gambar">Pilih File</label>
                                        </div>

                                        @error('gambar')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror

                                        <img src="" alt="{{ old("gambar") }}" class="img img-thumbnail img-temporary d-none" style="width: 800px; height: auto">
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('file_lo.index', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id]) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
