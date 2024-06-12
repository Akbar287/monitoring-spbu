@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Tambah Pengajuan Pengiriman</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.produk.index') }}">MS2</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman') }}">Pengiriman MS2</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Pengajuan Pengiriman</h2>
    <p class="section-lead">
        Tambahkan data Pengajuan Pengiriman anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Pengajuan Pengiriman') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('ms2.pengiriman.store') }}" method="post" enctype="multipart/form-data">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tanggal" type="date"
                                            class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                            value="{{ is_null(old("tanggal")) ? date('Y-m-d') : old("tanggal") }}" />

                                        @error('tanggal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                @foreach($produk as $p)
                                <div class="row mb-3 justify-content-center ">
                                    <label for="{{ $p->produk_id }}" class="col-md-4 col-form-label text-md-end">{{ __($p->nama_produk)
                                        }}</label>

                                    <div class="col-md-6">
                                        @if($p->detail_rencana_pembelian()->where('ms2', false)->where('delivery', false)->count() > 0)
                                        @foreach($p->detail_rencana_pembelian()->select('detail_rencana_pembelian.detail_rencana_pembelian_id', 'detail_rencana_pembelian.jumlah', 'detail_rencana_pembelian.satuan_jumlah', 'rencana_pembelian.kode_pembelian', 'rencana_pembelian.tanggal_pembelian', 'detail_rencana_file.file_lo_id')->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'detail_rencana_pembelian.rencana_pembelian_id')->leftJoin('detail_rencana_file', 'detail_rencana_file.detail_rencana_pembelian_id', 'detail_rencana_pembelian.detail_rencana_pembelian_id')->where('detail_rencana_file.file_lo_id', null)->where('ms2', false)->where('delivery', false)->orderBy('rencana_pembelian.tanggal_pembelian', 'asc')->get() as $pp)
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" value="{{ $pp->detail_rencana_pembelian_id }}" name="detail_rencana_pembelian_id[]" class="custom-control-input checkbox-drp" id="{{ $pp->detail_rencana_pembelian_id }}">
                                            <label class="custom-control-label" for="{{ $pp->detail_rencana_pembelian_id }}">{{ number_format($pp->jumlah, 2, ".", ",") . ' ' . $pp->satuan_jumlah . ' ('. $pp->kode_pembelian .' | '. $pp->tanggal_pembelian .')' }}</label>
                                        </div>
                                        <select class="custom-select mb-3 d-none select-{{ $pp->detail_rencana_pembelian_id }}">
                                            @foreach($shift as $s)
                                            <option value="{{ $s->jam_kerja_id }}">{{ $s->nama_jam_kerja }}</option>
                                            @endforeach
                                        </select>
                                        @endforeach
                                        @else
                                        <p>Tidak ada Stok {{ $p->nama_produk }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('ms2.pengiriman') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
