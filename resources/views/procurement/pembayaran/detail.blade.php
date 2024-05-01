@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Pembayaran</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Rencana Pembayaran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembayaran.show', $rencana->rencana_pembelian_id) }}">Detail Rencana</a></div>
        <div class="breadcrumb-item">Detail Pembayaran</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Pembayaran</h2>
    <p class="section-lead">
        Detail data Pembayaran anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Pembayaran') }}</h4>
                    <div class="card-header-action">
                        @if($pembayaran->is_confirmation_top)
                        <div class="badge badge-success">Top Manajemen</div>
                        @else
                        <div class="badge badge-danger">Top Manajemen</div>
                        @endif
                        @if($pembayaran->is_confirmation_admin)
                        <div class="badge badge-success">Admin</div>
                        @else
                        <div class="badge badge-danger">Admin</div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Penyetor')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="member_id" type="text" readonly
                                        class="form-control @error('member_id') is-invalid @enderror" name="member_id"
                                        value="{{ $pembayaran->member()->first()->ktp()->first()->nama }}"/>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_cek_bg" class="col-md-4 col-form-label text-md-end">{{ __('No Cek BG')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_cek_bg" type="text" readonly
                                        class="form-control @error('no_cek_bg') is-invalid @enderror" name="no_cek_bg"
                                        value="{{ $pembayaran->no_cek_bg }}"/>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_rekening" class="col-md-4 col-form-label text-md-end">{{ __('No Rekening')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_rekening" type="text" readonly
                                        class="form-control @error('no_rekening') is-invalid @enderror" name="no_rekening"
                                        value="{{ $pembayaran->no_rekening }}"/>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_rekening" class="col-md-4 col-form-label text-md-end">{{ __('Nama Rekening')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_rekening" type="text" readonly
                                        class="form-control @error('nama_rekening') is-invalid @enderror" name="nama_rekening"
                                        value="{{ $pembayaran->nama_rekening }}"/>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Bayar')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                        value="{{ $pembayaran->tanggal }}"/>
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
                                            value="{{ number_format($pembayaran->total_setoran, 6, ".", ",") }}" />
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('pembayaran.show',$rencana->rencana_pembelian_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                                    <a type="button" href="{{ route('pembayaran.edit', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    @endif
                                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->divisi()->first()->nama_divisi == 'Top Manajemen')
                                    @if($pembayaran->is_confirmation_top)
                                    <form action="{{ route('pembayaran.update.confirmation_top_no', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Tolak Konfirmasi Pembayaran ini!\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-danger mr-2"><i class="fas fa-times"></i> Batalkan Konfirmasi</button>
                                    </form>
                                    @else
                                    <form action="{{ route('pembayaran.update.confirmation_top_ok', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Konfirmasi Pembayaran ini!\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-info mr-2"><i class="fas fa-check"></i> Konfirmasi Top Manajemen</button>
                                    </form>
                                    @endif
                                    @endif

                                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                                    @if($pembayaran->is_confirmation_admin)
                                    <form action="{{ route('pembayaran.update.confirmation_admin_no', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Tolak Konfirmasi Pembayaran ini!\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-danger mr-2"><i class="fas fa-times"></i>Batalkan Konfirmasi</button>
                                    </form>
                                    @else
                                    <form action="{{ route('pembayaran.update.confirmation_admin_ok', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Konfirmasi Pembayaran ini!\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-info mr-2"><i class="fas fa-check"></i> Konfirmasi Admin</button>
                                    </form>
                                    @endif
                                    @endif

                                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                                    <form action="{{ route('pembayaran.destroy', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Pembayaran akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('File Pembayaran') }}</h4>
                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member_id == $pembayaran->member_id)
                    <div class="card-header-action">
                        <a href="{{ route('pembayaran.file.create', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-pembayaran-file">
                            <thead>
                                <tr>
                                    <th>Tanggal Upload</th>
                                    <th>Nama File</th>
                                    <th>Nama Dokumen</th>
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
@endsection
