@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Rencana Pembayaran</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Rencana Pembayaran</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Rencana Pembayaran</h2>
    <p class="section-lead">
        Detail data Rencana Pembayaran anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Rencana Pembelian') }}</h4>
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
                                        value="{{ $rencana->rencana_pembelian_id }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_spbu" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="nama_spbu" type="text"
                                        class="form-control @error('nama_spbu') is-invalid @enderror" name="nama_spbu"
                                        value="{{ $rencana->spbu()->first()->nama_spbu }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="">{{ $rencana->keterangan }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="grand_total" class="col-md-4 col-form-label text-md-end">{{ __('Grand Total (Sblm Pajak)')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="grand_total" type="text" readonly
                                        class="form-control @error('grand_total') is-invalid @enderror" name="grand_total"
                                        value="{{ 'Rp. ' . number_format($rencana->grand_total, 6, '.', ',') }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="grand_total" class="col-md-4 col-form-label text-md-end">{{ __('Grand Total (Stlh Pajak)')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="grand_total" type="text" readonly class="form-control @error('grand_total') is-invalid @enderror" name="grand_total" value="{{ $rencana->pajak_pembelian()->count() > 0 ? 'Rp. '. number_format($rencana->pajak_pembelian()->first()->total_amount, 6, ".", ",")  : 'Rp. ' . number_format($rencana->grand_total + ($rencana->grand_total * ($pajak->ppn / 100)) + ($rencana->grand_total * ($pajak->ppbkb / 100)) + ($rencana->grand_total * ($pajak->pph / 100)), 6, '.', ',') }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pembayaran') }}</h4>
                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                    <div class="card-header-action">
                        <a href="{{ route('pembayaran.create', $rencana->rencana_pembelian_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-pembayaran-show">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nomor Rekening</th>
                                    <th>Nama Rekening</th>
                                    <th>Total Bayar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pajak Pembelian') }}</h4>
                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                    <div class="card-header-action">
                        <a href="{{ route('pembayaran.pajak_edit', $rencana->rencana_pembelian_id) }}" class="btn btn-primary">
                            <i class="fas fa-pen"></i> Ubah
                        </a>
                    </div>
                    @endif
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
                                <input type="text" id="net_price" readonly class="form-control thousand-style @error('net_price') is-invalid @enderror" name="net_price" value="{{ $rencana->pajak_pembelian()->first()->net_price ?? $rencana->grand_total }}" />
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
                                <input type="text" id="ppn" readonly class="form-control thousand-style @error('ppn') is-invalid @enderror" name="ppn" value="{{ $rencana->pajak_pembelian()->first()->ppn ?? 0 }}" />
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
                                <input type="text" id="ppbkb" readonly class="form-control thousand-style @error('ppbkb') is-invalid @enderror" name="ppbkb" value="{{ $rencana->pajak_pembelian()->first()->ppbkb ?? 0 }}" />
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
                                <input type="text" id="pph" readonly class="form-control thousand-style @error('pph') is-invalid @enderror" name="pph" value="{{ $rencana->pajak_pembelian()->first()->pph ?? 0 }}" />
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
                                <input type="text" id="total_amount" readonly class="form-control thousand-style @error('total_amount') is-invalid @enderror" name="total_amount" value="{{ $rencana->pajak_pembelian()->first()->total_amount ?? $rencana->grand_total }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($rencana->is_confirmation == true)
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Konfirmasi Rencana Pembelian') }}</h4>
                </div>
                <div class="card-body">
                            <div class="row mb-3 justify-content-center">
                                <label for="jenis" class="col-md-4 col-form-label text-md-end">{{ __('Jenis Konfirmasi')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="selectgroup w-100">
                                        @if(!$rencana->is_denied)
                                        <label class="selectgroup-item">
                                            <input type="radio" name="jenis" value="true" class="selectgroup-input">
                                            <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-check"></i></span>
                                        </label>
                                        @else
                                        <label class="selectgroup-item">
                                            <input type="radio" name="jenis" value="false" class="selectgroup-input" checked="">
                                            <span class="selectgroup-button selectgroup-button-icon"><i class="fas fa-times"></i></span>
                                        </label>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Catatan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="">{{ $rencana->keterangan }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Produk') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-0">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-stripped">
                                    <thead>
                                        <tr>
                                            <th>Nama produk</th>
                                            <th>Rit</th>
                                            <th>Quantity</th>
                                            <th>Satuan</th>
                                            <th>Harga</th>
                                            <th>Total Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rencana->detail_rencana_pembelian()->get() as $p)
                                        <tr>
                                            <td>
                                                <input id="nama_produk" type="text" readonly class="form-control" name="nama_produk[]" value="{{ $p->produk()->first()->nama_produk }}">
                                            </td>
                                            <td>
                                                <input id="jumlah" type="text" min="0" readonly
                                                    class="form-control" name="jumlah[]"
                                                    value="{{number_format($p->rit, 6, ".", ',') }}" autocomplete="name"
                                                    autofocus>
                                            </td>
                                            <td>
                                                <input id="jumlah" type="text" min="0" readonly
                                                    class="form-control" name="jumlah[]"
                                                    value="{{number_format($p->jumlah, 6, ".", ',') }}" autocomplete="name"
                                                    autofocus>
                                            </td>
                                            <td>
                                                <input id="satuan_jumlah" type="text" min="0" readonly
                                                    class="form-control" name="satuan_jumlah[]"
                                                    value="{{ $p->satuan_jumlah }}" autocomplete="name"
                                                    autofocus>
                                            </td>
                                            <td>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <div class="input-group-text">Rp. </div>
                                                        </div>
                                                        <input id="harga" type="text" min="0" readonly class="form-control" name="harga[]" value="{{number_format($p->harga, 6, ".", ',') }}" autocomplete="name" autofocus>
                                                    </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">Rp. </div>
                                                    </div>
                                                    <input id="total_harga" type="text" min="0" readonly class="form-control" name="total_harga[]" value="{{number_format($p->total_harga, 6, ".", ',') }}" autocomplete="name" autofocus>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
                            <a type="button" href="{{ route('pembayaran.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                            @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                            @if($continue)
                            <form action="{{ route('pembayaran.delivery', $rencana->rencana_pembelian_id) }}" method="POST">@csrf @method('put')
                                <button type="submit" href="{{ route('pembayaran.delivery', $rencana->rencana_pembelian_id) }}"  onClick="return confirm('Sudah Bayar? Ingin meneruskan rencana ini ke tahap MS2 ?')" class="btn mx-2 btn-info"><i class="fas fa-check"></i> Konfirmasi Pembayaran</button>
                            </form>
                            @else
                            <button type="button" class="btn mx-2 btn-secondary" disabled><i class="fas fa-check"></i> Konfirmasi Pembayaran</button>
                            @endif
                            <form action="{{ route('pembayaran.ditolak', $rencana->rencana_pembelian_id) }}" method="POST">@csrf @method('put')
                                <button type="submit" title="Batalkan Pembayaran" onClick="return confirm('Anda yakin membatalkan pembayaran untuk rencana pembelian ini?')" class="btn mx-2 btn-danger"><i class="fas fa-times"></i> Batalkan Konfirmasi Rencana</button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
