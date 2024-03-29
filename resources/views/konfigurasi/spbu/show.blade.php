@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>SPBU</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('spbu.index') }}">SPBU</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail SPBU</h2>
    <p class="section-lead">
        Detail data SPBU anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail SPBU') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('ID SPBU')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="spbu_id" type="text"
                                            class="readonly form-control" name="spbu_id" readonly
                                            value="{{ $spbu->spbu_id }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="nama_spbu" class="col-md-4 col-form-label text-md-end">{{ __('Nama SPBU')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="nama_spbu" type="text"
                                            class="readonly form-control" name="nama_spbu" readonly
                                            value="{{ $spbu->nama_spbu }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="nomor_spbu" class="col-md-4 col-form-label text-md-end">{{ __('Nomor SPBU')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="nomor_spbu" type="text"
                                            class="readonly form-control" name="nomor_spbu" readonly
                                            value="{{ $spbu->nomor_spbu }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal_pendirian" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Berdiri')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="tanggal_pendirian" type="date"
                                            class="readonly form-control" name="tanggal_pendirian" readonly
                                            value="{{ $spbu->tanggal_pendirian }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="luas_lahan" class="col-md-4 col-form-label text-md-end">{{ __('Luas Lahan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="luas_lahan" type="text"
                                            class="readonly form-control" name="luas_lahan" readonly
                                            value="{{ $spbu->luas_lahan }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="satuan_luas" class="col-md-4 col-form-label text-md-end">{{ __('Satuan Lahan')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="satuan_luas" type="text" placeholder="Ha/Km2/m2"
                                            class="readonly form-control" name="satuan_luas" readonly
                                            value="{{ $spbu->satuan_luas }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="alamat" class="col-md-4 col-form-label text-md-end">{{ __('Alamat')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <textarea id="alamat" type="text" rows="3" cols="3"
                                            class="readonly form-control" name="alamat" readonly
                                            value="" autocomplete="name"
                                            autofocus>{{ $spbu->alamat }}</textarea>
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('spbu.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <a type="button" href="{{ route('spbu.edit', $spbu->spbu_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                        <form action="{{ route('spbu.destroy', $spbu->spbu_id) }}" method="POST">@csrf @method('delete')
                                            <button type="submit" onclick="return confirm('Spbu akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                        </form>
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
