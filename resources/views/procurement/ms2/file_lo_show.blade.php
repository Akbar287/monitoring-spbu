@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail File LO</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.produk.index') }}">MS2</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman') }}">PengirimanMS2</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman.show', $pengiriman->pengiriman_id) }}">Detail Pengiriman</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail File LO</h2>
    <p class="section-lead">
         data File LO anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail File LO') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="detail_rencana_pembelian_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_faktur" type="text" readonly class="form-control" name="no_faktur" value="{{ $file_lo->produk()->first()->nama_produk . ' ' . number_format($file_lo->jumlah, 2, ".", ",") . ' ' . $file_lo->satuan_jumlah . ' ' . '('.$file_lo->detail_rencana_pembelian()->first()->rencana_pembelian()->first()->kode_pembelian . ')' }}" />
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_faktur" class="col-md-4 col-form-label text-md-end">{{ __('No Faktur')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_faktur" type="text" class="form-control" readonly name="no_faktur" value="{{ $file_lo->no_faktur }}" />
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_lo" class="col-md-4 col-form-label text-md-end">{{ __('No LO')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_lo" type="text" class="form-control" name="no_lo" readonly value="{{ $file_lo->no_lo }}" />
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="gambar" class="col-md-4 col-form-label text-md-end">{{
                                        __('File')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="custom-file">
                                            <input type="file" name="gambar" disabled class="custom-file-input" id="gambar">
                                            <label class="custom-file-label" for="gambar">Pilih File</label>
                                        </div>

                                        <img src="{{ asset('storage/file_lo/' . $file_lo->nama_file) }}" alt="{{ $file_lo->nama_dokumen }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto">
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('ms2.pengiriman.show', $pengiriman->pengiriman_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <a type="button" href="{{ route('ms2.pengiriman.file_lo_show.lampiran.index', [$pengiriman->pengiriman_id, $file_lo->file_lo_id]) }}" class="btn btn-info mr-2"><i class="fas fa-file"></i> Lampiran File Lo</a>
                                        <form action="{{ route('ms2.pengiriman.file_lo_delete',[$pengiriman->pengiriman_id, $file_lo->file_lo_id]) }}" method="POST">@csrf @method('delete')
                                            <button type="submit" onclick="return confirm('Produk akan dikeluarkan dari pengiriman!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
