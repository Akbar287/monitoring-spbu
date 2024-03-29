@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Monitoring Stok</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.index') }}">Monitoring</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.show', $stok->stok_inventory_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Ubah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah Monitoring Stok</h2>
    <p class="section-lead">
        Ubah data Monitoring Stok anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            <form action="{{ route('monitoring.update', $stok->stok_inventory_id) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Ubah Monitoring Stok') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')}}</label>

                                    <div class="col-md-6">
                                        <input readonly id="produk_id" type="text"
                                            class="form-control @error('produk_id') is-invalid @enderror" name="produk_id"
                                            value="{{ $stok->produk()->first()->nama_produk }}"
                                            >
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="stok" class="col-md-4 col-form-label text-md-end">{{ __('Stok')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="stok" type="text"
                                            class="form-control @error('stok') is-invalid @enderror" name="stok"
                                            value="{{ $stok->stok }}" readonly
                                            >

                                        @error('stok')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h4>{{ __('Atur Dombak') }}</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="alert alert-danger">Pastikan anda mengetahui apa yang anda perbuat. Merubah initial stok bisa MERUBAH dan/atau MERUSAK perhitungan laporan stok ataupun keuangan.</div>
                                                <div class="row mb-3 justify-content-center">
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
                                                                @foreach($dombak as $d)
                                                                <tr>
                                                                    <td>
                                                                        <input readonly id="dombak_id[]" type="text" readonly
                                                                            class="form-control @error('dombak_id[]') is-invalid @enderror" name="dombak_id[]"
                                                                            value="{{ $d->dombak_id }}"
                                                                            >
                                                                    </td>
                                                                    <td>
                                                                        <input readonly id="nama_dombak[]" type="text" readonly
                                                                            class="form-control @error('nama_dombak[]') is-invalid @enderror" name="nama_dombak[]"
                                                                            value="{{ $d->nama_dombak }}"
                                                                            >
                                                                    </td>
                                                                    <td>
                                                                        <input id="stok[]" type="text"
                                                                            class="form-control @error('stok[]') is-invalid @enderror" name="stok[]"
                                                                            value="{{ $d->stok_inventory_dombak()->where('stok_inventory_id', $stok->stok_inventory_id)->count() > 0 ? $d->stok_inventory_dombak()->where('stok_inventory_id', $stok->stok_inventory_id)->first()->stok : '0.00' }}"
                                                                            >
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <span class="text-muted">Jangan Gunakan koma, untuk kedetailan gunakan 2 angka dibelakang contoh: 24000.00 liter.<br /> Untuk menghapus dombak, kembalikan stok ke angka 0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('monitoring.show', $stok->stok_inventory_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button type="submit" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
