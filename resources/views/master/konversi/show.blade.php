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
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Konversi</h2>
    <p class="section-lead">
        Detail data Konversi anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Konversi') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="konversi_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Konversi')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="konversi_id" type="text" readonly
                                        class="form-control @error('konversi_id') is-invalid @enderror" name="konversi_id"
                                        value="{{ $konversi->konversi_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="satuan_ukur_tinggi_id" class="col-md-4 col-form-label text-md-end">{{ __('Satuan Ukur Tinggi')}}</label>
        
                                <div class="col-md-6">
                                    <input readonly id="satuan_ukur_tinggi_id" type="text"
                                        class="form-control @error('satuan_ukur_tinggi_id') is-invalid @enderror" name="satuan_ukur_tinggi_id"
                                        value="{{ $konversi->satuan_ukur_tinggi()->first()->nama_satuan }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="satuan_ukur_volume_id" class="col-md-4 col-form-label text-md-end">{{ __('Satuan Ukur Volume')}}</label>
        
                                <div class="col-md-6">
                                    <input readonly id="satuan_ukur_volume_id" type="text"
                                        class="form-control @error('satuan_ukur_volume_id') is-invalid @enderror" name="satuan_ukur_volume_id"
                                        value="{{ $konversi->satuan_ukur_volume()->first()->nama_satuan }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tinggi" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="tinggi" type="text" readonly
                                        class="form-control thousand-style @error('tinggi') is-invalid @enderror" name="tinggi"
                                        value="{{ $konversi->tinggi }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume" class="col-md-4 col-form-label text-md-end">{{ __('Volume')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="volume" type="text" readonly
                                        class="form-control thousand-style @error('volume') is-invalid @enderror" name="volume"
                                        value="{{ $konversi->volume }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('konversi.index', $dombak->dombak_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('konversi.edit', [$dombak->dombak_id, $konversi->konversi_id]) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('konversi.destroy', [$dombak->dombak_id, $konversi->konversi_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Konversi akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
