@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Rencana Pembelian</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.index') }}">Rencana Pembelian</a></div>
        <div class="breadcrumb-item"><a href="{{ route('perencanaan.ditolak') }}">Riwayat</a></div>
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
                    <div class="card-header-action">
                        <div class="badge {{ $rencana->is_denied ? 'badge-danger' : 'badge-success'}}">
                            {{ $rencana->is_denied ? 'Ditolak' : 'Diterima'}}
                        </div>
                    </div>
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
                                        value="{{ $rencana->rencana_pembelian_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_spbu" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="nama_spbu" type="text"
                                        class="form-control @error('nama_spbu') is-invalid @enderror" name="nama_spbu"
                                        value="{{ $rencana->spbu()->first()->nama_spbu }}" autocomplete="name"
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
                                        autofocus>{{ $rencana->keterangan }}</textarea>
                                </div>
                            </div>

                            @if($rencana->detail_rencana_pembelian()->count() > 0)
                            <div class="row mb-0">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-stripped">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        Nama produk
                                                    </th>
                                                    <th>
                                                        Quantity
                                                    </th>
                                                    <th>
                                                        Satuan
                                                    </th>
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
                                                            value="{{number_format($p->jumlah, 6, ".", ',') }}" autocomplete="name"
                                                            autofocus>
                                                    </td>
                                                    <td>
                                                        <input id="satuan_jumlah" type="text" min="0" readonly
                                                            class="form-control" name="satuan_jumlah[]"
                                                            value="{{ $p->satuan_jumlah }}" autocomplete="name"
                                                            autofocus>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <p>Belum ada Produk yang direncanakan untuk dibeli. Tambah dan atur produk dengan klik di kanan atas atau klik <a href="{{ route('perencanaan.detail.index', $rencana->rencana_pembelian_id) }}">Atur Produk</a></p>
                                </div>
                            </div>
                            @endif

                            @if($rencana->is_confirmation == true)
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

                                    @error('jenis')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Catatan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="" autocomplete="name"
                                        autofocus>{{ $rencana->keterangan_denied }}</textarea>

                                    @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            @endif

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('perencanaan.ditolak') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
