@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Monitoring Stok</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.index') }}">Monitoring Stok</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Monitoring Stok</h2>
    <p class="section-lead">
        Detail data Monitoring Stok anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Monitoring Stok') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('monitoring.show_riwayat', $stok->stok_inventory_id) }}" class="btn btn-primary">
                            Riwayat Stok
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="produk_id" type="text"
                                        class="form-control @error('produk_id') is-invalid @enderror" name="produk_id"
                                        value="{{ $stok->produk()->first()->nama_produk }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="stok" class="col-md-4 col-form-label text-md-end">{{ __('Total Stok')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="stok" type="text"
                                        class="form-control @error('stok') is-invalid @enderror" name="stok"
                                        value="{{ $stok->stok }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center mt-3">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Id Dombak</th>
                                                <th>Nama Dombak</th>
                                                <th>Initial Stok</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stok->stok_inventory_dombak()->get() as $d)
                                            <tr>
                                                <td>
                                                    <input readonly id="dombak_id[]" type="text" readonly
                                                        class="form-control @error('dombak_id[]') is-invalid @enderror" name="dombak_id[]"
                                                        value="{{ $d->dombak()->first()->dombak_id }}" />
                                                </td>
                                                <td>
                                                    <input readonly id="nama_dombak[]" type="text" readonly
                                                        class="form-control @error('nama_dombak[]') is-invalid @enderror" name="nama_dombak[]"
                                                        value="{{ $d->dombak()->first()->nama_dombak }}" />
                                                </td>
                                                <td>
                                                    <input id="stok[]" type="text" readonly
                                                        class="form-control @error('stok[]') is-invalid @enderror" name="stok[]"
                                                        value="{{ $d->stok }}" />
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('monitoring.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                                    <a type="button" href="{{ route('monitoring.edit', $stok->stok_inventory_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('monitoring.delete', $stok->stok_inventory_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Stok Inventory akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
