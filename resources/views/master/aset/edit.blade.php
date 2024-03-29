@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Aset</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.index') }}">Aset</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.show', $aset->aset_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Ubah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah Aset</h2>
    <p class="section-lead">
        Ubah data Aset anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah Aset') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('aset.update', $aset->aset_id) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('spbu_id') is-invalid @enderror" name="spbu_id" id="spbu_id">
                                            @foreach($spbu as $s)
                                            <option {{ $aset->spbu_id == $s->spbu_id ? 'selected' : '' }} value="{{ $s->spbu_id }}">{{ $s->nama_spbu . ' ('.$s->nomor_spbu.')' }}</option>
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
                                    <label for="nama_aset" class="col-md-4 col-form-label text-md-end">{{ __('Nama Aset')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="nama_aset" type="text"
                                            class="form-control @error('nama_aset') is-invalid @enderror" name="nama_aset"
                                            value="{{ $aset->nama_aset }}" autocomplete="name"
                                            autofocus>
            
                                        @error('nama_aset')
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
                                        <input id="jumlah" type="text"
                                            class="form-control @error('jumlah') is-invalid @enderror" name="jumlah"
                                            value="{{ $aset->jumlah }}" autocomplete="name"
                                            autofocus>
            
                                        @error('jumlah')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal_digunakan" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Digunakan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="tanggal_digunakan" type="date"
                                            class="form-control @error('tanggal_digunakan') is-invalid @enderror" name="tanggal_digunakan"
                                            value="{{ $aset->tanggal_digunakan }}" autocomplete="name"
                                            autofocus>
            
                                        @error('tanggal_digunakan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="harga" class="col-md-4 col-form-label text-md-end">{{ __('Harga')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    Rp
                                                </div>
                                            </div>
                                            <input id="harga" type="text"
                                                class="form-control currency @error('harga') is-invalid @enderror" name="harga"
                                                value="{{ $aset->harga }}" autocomplete="name"
                                                autofocus>
                                        </div>
            
                                        @error('harga')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="penyusutan_per_hari" class="col-md-4 col-form-label text-md-end">{{ __('Penyusutan Per Hari')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    Rp
                                                </div>
                                            </div>
                                            <input id="penyusutan_per_hari" type="text"
                                                class="form-control @error('penyusutan_per_hari') is-invalid @enderror" name="penyusutan_per_hari"
                                                value="{{ $aset->penyusutan_per_hari }}" autocomplete="name"
                                                autofocus>
                                        </div>
                                        
                                        @error('penyusutan_per_hari')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="is_digunakan" class="col-md-4 col-form-label text-md-end">{{ __('Digunakan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('is_digunakan') is-invalid @enderror" name="is_digunakan" id="is_digunakan">
                                            <option {{ $aset->is_digunakan == 'false' ? 'selected' : '' }} value="false">Tidak digunakan</option>
                                            <option {{ $aset->is_digunakan == 'true' ? 'selected' : '' }} value="true">Digunakan</option>
                                        </select>
            
                                        @error('is_aktif')
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
                                            autofocus>{{ $aset->keterangan }}</textarea>
            
                                        @error('keterangan')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('aset.show', $aset->aset_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
