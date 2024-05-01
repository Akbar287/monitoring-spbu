@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Pembelian Produk</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.produk.index') }}">MS2</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.produk.show', $detail->produk_id) }}">Detail MS2</a></div>
        <div class="breadcrumb-item">Detail Pembelian Produk</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Detail Pembelian Produk</h2>
    <p class="section-lead">
        Detail data Detail Pembelian Produk anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Rencana Pembelian Produk') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="rencana_pembelian_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Rencana Pembelian')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="rencana_pembelian_id" type="text" readonly
                                        class="form-control @error('rencana_pembelian_id') is-invalid @enderror" name="rencana_pembelian_id"
                                        value="{{ $detail->rencana_pembelian()->first()->rencana_pembelian_id }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_spbu" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="nama_spbu" type="text"
                                        class="form-control @error('nama_spbu') is-invalid @enderror" name="nama_spbu"
                                        value="{{ $detail->rencana_pembelian()->first()->spbu()->first()->nama_spbu }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="" >{{ $detail->rencana_pembelian()->first()->keterangan }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="grand_total" class="col-md-4 col-form-label text-md-end">{{ __('Grand Total')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="grand_total" type="text" readonly
                                        class="form-control @error('grand_total') is-invalid @enderror" name="grand_total"
                                        value="{{ 'Rp. ' . number_format($detail->rencana_pembelian()->first()->grand_total, 6, '.', ',') }}"  />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pembayaran') }}</h4>
                </div>
                <div class="card-body">
                    @foreach($detail->rencana_pembelian()->first()->pembayaran()->get() as $p)
                    <h2 class="section-title" style="margin-top: -10px;">Pembayaran #{{ $loop->iteration }}</h2>
                    <div class="row mb-3 justify-content-center">
                        <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Penyetor')
                            }}</label>

                        <div class="col-md-6">
                            <input id="member_id" type="text" readonly
                                class="form-control @error('member_id') is-invalid @enderror" name="member_id"
                                value="{{ $p->member()->first()->ktp()->first()->nama }}"/>
                        </div>
                    </div>

                    <div class="row mb-3 justify-content-center">
                        <label for="no_cek_bg" class="col-md-4 col-form-label text-md-end">{{ __('No Cek BG')
                            }}</label>

                        <div class="col-md-6">
                            <input id="no_cek_bg" type="text" readonly
                                class="form-control @error('no_cek_bg') is-invalid @enderror" name="no_cek_bg"
                                value="{{ $p->no_cek_bg }}"/>
                        </div>
                    </div>

                    <div class="row mb-3 justify-content-center">
                        <label for="no_rekening" class="col-md-4 col-form-label text-md-end">{{ __('No Rekening')
                            }}</label>

                        <div class="col-md-6">
                            <input id="no_rekening" type="text" readonly
                                class="form-control @error('no_rekening') is-invalid @enderror" name="no_rekening"
                                value="{{ $p->no_rekening }}"/>
                        </div>
                    </div>

                    <div class="row mb-3 justify-content-center">
                        <label for="nama_rekening" class="col-md-4 col-form-label text-md-end">{{ __('Nama Rekening')
                            }}</label>

                        <div class="col-md-6">
                            <input id="nama_rekening" type="text" readonly
                                class="form-control @error('nama_rekening') is-invalid @enderror" name="nama_rekening"
                                value="{{ $p->nama_rekening }}"/>
                        </div>
                    </div>

                    <div class="row mb-3 justify-content-center">
                        <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Bayar')
                            }}</label>

                        <div class="col-md-6">
                            <input id="tanggal" type="date" readonly
                                class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                value="{{ $p->tanggal }}"/>
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
                                <input id="total_setoran" type="text" readonly
                                    class="form-control @error('total_setoran') is-invalid @enderror" name="total_setoran"
                                    value="{{ number_format($p->total_setoran, 2, ".", ",") }}" />
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pajak Pembelian') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3 justify-content-center">
                        <label for="net_price" class="col-md-4 col-form-label text-md-end">{{ __('Net Price')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" id="net_price" readonly class="form-control thousand-style @error('net_price') is-invalid @enderror" name="net_price" value="{{ $detail->rencana_pembelian()->first()->pajak_pembelian()->first()->net_price ?? $detail->rencana_pembelian()->first()->grand_total }}" />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-center">
                        <label for="ppn" class="col-md-4 col-form-label text-md-end">{{ __('PPn')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" id="ppn" readonly class="form-control thousand-style @error('ppn') is-invalid @enderror" name="ppn" value="{{ $detail->rencana_pembelian()->first()->pajak_pembelian()->first()->ppn ?? 0 }}" />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-center">
                        <label for="ppbkb" class="col-md-4 col-form-label text-md-end">{{ __('PPBKB')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" id="ppbkb" readonly class="form-control thousand-style @error('ppbkb') is-invalid @enderror" name="ppbkb" value="{{ $detail->rencana_pembelian()->first()->pajak_pembelian()->first()->ppbkb ?? 0 }}" />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-center">
                        <label for="pph" class="col-md-4 col-form-label text-md-end">{{ __('PPh')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" id="pph" readonly class="form-control thousand-style @error('pph') is-invalid @enderror" name="pph" value="{{ $detail->rencana_pembelian()->first()->pajak_pembelian()->first()->pph ?? 0 }}" />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-center">
                        <label for="total_amount" class="col-md-4 col-form-label text-md-end">{{ __('Total Amount (Gross Price)')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" id="total_amount" readonly class="form-control thousand-style @error('total_amount') is-invalid @enderror" name="total_amount" value="{{ $detail->rencana_pembelian()->first()->pajak_pembelian()->first()->total_amount ?? $detail->rencana_pembelian()->first()->grand_total }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Produk') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-0">
                        <div class="col-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="produk_id" type="text" readonly
                                        class="form-control" name="produk_id"
                                        value="{{ $detail->produk()->first()->nama_produk }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="rit" class="col-md-4 col-form-label text-md-end">{{ __('RIT')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="rit" type="text" readonly
                                        class="form-control @error('rit') is-invalid @enderror" name="rit"
                                        value="{{ number_format($detail->rit, 2, ".", ',') }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Jumlah / Quantity')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="jumlah" type="text" readonly
                                        class="form-control @error('jumlah') is-invalid @enderror" name="jumlah"
                                        value="{{ number_format($detail->jumlah, 2, ".", ',') }}" autocomplete="name"
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
                                            value="{{ number_format($detail->harga, 2, ".", ",") }}" autocomplete="name"
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
                                            value="{{ number_format($detail->total_harga, 2, ".", ",") }}" autocomplete="name"
                                            autofocus>

                                      </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pilihan') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4 d-flex align-items-end">
                            <a type="button" href="{{ route('ms2.produk.show', $detail->produk_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
