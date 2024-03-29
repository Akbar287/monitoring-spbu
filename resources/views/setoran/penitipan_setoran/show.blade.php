@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Penitipan Setoran</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.penitipan') }}">Penitipan Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.penitipan.show', $penjualan->penjualan_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.penitipan.index_penitipan', $penjualan->penjualan_id) }}">List</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Penitipan Setoran</h2>
    <p class="section-lead">
        Detail data Penitipan Setoran anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Penitipan Setoran') }}</h4>
                    <div class="card-header-action">
                        @if($penitipan->is_accepted)
                        <div class="badge badge-success">Konfirmasi</div>
                        @else
                        <div class="badge badge-danger">Tidak Konfirmasi</div>
                        @endif
                        <a href="{{ route('setoran.penitipan.file.index', [$penjualan->penjualan_id, $penitipan->penarikan_id]) }}" class="btn btn-primary">File</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Dititipkan Oleh')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="member_id" type="text" class="form-control" name="member_id" value="{{ $penitipan->member()->first()->ktp()->first()->nama }}" readonly />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="date" class="form-control" name="tanggal" value="{{ $penitipan->tanggal }}" readonly />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_bank" class="col-md-4 col-form-label text-md-end">{{ __('Nama Bank')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_bank" type="text" class="form-control" name="nama_bank" value="{{ $penitipan->nama_bank }}" readonly />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_referensi" class="col-md-4 col-form-label text-md-end">{{ __('No. Referensi')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_referensi" type="text" class="form-control" name="no_referensi" value="{{ $penitipan->no_referensi }}" readonly />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="metode_transfer" class="col-md-4 col-form-label text-md-end">{{ __('Metode Transfer')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="metode_transfer" type="text" class="form-control" name="metode_transfer" value="{{ $penitipan->metode_transfer }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="total_uang" class="col-md-4 col-form-label text-md-end">{{ __('Total Uang')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Rp
                                            </div>
                                        </div>
                                        <input id="total_uang" type="text" class="form-control thousand-style" name="total_uang" value="{{ $penitipan->total_uang }}" readonly/>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-8 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('setoran.penitipan.index_penitipan', $penjualan->penjualan_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('setoran.penitipan.edit', [$penjualan->penjualan_id, $penitipan->penarikan_id]) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    @if(Auth::user()->member()->first()->jabatan()->first()->nama_jabatan == 'Admin')
                                    <form action="{{ route('setoran.penitipan.update_confirmation', [$penjualan->penjualan_id, $penitipan->penarikan_id]) }}" method="POST">@csrf @method('put')
                                        @if($penitipan->is_accepted)
                                        <button type="submit" onclick="return confirm('Penitipan Setoran akan di batalkan konfirmasi!\n Lanjutkan?')" title="Batalkan Konfirmasi Data" class="btn btn-danger mr-2"><i class="fas fa-times"></i> Batalkan Konfirmasi</button>
                                        @else
                                        <button type="submit" onclick="return confirm('Penitipan Setoran akan di konfirmasi!\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-info mr-2"><i class="fas fa-check"></i> Konfirmasi</button>
                                        @endif
                                    </form>
                                    @endif
                                    <form action="{{ route('setoran.penitipan.delete', [$penjualan->penjualan_id, $penitipan->penarikan_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Penitipan Setoran akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
