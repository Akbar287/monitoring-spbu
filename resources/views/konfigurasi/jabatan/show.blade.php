@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Jabatan</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('jabatan.index') }}">Jabatan</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Jabatan</h2>
    <p class="section-lead">
        Detail data Jabatan anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Jabatan') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="level_id" class="col-md-4 col-form-label text-md-end">{{ __('Level')}}</label>
        
                                <div class="col-md-6">
                                    <input readonly id="level_id" type="text"
                                        class="form-control @error('level_id') is-invalid @enderror" name="level_id"
                                        value="{{ $jabatan->level()->first()->nama_level }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_jabatan" class="col-md-4 col-form-label text-md-end">{{ __('Nama Jabatan')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input readonly id="nama_jabatan" type="text"
                                        class="form-control @error('nama_jabatan') is-invalid @enderror" name="nama_jabatan"
                                        value="{{ $jabatan->nama_jabatan }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="is_aktif" class="col-md-4 col-form-label text-md-end">{{ __('Aktifkan')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input readonly id="is_aktif" type="text"
                                        class="form-control @error('is_aktif') is-invalid @enderror" name="is_aktif"
                                        value="{{ $jabatan->is_aktif ? 'Aktif' : 'Tidak Aktif' }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <textarea id="keterangan" readonly type="text" rows="3" cols="3"
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="" autocomplete="name"
                                        autofocus>{{ $jabatan->keterangan }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('jabatan.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('jabatan.edit', $jabatan->jabatan_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('jabatan.destroy', $jabatan->jabatan_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Jabatan akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
