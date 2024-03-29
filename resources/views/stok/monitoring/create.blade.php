@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Monitoring Stok</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('monitoring.index') }}">Monitoring</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Monitoring Stok</h2>
    <p class="section-lead">
        Tambahkan data Monitoring Stok anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            <form action="{{ route('monitoring.store') }}" method="post" enctype="multipart/form-data">@csrf 
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Pilih Produk') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('produk_id') is-invalid @enderror" name="produk_id" id="produk_id">
                                            @foreach($produk as $s)
                                            <option {{ old('produk_id') == $s->produk_id ? 'selected' : '' }} value="{{ $s->produk_id }}">{{ $s->nama_produk }}</option>
                                            @endforeach
                                        </select>
            
                                        @error('produk_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Atur Dombak') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
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
                                                            value="{{ $d->dombak_id }}" autocomplete="name"
                                                            autofocus>
                                                    </td>
                                                    <td>
                                                        <input readonly id="nama_dombak[]" type="text" readonly
                                                            class="form-control @error('nama_dombak[]') is-invalid @enderror" name="nama_dombak[]"
                                                            value="{{ $d->nama_dombak }}" autocomplete="name"
                                                            autofocus>
                                                    </td>
                                                    <td>
                                                        <input id="stok[]" type="text"
                                                            class="form-control @error('stok[]') is-invalid @enderror" name="stok[]"
                                                            value="0.00" autocomplete="name"
                                                            autofocus>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <span class="text-muted">Jangan Gunakan koma, untuk kedetailan gunakan 2 angka dibelakang contoh: 24000.00 liter</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('monitoring.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
