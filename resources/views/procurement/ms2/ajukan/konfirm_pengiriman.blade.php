@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Konfirmasi Pengiriman</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.produk.index') }}">MS2</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman') }}">Pengiriman MS2</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman.show', $ms2->ms2_id) }}">Detail Pengiriman</a></div>
        <div class="breadcrumb-item">Konfirmasi Pengiriman</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Konfirmasi Pengiriman</h2>
    <p class="section-lead">
        Konfirmasi Pengiriman anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Konfirmasi Pengiriman') }}</h4>
                    <div class="card-header-action">
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('ms2.pengiriman.update_pengiriman_to_delivery', $ms2->ms2_id) }}" method="post">
                        <div class="row justify-content-center">
                            <div class="col-12 col-sm-12 col-md-10">
                                <div class="alert alert-info">Silakan isi form yang masih kosong</div>
                            </div>
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
                                        <input id="tanggal" type="date" class="form-control" name="tanggal" value="{{ $ms2->tanggal }}" />
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_do" class="col-md-4 col-form-label @error('no_do') is-invalid @enderror text-md-end">{{ __('Nomor DO') }}</label>

                                    <div class="col-md-6">
                                        <input id="no_do" type="text" class="form-control" name="no_do" value="{{ old('no_do') }}" />

                                        @error('no_do')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_pol" class="col-md-4 col-form-label text-md-end">{{ __('Nomor Polisi') }}</label>

                                    <div class="col-md-6">
                                        <input id="no_pol" type="text" class="form-control @error('no_pol') is-invalid @enderror" name="no_pol" value="{{ old('no_pol') }}" />

                                        @error('no_pol')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="catatan" class="col-md-4 col-form-label text-md-end">{{ __('Catatan') }}</label>

                                    <div class="col-md-6">
                                        <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror" id="catatan" cols="30" rows="10">{{ old('catatan') }}</textarea>
                                        @error('catatan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="produk" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <th>Aksi</th>
                                                    <th>Nama Produk</th>
                                                    <th>Kuantitas</th>
                                                    <th>Kode Pembelian</th>
                                                </thead>
                                                <tbody>
                                                    @foreach($ms2->detail_rencana_pembelian_ms2()->select('detail_rencana_pembelian.detail_rencana_pembelian_id', 'detail_rencana_pembelian.jumlah', 'detail_rencana_pembelian.satuan_jumlah', 'rencana_pembelian.kode_pembelian', 'produk.nama_produk')->join('detail_rencana_pembelian', 'detail_rencana_pembelian.detail_rencana_pembelian_id', 'detail_rencana_pembelian_ms2.detail_rencana_pembelian_id')->join('produk', 'produk.produk_id', 'detail_rencana_pembelian.produk_id')->join('rencana_pembelian', 'detail_rencana_pembelian.rencana_pembelian_id', 'rencana_pembelian.rencana_pembelian_id')->get() as $p)
                                                    <tr>
                                                        <td>
                                                            <button class="btn btn-sm btn-danger btn-rounded btn-minus-produk" type="button"><i class="fas fa fa-minus"></i></button>
                                                            <button class="btn btn-sm btn-success btn-rounded d-none btn-plus-produk" data-id="{{ $p->detail_rencana_pembelian_id }}" type="button"><i class="fas fa fa-plus"></i></button>
                                                        </td>
                                                        <input type="hidden" name="detail_rencana_pembelian_id[]" value="{{ $p->detail_rencana_pembelian_id }}">
                                                        <td>{{ $p->nama_produk }}</td>
                                                        <td>{{ $p->jumlah . ' ('. $p->satuan_jumlah .')' }}</td>
                                                        <td>{{ $p->kode_pembelian }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="alert alert-danger">Pastikan produk terpilih sudah terdapat File LO</div>
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('ms2.pengiriman.show', $ms2->ms2_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>

                                        @if(!$ms2->is_done)
                                        <form action="{{ route('ms2.pengiriman.update_pengiriman_to_delivery', $ms2->ms2_id) }}" method="POST">@csrf @method('put')
                                            <button type="submit" onclick="return confirm('MS2 akan dikonfirmasi!\nPindahkan ke Pengiriman Delivery? \nPastikan Produk yang dikirim sudah sesuai!')" title="Simpan Data dan lanjutkan ke halaman upload" class="btn btn-success mr-2"><i class="fas fa-check"></i> Simpan</button>
                                        </form>
                                        @endif
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
