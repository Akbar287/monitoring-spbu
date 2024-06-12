@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Pengiriman</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.produk.index') }}">MS2</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman') }}">Pengiriman MS2</a></div>
        <div class="breadcrumb-item">Detail Pengiriman</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Pengiriman</h2>
    <p class="section-lead">
        Detail Pengiriman anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Pengiriman') }}</h4>
                    <div class="card-header-action">
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        @if($ms2->is_done)
                        <div class="col-md-12">
                            <div class="alert alert-success">MS2 ini sudah di pengiriman. <a href="{{ route('pengiriman.show', $ms2->pengiriman()->first()->pengiriman_id) }}">Klik Disini</a> untuk ke halaman pengiriman</div>
                        </div>
                        @endif
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Yang Menerima') }}</label>

                                <div class="col-md-6">
                                    <input id="member_id" type="text" readonly class="form-control" name="member_id" value="{{ $ms2->member()->first()->ktp()->first()->nama }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="text" readonly class="form-control" name="tanggal" value="{{ Carbon\Carbon::createFromFormat('Y-m-d', $ms2->tanggal)->format('d F Y') }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="kode_sms" class="col-md-4 col-form-label text-md-end">{{ __('Kode SMS')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea name="kode_sms" id="kode_sms" class="form-control" readonly cols="30" rows="10">
                                        {{ $ms2->kode_sms }}
                                    </textarea>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="produk" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <th>No</th>
                                                <th>Nama Produk</th>
                                                <th>Kuantitas</th>
                                                <th>Kode Pembelian</th>
                                            </thead>
                                            <tbody>
                                                @foreach($ms2->detail_rencana_pembelian_ms2()->select('detail_rencana_pembelian.jumlah', 'detail_rencana_pembelian.satuan_jumlah', 'rencana_pembelian.kode_pembelian', 'produk.nama_produk', 'detail_rencana_pembelian_ms2.is_delivery')->join('detail_rencana_pembelian', 'detail_rencana_pembelian.detail_rencana_pembelian_id', 'detail_rencana_pembelian_ms2.detail_rencana_pembelian_id')->join('produk', 'produk.produk_id', 'detail_rencana_pembelian.produk_id')->join('rencana_pembelian', 'detail_rencana_pembelian.rencana_pembelian_id', 'rencana_pembelian.rencana_pembelian_id')->get() as $p)
                                                <tr>
                                                    <td>@if($ms2->is_done) @if(!$p->is_delivery) NO @else OK @endif @else {{$loop->iteration}} @endif</td>
                                                    <td>{{ $p->nama_produk }}</td>
                                                    <td>{{ $p->jumlah . ' ('. $p->satuan_jumlah .')' }}</td>
                                                    <td>{{ $p->kode_pembelian }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-0 justify-content-center">
                                @if(!$ms2->is_done)
                                <div class="col-12 col-md-10 col-lg-8">
                                    <div class="alert alert-info">Jika ODI sudah muncul, silakan di klik tombol Lanjutkan ke pengiriman untuk mengisi informasi pengiriman</div>
                                </div>
                                @endif
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('ms2.pengiriman') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>

                                    @if(!$ms2->is_done)
                                    <a type="button" href="{{ route('ms2.pengiriman.konfirm_pengiriman', $ms2->ms2_id) }}" class="btn btn-success mr-2"><i class="fas fa-arrow-right"></i> Lanjutkan ke Pengiriman</a>
                                    @endif

                                    @if(!$ms2->is_done)
                                    <form action="{{ route('ms2.pengiriman.delete',$ms2->ms2_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Ms2 akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                    </form>
                                    @endif
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
