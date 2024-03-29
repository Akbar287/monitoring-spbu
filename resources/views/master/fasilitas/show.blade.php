@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Fasilitas</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('fasilitas.index') }}">Fasilitas</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Fasilitas</h2>
    <p class="section-lead">
        Detail data Fasilitas anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Fasilitas') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('fasilitas.file.index', $fasilitas->fasilitas_id) }}" class="btn btn-primary">
                            File
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="fasilitas_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Fasilitas')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="fasilitas_id" type="text" readonly
                                        class="form-control @error('fasilitas_id') is-invalid @enderror" name="fasilitas_id"
                                        value="{{ $fasilitas->fasilitas_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>
        
                                <div class="col-md-6">
                                    <input readonly id="nama_divisi" type="text"
                                        class="form-control @error('nama_divisi') is-invalid @enderror" name="nama_divisi"
                                        value="{{ $fasilitas->spbu()->first()->nama_spbu }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_fasilitas" class="col-md-4 col-form-label text-md-end">{{ __('Nama Fasilitas')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="nama_fasilitas" type="text" readonly
                                        class="form-control @error('nama_fasilitas') is-invalid @enderror" name="nama_fasilitas"
                                        value="{{ $fasilitas->nama_fasilitas }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Jumlah')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="jumlah" type="text" readonly
                                        class="form-control @error('jumlah') is-invalid @enderror" name="jumlah"
                                        value="{{ $fasilitas->jumlah }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal_digunakan" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Digunakan')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="tanggal_digunakan" type="date" readonly
                                        class="form-control @error('tanggal_digunakan') is-invalid @enderror" name="tanggal_digunakan"
                                        value="{{ $fasilitas->tanggal_digunakan }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="" autocomplete="name"
                                        autofocus>{{ $fasilitas->keterangan }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('fasilitas.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('fasilitas.edit', $fasilitas->fasilitas_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('fasilitas.destroy', $fasilitas->fasilitas_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Fasilitas akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
