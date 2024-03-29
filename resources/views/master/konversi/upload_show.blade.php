@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Konversi</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('dombak.index') }}">Dombak</a></div>
        <div class="breadcrumb-item"><a href="{{ route('dombak.show', $dombak->dombak_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('konversi.index', $dombak->dombak_id) }}">Konversi</a></div>
        <div class="breadcrumb-item">Upload</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Unggah Konversi</h2>
    <p class="section-lead">
        Unggah data Konversi anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Unggah Konversi untuk Dombak ' . $dombak->nama_dombak) }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('konversi.upload_store', $dombak->dombak_id) }}" method="post" enctype="multipart/form-data">@csrf 
                        <div class="row">
                            <div class="col-md-12">
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="satuan_ukur_tinggi_id" class="col-md-4 col-form-label text-md-end">{{ __('Satuan Tinggi')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('satuan_ukur_tinggi_id') is-invalid @enderror" name="satuan_ukur_tinggi_id" id="satuan_ukur_tinggi_id">
                                            @foreach($satuanUkurTinggi as $s)
                                            <option {{ old('satuan_ukur_tinggi_id') == $s->satuan_ukur_tinggi_id ? 'selected' : '' }} value="{{ $s->satuan_ukur_tinggi_id }}">{{ $s->nama_satuan . ' ('.$s->singkatan.')' }}</option>
                                            @endforeach
                                        </select>
            
                                        @error('satuan_ukur_tinggi_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="satuan_ukur_volume_id" class="col-md-4 col-form-label text-md-end">{{ __('Satuan Volume')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select class="custom-select @error('satuan_ukur_volume_id') is-invalid @enderror" name="satuan_ukur_volume_id" id="satuan_ukur_volume_id">
                                            @foreach($satuanUkurVolume as $s)
                                            <option {{ old('satuan_ukur_volume_id') == $s->satuan_ukur_volume_id ? 'selected' : '' }} value="{{ $s->satuan_ukur_volume_id }}">{{ $s->nama_satuan . ' ('.$s->singkatan.')' }}</option>
                                            @endforeach
                                        </select>
            
                                        @error('satuan_ukur_volume_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="upload" class="col-md-4 col-form-label text-md-end">{{ __('Upload File Excel')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input @error('upload') is-invalid @enderror" id="upload" name="upload">
                                            <label class="custom-file-label" for="upload">Pilih File</label>
                                        </div>
            
                                        @error('upload')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('konversi.index', $dombak->dombak_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
