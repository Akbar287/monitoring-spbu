@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail File LO</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.rencana.index') }}">Rencana Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.rencana.show', $rencana->rencana_pembelian_id) }}">Detail Rencana</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.index', $rencana->rencana_pembelian_id) }}">Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.show', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id]) }}">Detail Pengiriman</a></div>
        <div class="breadcrumb-item"><a href="{{ route('file_lo.index', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id]) }}">File LO</a></div>
        <div class="breadcrumb-item">Detail File LO</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail File LO</h2>
    <p class="section-lead">
        Detail File LO anda.
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
                                <label for="detail_rencana_pembelian_id" class="col-md-4 col-form-label text-md-end">{{ __('Detail Rencana Pembelian')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="detail_rencana_pembelian_id" type="text"
                                        class="form-control @error('detail_rencana_pembelian_id') is-invalid @enderror" name="detail_rencana_pembelian_id"
                                        value="{{ $file_lo->produk()->first()->nama_produk . ' (' . number_format($file_lo->jumlah, 6, ",", ".") . ' ' . $file_lo->satuan_jumlah .')' }}" >
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
                                        <input id="jumlah" type="text" readonly class="form-control thousand-style" name="jumlah" value="{{ $file_lo->jumlah }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_faktur" class="col-md-4 col-form-label text-md-end">{{ __('No. Faktur')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_faktur" type="text" readonly
                                        class="form-control @error('no_faktur') is-invalid @enderror" name="no_faktur"
                                        value="{{ $file_lo->no_faktur }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_lo" class="col-md-4 col-form-label text-md-end">{{ __('No. LO')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_lo" type="text" readonly
                                        class="form-control @error('no_lo') is-invalid @enderror" name="no_lo"
                                        value="{{ $file_lo->no_lo }}">
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
                                    <a type="button" href="{{ route('file_lo.index', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id]) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('file_lo.edit', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id, $file_lo->file_lo_id]) }}" class="btn mr-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <a type="button" target="_blank" href="{{ route('file_lo.download', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id, $file_lo->file_lo_id]) }}" class="btn mr-2 btn-info"><i class="fas fa-pen"></i> Unduh File</a>
                                    <form action="{{ route('file_lo.destroy', [$rencana->rencana_pembelian_id, $pengiriman->pengiriman_id, $file_lo->file_lo_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('File LO akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
