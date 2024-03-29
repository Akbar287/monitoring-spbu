@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Hari</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('hari.index') }}">Hari</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Hari</h2>
    <p class="section-lead">
        Detail data Hari anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Hari') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="nama_hari" class="col-md-4 col-form-label text-md-end">{{ __('Nama Hari')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="nama_hari" type="text" readonly
                                        class="form-control @error('nama_hari') is-invalid @enderror" name="nama_hari"
                                        value="{{ $hari->nama_hari }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="hari_kerja" class="col-md-4 col-form-label text-md-end">{{ __('Hari Kerja')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="hari_kerja" type="text" readonly
                                        class="form-control @error('hari_kerja') is-invalid @enderror" name="hari_kerja"
                                        value="{{ $hari->hari_kerja == true ? 'Hari Kerja' : "Hari Libur" }}" autocomplete="name"
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
                                        autofocus>{{ $hari->keterangan }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('hari.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('hari.edit', $hari->hari_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('hari.destroy', $hari->hari_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Hari akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
