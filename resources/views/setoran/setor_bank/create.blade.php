@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Bank</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.setoran_bank.index_setoran_bank_penjualan') }}">Setoran Bank</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.setoran_bank.show_setoran_bank_penjualan', $penjualan->penjualan_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.setoran_bank.bank.index', $penjualan->penjualan_id) }}">Bank</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Bank</h2>
    <p class="section-lead">
        Tambahkan data Bank anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Bank') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('setoran.setoran_bank.bank.store', $penjualan->penjualan_id) }}" method="post" enctype="multipart/form-data">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="total_setoran" class="col-md-4 col-form-label text-md-end">{{ __('Total Setoran')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    Rp
                                                </div>
                                            </div>
                                            <input id="total_setoran" type="text" class="form-control thousand-style @error('total_setoran') is-invalid @enderror" name="total_setoran" value="{{ $penjualan->total_penjualan }}" readonly />
                                        </div>

                                        @error('total_setoran')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Dititipkan Oleh')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select name="member_id" id="member_id" class="custom-select @error('member_id') is-invalid @enderror" name="member_id">
                                            @foreach($member as $m)
                                            <option value="{{ $m->member_id }}" {{ $m->member_id  == old('member_id') ? 'selected' : '' }}>{{ $m->nama }}</option>
                                            @endforeach
                                        </select>

                                        @error('member_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tanggal" type="date" class="form-control @error('tanggal') is-invalid @enderror" name="tanggal" value="{{ is_null(old("tanggal")) ? date('Y-m-d') : old("tanggal") }}" />

                                        @error('tanggal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="nama_bank" class="col-md-4 col-form-label text-md-end">{{ __('Nama Bank')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="nama_bank" type="text" class="form-control @error('nama_bank') is-invalid @enderror" name="nama_bank" value="{{ is_null(old("nama_bank")) ? '' : old("nama_bank") }}" />

                                        @error('nama_bank')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_referensi" class="col-md-4 col-form-label text-md-end">{{ __('No. Referensi')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_referensi" type="text" class="form-control @error('no_referensi') is-invalid @enderror" name="no_referensi" value="{{ is_null(old("no_referensi")) ? '' : old("no_referensi") }}" />

                                        @error('no_referensi')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="metode_transfer" class="col-md-4 col-form-label text-md-end">{{ __('Metode Transfer')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="metode_transfer" type="text" class="form-control @error('metode_transfer') is-invalid @enderror" name="metode_transfer" value="{{ is_null(old("metode_transfer")) ? '' : old("metode_transfer") }}" />

                                        @error('metode_transfer')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
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
                                            <input id="total_uang" type="text"
                                                class="form-control thousand-style @error('total_uang') is-invalid @enderror" name="total_uang"
                                                value="{{ old("total_uang") }}"/>
                                        </div>

                                        @error('total_uang')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('setoran.setoran_bank.bank.index', $penjualan->penjualan_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button type="submit" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
