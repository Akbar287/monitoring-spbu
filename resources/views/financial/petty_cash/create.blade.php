@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Petty Cash</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.financial') }}">Financial</a></div>
        <div class="breadcrumb-item"><a href="{{ route('financial.petty-cash') }}">Petty Cash</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Petty Cash</h2>
    <p class="section-lead">
        Tambahkan data Petty Cash anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Petty Cash') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('petty-cash.store') }}" method="post" enctype="multipart/form-data">@csrf 
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('spbu_id') is-invalid @enderror" name="spbu_id" id="spbu_id">
                                            @foreach($spbu as $s)
                                            <option {{ old('spbu_id') == $s->spbu_id ? 'selected' : '' }} value="{{ $s->spbu_id }}">{{ $s->nama_spbu . ' ('.$s->nomor_spbu.')' }}</option>
                                            @endforeach
                                        </select>
            
                                        @error('spbu_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_kode" class="col-md-4 col-form-label text-md-end">{{ __('No. Kode')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="no_kode" type="text"
                                            class="form-control @error('no_kode') is-invalid @enderror" name="no_kode"
                                            value="{{ old("no_kode") }}" autocomplete="name"
                                            autofocus>
            
                                        @error('no_kode')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_bukti" class="col-md-4 col-form-label text-md-end">{{ __('No. Bukti')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="no_bukti" type="text"
                                            class="form-control @error('no_bukti') is-invalid @enderror" name="no_bukti"
                                            value="{{ old("no_bukti") }}" autocomplete="name"
                                            autofocus>
            
                                        @error('no_bukti')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="jenis" class="col-md-4 col-form-label text-md-end">{{ __('Jenis')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('jenis') is-invalid @enderror" name="jenis" id="jenis">
                                            <option {{ old('jenis') == 'debit' ? 'selected' : '' }} value="{{ 'debit' }}">Debit</option>
                                            <option {{ old('jenis') == 'kredit' ? 'selected' : '' }} value="{{ 'kredit' }}">Kredit</option>
                                        </select>
            
                                        @error('jenis')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Jumlah')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="input-group mb-2">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text">Rp. </div>
                                                </div>
                                                <input id="jumlah" type="text" class="form-control thousand-style @error('jumlah') is-invalid @enderror" name="jumlah" value="{{ old("jumlah") }}">
                                            </div>
                                        </div>
            
                                        @error('jumlah')
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
                                            value="{{ is_null(old("tanggal")) ? date('Y-m-d') : old("tanggal") }}" autocomplete="name"
                                            autofocus>
            
                                        @error('tanggal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <textarea id="keterangan" type="text" rows="3" cols="3"
                                            class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                            value="" autocomplete="name"
                                            autofocus>{{ old("keterangan") }}</textarea>
            
                                        @error('keterangan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('financial.petty-cash') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
