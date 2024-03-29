@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Pengiriman</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.produk.index') }}">MS2</a></div>
        <div class="breadcrumb-item"><a href="{{ route('ms2.pengiriman') }}">PengirimanMS2</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Pengiriman</h2>
    <p class="section-lead">
        Tambahkan data Pengiriman anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Pengiriman') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('ms2.pengiriman.store') }}" method="post" enctype="multipart/form-data">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Yang Menerima')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('member_id') is-invalid @enderror" name="member_id" id="member_id">
                                            @foreach($member as $s)
                                            <option {{ old('member_id') == $s->member_id ? 'selected' : '' }} value="{{ $s->member_id }}">{{ $s->ktp()->first()->nama  }}</option>
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
                                    <label for="no_do" class="col-md-4 col-form-label text-md-end">{{ __('No DO')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_do" type="text"
                                            class="form-control @error('no_do') is-invalid @enderror" name="no_do"
                                            value="{{ old("no_do") }}" />

                                        @error('no_do')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_pol" class="col-md-4 col-form-label text-md-end">{{ __('No Pol')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_pol" type="text"
                                            class="form-control @error('no_pol') is-invalid @enderror" name="no_pol"
                                            value="{{ old("no_pol") }}" />

                                        @error('no_pol')
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
                                        <input id="tanggal" type="date"
                                            class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                            value="{{ is_null(old("tanggal")) ? date('Y-m-d') : old("tanggal") }}" />

                                        @error('tanggal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="catatan" class="col-md-4 col-form-label text-md-end">{{ __('Catatan')
                                        }}</label>

                                    <div class="col-md-6">
                                        <textarea id="catatan" class="form-control @error('catatan') is-invalid @enderror" name="catatan" cols="30" rows="10">{{ old("catatan") }}</textarea>

                                        @error('catatan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('ms2.pengiriman') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
