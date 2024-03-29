@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Open Penjualan</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open') }}">Open Penjualan</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Open Penjualan</h2>
    <p class="section-lead">
        Detail data Open Penjualan anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Open Penjualan') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('setoran.open.detail', $penjualan->penjualan_id) }}" class="btn btn-primary">
                            Detail
                        </a>
                    </div>
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
                                    <a type="button" href="{{ route('setoran.open') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('setoran.open.edit', $penjualan->penjualan_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    @if($penjualan->status_setoran()->first()->nama_status == 'Open Penjualan')
                                    <form action="{{ route('setoran.open.update.status', $penjualan->penjualan_id) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Open Penjualan akan dipindahkan ke Penitipan Setoran!\nPastikan Uang yang terinput sudah sesuai.\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-info mr-2"><i class="fas fa-arrow-right"></i> Konfirmasi</button>
                                    </form>
                                    @else
                                    <form action="{{ route('setoran.open.update.status', $penjualan->penjualan_id) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Open Penjualan akan dipindahkan kembali ke Open Penjualan!.\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-danger mr-2"><i class="fas fa-arrow-left"></i> Batalkan Konfirmasi</button>
                                    </form>
                                    @endif
                                    <form action="{{ route('setoran.open.delete', $penjualan->penjualan_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Open Penjualan akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
