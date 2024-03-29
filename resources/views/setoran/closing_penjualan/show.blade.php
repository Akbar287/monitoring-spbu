@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Closing Penjualan</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.closing') }}">Closing Penjualan</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Closing Penjualan</h2>
    <p class="section-lead">
        Detail data Closing Penjualan anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Closing Penjualan') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="dispenser_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="spbu_id" type="text"
                                        class="form-control @error('spbu_id') is-invalid @enderror" name="spbu_id"
                                        value="{{ $penjualan->spbu()->first()->nama_spbu }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="produk_id" type="text"
                                        class="form-control @error('produk_id') is-invalid @enderror" name="produk_id"
                                        value="{{ $penjualan->produk()->first()->nama_produk }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="dispenser_id" class="col-md-4 col-form-label text-md-end">{{ __('Dispenser')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="dispenser_id" type="text"
                                        class="form-control @error('dispenser_id') is-invalid @enderror" name="dispenser_id"
                                        value="{{ $penjualan->dispenser()->first()->nama_dispenser }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jam_kerja_id" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="jam_kerja_id" type="text"  readonly
                                        class="form-control @error('jam_kerja_id') is-invalid @enderror" name="jam_kerja_id"
                                        value="{{ $penjualan->jam_kerja()->first()->nama_jam_kerja }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                        value="{{ $penjualan->tanggal }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="total_uang_kotak" class="col-md-4 col-form-label text-md-end">{{ __('Total Uang Kotak')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Rp
                                            </div>
                                        </div>
                                        <input id="total_uang_kotak" type="text" readonly
                                            class="form-control thousand-style @error('total_uang_kotak') is-invalid @enderror" name="total_uang_kotak"
                                            value="{{ $penjualan->total_uang_kotak }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="total_penjualan" class="col-md-4 col-form-label text-md-end">{{ __('Total Penjualan')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Rp
                                            </div>
                                        </div>
                                        <input id="total_penjualan" type="text" readonly
                                            class="form-control thousand-style @error('total_penjualan') is-invalid @enderror" name="total_penjualan"
                                            value="{{ $penjualan->total_penjualan }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-8 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('setoran.closing') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->divisi()->first()->nama_divisi == 'Top Manajemen')
                                    <form action="{{ route('setoran.closing.update', $penjualan->penjualan_id) }}" method="POST">@csrf @method('put')
                                        <input type="hidden" name="type" value="top">
                                        <button type="submit" onclick="return confirm('Penjualan akan {{ $penjualan->is_accepted_top ? 'Tidak Dikonfirmasi' : 'dikonfirmasi'}}!\n Lanjutkan?')" title="Konfirmasi Data" class="btn {{ $penjualan->is_accepted_top ? 'btn-danger' : 'btn-success'}}"><i class="fas {{ $penjualan->is_accepted_top ? 'fa-times' : 'fa-check'}}"></i> Konfirmasi Top</button>
                                    </form>
                                    @endif
                                    @if(Auth::user()->member()->first()->jabatan()->first()->nama_jabatan == 'Admin')
                                    <form action="{{ route('setoran.closing.update', $penjualan->penjualan_id) }}" method="POST">@csrf @method('put')
                                        <input type="hidden" name="type" value="admin">
                                        <button type="submit" onclick="return confirm('Penjualan akan {{ $penjualan->is_accepted_admin ? 'Tidak Dikonfirmasi' : 'dikonfirmasi'}}!\n Lanjutkan?')" title="Konfirmasi Data" class="btn {{ $penjualan->is_accepted_admin ? 'btn-danger' : 'btn-success'}}"><i class="fas {{ $penjualan->is_accepted_admin ? 'fa-times' : 'fa-check'}}"></i> Konfirmasi Admin</button>
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
