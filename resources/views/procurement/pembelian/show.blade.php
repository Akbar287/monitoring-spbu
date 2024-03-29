@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Rencana Pembelian</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.index') }}">Rencana Pembelian</a></div>
        <div class="breadcrumb-item">Detail</div>
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
                    <h4>{{ __('Detail Rencana Pembelian') }}</h4>
                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                    <div class="card-header-action">
                        <a href="{{ route('pembelian.detail.index', $pembelian->rencana_pembelian_id) }}" class="btn btn-primary">
                            Atur Harga
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="rencana_pembelian_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Rencana Pembelian')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="rencana_pembelian_id" type="text" readonly
                                        class="form-control @error('rencana_pembelian_id') is-invalid @enderror" name="rencana_pembelian_id"
                                        value="{{ $pembelian->rencana_pembelian_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_spbu" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="nama_spbu" type="text"
                                        class="form-control @error('nama_spbu') is-invalid @enderror" name="nama_spbu"
                                        value="{{ $pembelian->spbu()->first()->nama_spbu }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="" autocomplete="name"
                                        autofocus>{{ $pembelian->keterangan }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="grand_total" class="col-md-4 col-form-label text-md-end">{{ __('Grand Total (Sblm Pajak)')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="grand_total" type="text" readonly
                                        class="form-control @error('grand_total') is-invalid @enderror" name="grand_total"
                                        value="{{ 'Rp. ' . number_format($pembelian->grand_total, 2, '.', ',') }}" autocomplete="name"
                                        autofocus />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="grand_total" class="col-md-4 col-form-label text-md-end">{{ __('Grand Total (Stlh Pajak)')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="grand_total" type="text" readonly class="form-control @error('grand_total') is-invalid @enderror" name="grand_total" value="{{ $pembelian->pajak_pembelian()->count() > 0 ? 'Rp. '. number_format($pembelian->pajak_pembelian()->first()->total_amount, 0, ".", ",")  : 'Rp. ' . number_format($pembelian->grand_total + ($pembelian->grand_total * ($pajak->ppn / 100)) + ($pembelian->grand_total * ($pajak->ppbkb / 100)) + ($pembelian->grand_total * ($pajak->pph / 100)), 2, '.', ',') }}" />
                                </div>
                            </div>
                            <h2 class="section-title">Pajak</h2>
                            <p class="section-lead">
                                Pajak dihitung berdasarkan harga total produk sebagai harga dasar
                            </p>
                            <div class="row mb-0">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-stripped">
                                            <thead>
                                                <tr>
                                                    <th>Nama Pajak</th>
                                                    <th>Harga Dasar</th>
                                                    <th>Persen (%)</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!is_null($pajak))
                                                <tr>
                                                    <td>PPN</td>
                                                    <td>{{ 'Rp. '. number_format($pembelian->pajak_pembelian()->count() > 0 ? $pembelian->pajak_pembelian()->first()->net_price : $pembelian->grand_total, 0, ".", ",") }}</td>
                                                    <td>{{ $pajak->ppn }}</td>
                                                    <td>{{ 'Rp. '. number_format($pembelian->pajak_pembelian()->count() > 0 ? $pembelian->pajak_pembelian()->first()->ppn : $pembelian->grand_total * ($pajak->ppn / 100), 0, ".", ",") }}</td>
                                                </tr>
                                                <tr>
                                                    <td>PPPBKB</td>
                                                    <td>{{ 'Rp. '. number_format($pembelian->pajak_pembelian()->count() > 0 ? $pembelian->pajak_pembelian()->first()->net_price : $pembelian->grand_total, 0, ".", ",") }}</td>
                                                    <td>{{ $pajak->ppbkb }}</td>
                                                    <td>{{ 'Rp. '. number_format($pembelian->pajak_pembelian()->count() > 0 ? $pembelian->pajak_pembelian()->first()->ppbkb : $pembelian->grand_total * ($pajak->ppbkb / 100), 0, ".", ",") }}</td>
                                                </tr>
                                                <tr>
                                                    <td>PPH</td>
                                                    <td>{{ 'Rp. '. number_format($pembelian->pajak_pembelian()->count() > 0 ? $pembelian->pajak_pembelian()->first()->net_price : $pembelian->grand_total, 0, ".", ",") }}</td>
                                                    <td>{{ $pajak->pph }}</td>
                                                    <td>{{ 'Rp. '. number_format($pembelian->pajak_pembelian()->count() > 0 ? $pembelian->pajak_pembelian()->first()->pph : $pembelian->grand_total * ($pajak->pph / 100), 0, ".", ",") }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3">Total</td>
                                                    <td colspan="1">{{ 'Rp. '. number_format(
                                                        ($pembelian->pajak_pembelian()->count() > 0 ? $pembelian->pajak_pembelian()->first()->ppn : $pembelian->grand_total * ($pajak->ppn / 100)) +

                                                        ($pembelian->pajak_pembelian()->count() > 0 ? $pembelian->pajak_pembelian()->first()->ppbkb : $pembelian->grand_total * ($pajak->ppbkb / 100)) +

                                                        ($pembelian->pajak_pembelian()->count() > 0 ? $pembelian->pajak_pembelian()->first()->pph : $pembelian->grand_total * ($pajak->pph / 100))

                                                        , 0, ".", ",") }}</td>
                                                </tr>
                                                @else
                                                <tr>
                                                    <td colspan="4">Anda Belum Mengatur Konfigurasi Pajak</td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <h2 class="section-title">Produk</h2>
                            <p class="section-lead">
                                Produk dibawah ini adalah rincian harga dan total harga.
                            </p>
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
                                                @foreach($pembelian->detail_rencana_pembelian()->get() as $p)
                                                <tr>
                                                    <td>
                                                        <input id="nama_produk" type="text" readonly class="form-control" name="nama_produk[]" value="{{ $p->produk()->first()->nama_produk }}">
                                                    </td>
                                                    <td>
                                                        <input id="jumlah" type="text" min="0" readonly
                                                            class="form-control" name="jumlah[]"
                                                            value="{{number_format($p->rit, 2, ".", ',') }}" autocomplete="name"
                                                            autofocus>
                                                    </td>
                                                    <td>
                                                        <input id="jumlah" type="text" min="0" readonly
                                                            class="form-control" name="jumlah[]"
                                                            value="{{number_format($p->jumlah, 2, ".", ',') }}" autocomplete="name"
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
                                                                <input id="harga" type="text" min="0" readonly class="form-control" name="harga[]" value="{{number_format($p->harga, 2, ".", ',') }}" autocomplete="name" autofocus>
                                                            </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <div class="input-group-text">Rp. </div>
                                                            </div>
                                                            <input id="total_harga" type="text" min="0" readonly class="form-control" name="total_harga[]" value="{{number_format($p->total_harga, 2, ".", ',') }}" autocomplete="name" autofocus>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                            @if($pembelian->is_confirmation == true)
                            <div class="row mb-3 justify-content-center">
                                <label for="jenis" class="col-md-4 col-form-label text-md-end">{{ __('Jenis Konfirmasi')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="selectgroup w-100">
                                        @if(!$pembelian->is_denied)
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
                                        value="" autocomplete="name"
                                        autofocus>{{ $pembelian->keterangan_denied }}</textarea>
                                </div>
                            </div>
                            @endif
                            @endif

                            @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">Pastikan Harga Pembelian tidak salah.</div>
                                </div>
                            </div>
                            @endif
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('pembelian.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                                    <a type="button" href="{{ route('pembelian.konfirmasi', $pembelian->rencana_pembelian_id) }}"  onClick="return confirm('Anda yakin ingin meneruskan Rencana Pembelian ini ke tahap pembayaran?')" class="btn mx-2 btn-info"><i class="fas fa-check"></i> Konfirmasi Harga</a>
                                    <a type="button" href="{{ route('pembelian.perencanaan', $pembelian->rencana_pembelian_id) }}"  onClick="return confirm('Anda yakin membatalkan pembelian untuk rencana ini?')" class="btn mx-2 btn-danger"><i class="fas fa-times"></i> Batalkan Pembelian Rencana Ini</a>
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
