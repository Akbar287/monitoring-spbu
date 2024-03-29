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
                        @if($pengiriman->is_confirmation_admin)
                        <div class="badge badge-success">Terkonfirmasi</div>
                        @else
                        <div class="badge badge-danger">Belum Dikonfirmasi</div>
                        @endif
                        @if($pengiriman->is_done)
                        <div class="badge badge-success">Selesai</div>
                        @else
                        <div class="badge badge-danger">Belum Selesai</div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Yang Menerima')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="member_id" type="text" readonly
                                        class="form-control" name="member_id"
                                        value="{{ $pengiriman->member()->first()->ktp()->first()->nama }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_do" class="col-md-4 col-form-label text-md-end">{{ __('No DO')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_do" type="text"
                                        class="form-control" name="no_do" readonly
                                        value="{{ $pengiriman->no_do }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_pol" class="col-md-4 col-form-label text-md-end">{{ __('No Pol')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_pol" type="text" readonly
                                        class="form-control" name="no_pol"
                                        value="{{ $pengiriman->no_pol }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control" name="tanggal"
                                        value="{{ $pengiriman->tanggal }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>


                            <div class="row mb-3 justify-content-center">
                                <label for="catatan" class="col-md-4 col-form-label text-md-end">{{ __('Catatan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="catatan" readonly class="form-control @error('catatan') is-invalid @enderror" name="catatan" cols="30" rows="10">{{ $pengiriman->catatan }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-12">
                                    @if(!$pengiriman->is_confirmation_admin && !$pengiriman->is_done)
                                    <div class="alert alert-warning">Konfirmasi untuk mengubah status dalam pengiriman.</div>
                                    @endif
                                    @if($pengiriman->is_confirmation_admin && !$pengiriman->is_done)
                                    <div class="alert alert-warning">Tandai Selesai untuk mengubah status Sudah Diterima.</div>
                                    @endif
                                </div>
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('ms2.pengiriman') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('ms2.pengiriman.edit', $pengiriman->pengiriman_id) }}" class="btn mr-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    @if(!$pengiriman->is_confirmation_admin)
                                    <form action="{{ route('ms2.pengiriman.update_pengiriman_to_delivery', $pengiriman->pengiriman_id) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('MS2 akan dikonfirmasi!\nPindahkan ke Pengiriman Delivery? \nPastikan Produk yang dikirim sudah sesuai!')" title="Konfirmasi Data" class="btn btn-info mr-2"><i class="fas fa-check"></i> Konfirmasi</button>
                                    </form>
                                    @endif
                                    <form action="{{ route('ms2.pengiriman.delete',$pengiriman->pengiriman_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Ms2 akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Pengiriman Produk MS2') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('ms2.pengiriman.file_lo_create', $pengiriman->pengiriman_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-produk-detail-ms2">
                                    <thead>
                                        <tr>
                                            <th>No. LO</th>
                                            <th>Tanggal Pembelian</th>
                                            <th>Kode Pembelian</th>
                                            <th>Produk</th>
                                            <th>Stok</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
