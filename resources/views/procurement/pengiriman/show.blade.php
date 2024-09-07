@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Pengiriman</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pengiriman.index') }}">Pengiriman</a></div>
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
                        <div class="badge badge-success">Terkonfirmasi Admin</div>
                        @else
                        <div class="badge badge-danger">Belum Dikonfirmasi Admin</div>
                        @endif
                        @if($pengiriman->is_confirmation_top)
                        <div class="badge badge-success">Terkonfirmasi Top</div>
                        @else
                        <div class="badge badge-danger">Belum Dikonfirmasi Top</div>
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
                        <div class="col-12">
                            @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->ktp()->first()->nama == $pengiriman->member()->first()->ktp()->first()->nama)
                            @if(!$pengiriman->is_confirmation_admin && !$pengiriman->is_done)
                            <div class="alert alert-warning">Konfirmasi untuk mengubah status dalam pengiriman.</div>
                            @endif
                            @if($pengiriman->is_confirmation_admin && !$pengiriman->is_done)
                            <div class="alert alert-warning">Tandai Selesai untuk mengubah status Sudah Diterima.</div>
                            @endif
                            @endif
                        </div>

                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Yang Menerima')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="member_id" type="text" readonly
                                        class="form-control" name="member_id"
                                        value="{{ $pengiriman->member()->first()->ktp()->first()->nama }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_do" class="col-md-4 col-form-label text-md-end">{{ __('No DO')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_do" type="text"
                                        class="form-control" name="no_do" readonly
                                        value="{{ $pengiriman->no_do }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_pol" class="col-md-4 col-form-label text-md-end">{{ __('No Pol')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_pol" type="text" readonly
                                        class="form-control" name="no_pol"
                                        value="{{ $pengiriman->no_pol }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="catatan" class="col-md-4 col-form-label text-md-end">{{ __('Catatan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea name="catatan" readonly class="form-control" id="catatan" cols="30" rows="10">
                                        {{ $pengiriman->catatan }}
                                    </textarea>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control" name="tanggal"
                                        value="{{ $pengiriman->tanggal }}" />
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-10 offset-md-2 d-flex align-items-end">
                                    <a type="button" href="{{ route('pengiriman.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('pengiriman.ubah_catatan', $pengiriman->pengiriman_id) }}" class="btn btn-info mr-2"><i class="fas fa-pen"></i> Ubah Catatan</a>
                                    @if(count($temp) == 0)
                                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->divisi()->first()->nama_divisi == 'Top Manajemen' || Auth::user()->member()->first()->ktp()->first()->nama == $pengiriman->member()->first()->ktp()->first()->nama)

                                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                                    @if(!$pengiriman->is_done)
                                    @if($pengiriman->is_confirmation_admin)
                                    <form action="{{ route('pengiriman.admin_down', [$pengiriman->pengiriman_id]) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Konfirmasi akan dibatalkan?')" title="Selesaikan" class="btn btn-danger mr-2"><i class="fas fa-times"></i> Batalkan Konfirmasi</button>
                                    </form>
                                    @else
                                    <form action="{{ route('pengiriman.admin_top', [$pengiriman->pengiriman_id]) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Pengiriman akan dikonfirmasi?')" title="Konfirmasi" class="btn btn-success mr-2"><i class="fas fa-check"></i> Konfirmasi Admin</button>
                                    </form>
                                    @endif
                                    @endif
                                    @endif

                                    {{-- @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->divisi()->first()->nama_divisi == 'Top Manajemen') --}}
                                    @if(!$pengiriman->is_done)
                                    @if($pengiriman->is_confirmation_top)
                                    <form action="{{ route('pengiriman.top_down', [$pengiriman->pengiriman_id]) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Konfirmasi akan dibatalkan ?')" title="Batalkan Konfirmasi" class="btn btn-danger mr-2"><i class="fas fa-times"></i> Batalkan Konfirmasi</button>
                                    </form>
                                    @else
                                    <form action="{{ route('pengiriman.top_top', [$pengiriman->pengiriman_id]) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Pengiriman akan dikonfirmasi ?')" title="Konfirmasi" class="btn btn-success mr-2"><i class="fas fa-check"></i> Konfirmasi Top Manajemen</button>
                                    </form>
                                    @endif
                                    @endif
                                    {{-- @endif --}}

                                    @if($pengiriman->is_confirmation_admin && $pengiriman->is_confirmation_top && !$pengiriman->is_done)
                                    <form action="{{ route('pengiriman.done', [$pengiriman->pengiriman_id]) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Pengiriman akan diselesaikan dan dilanjukan ke fase Penerimaan BBM!\n Lanjutkan?')" title="Selesaikan" class="btn btn-info mr-2"><i class="fas fa-check"></i> Tandai Selesai</button>
                                    </form>
                                    @endif
                                    @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>File LO</h4>
                    @if(count($temp) > 0)
                    <div class="card-header-action">
                        <a href="{{ route('pengiriman.file_lo_create', $pengiriman->pengiriman_id) }}" class="btn btn-success">Tambah File LO</a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-pengiriman-lo">
                            <thead>
                                <tr>
                                    <th>No. Faktur</th>
                                    <th>No. LO</th>
                                    <th>Nama Produk</th>
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
@endsection
