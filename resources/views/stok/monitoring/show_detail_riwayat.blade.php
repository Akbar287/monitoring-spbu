@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Riwayat Monitoring Stok</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.index') }}">Monitoring Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.show', $stok->stok_inventory_id) }}">Detail Monitoring Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.show_riwayat', $stok->stok_inventory_id) }}">Riwayat Monitoring Stok</a></div>
        <div class="breadcrumb-item">Detail Riwayat Monitoring Stok</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Riwayat Monitoring Stok</h2>
    <p class="section-lead">
        Detail Riwayat Monitoring Stok.
    </p>
    <div class="row justify-content-center">
        <div class="col-12">
            @if(session('msg')){!! session('msg') !!} @endif
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Rincian Dokumen Stok') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="dokumen_stok_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Dokumen Stok')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="dokumen_stok_id" type="text" readonly
                                        class="form-control @error('dokumen_stok_id') is-invalid @enderror" name="dokumen_stok_id"
                                        value="{{ $dokumenStok->dokumen_stok_id }}" />
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="nama_movement" class="col-md-4 col-form-label text-md-end">{{ __('Nama Movement')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_movement" type="text" readonly
                                        class="form-control @error('nama_movement') is-invalid @enderror" name="nama_movement"
                                        value="{{ $dokumenStok->type_dokumen_stok()->first()->type_movement }}" />
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="nama_pegawai" class="col-md-4 col-form-label text-md-end">{{ __('Nama Pegawai')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_pegawai" type="text" readonly
                                        class="form-control @error('nama_pegawai') is-invalid @enderror" name="nama_pegawai"
                                        value="{{ $dokumenStok->member()->first()->ktp()->first()->nama }}" />
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="nama_produk" class="col-md-4 col-form-label text-md-end">{{ __('Nama Produk')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_produk" type="text" readonly
                                        class="form-control @error('nama_produk') is-invalid @enderror" name="nama_produk"
                                        value="{{ $dokumenStok->stok_inventory()->first()->produk()->first()->nama_produk }}" />
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="nama_dombak" class="col-md-4 col-form-label text-md-end">{{ __('Nama Dombak')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_dombak" type="text" readonly
                                        class="form-control @error('nama_dombak') is-invalid @enderror" name="nama_dombak"
                                        value="{{ $dokumenStok->dombak()->first()->nama_dombak }}" />
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="text" readonly
                                        class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                        value="{{ Carbon\Carbon::createFromFormat('Y-m-d', $dokumenStok->tanggal)->format('d F Y') }}" />
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="nama_jam_kerja" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_jam_kerja" type="text" readonly
                                        class="form-control @error('nama_jam_kerja') is-invalid @enderror" name="nama_jam_kerja"
                                        value="{{ $dokumenStok->jam_kerja()->first()->nama_jam_kerja }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Rincian Dokumen Stok') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="stok_awal" class="col-md-4 col-form-label text-md-end">{{ __('Stok Awal')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <input id="stok_awal" type="text" readonly
                                            class="form-control @error('stok_awal') is-invalid @enderror" name="stok_awal"
                                            value="{{ $stokHitung['stok_awal'] }}" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">Liter</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="rencana_pembelian_id" class="col-md-4 col-form-label text-md-end">{{ __('Stok Akhir')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <input id="stok_akhir" type="text" readonly
                                            class="form-control @error('stok_akhir') is-invalid @enderror" name="stok_akhir"
                                            value="{{ $stokHitung['stok_akhir'] }}" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">Liter</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="stok_akhir_teoritis" class="col-md-4 col-form-label text-md-end">{{ __('Stok Akhir Teoritis')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <input id="stok_akhir_teoritis" type="text" readonly
                                            class="form-control @error('stok_akhir_teoritis') is-invalid @enderror" name="stok_akhir_teoritis"
                                            value="{{ $stokHitung['stok_akhir_teoritis'] }}" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">Liter</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="stok_pl" class="col-md-4 col-form-label text-md-end">{{ __('Stok Profit/Loss')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{ $stokHitung['stok_akhir_pl'] > 0 ? '+' : '-' }}</span>
                                        </div>
                                        <input id="stok_pl" type="text" readonly
                                            class="form-control @error('stok_pl') is-invalid @enderror" name="stok_pl"
                                            value="{{ $stokHitung['stok_akhir_pl'] }}" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">Liter</span>
                                        </div>
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
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('monitoring.show_riwayat', $stok->stok_inventory_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
