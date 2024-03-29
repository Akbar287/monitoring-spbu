@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Dombak</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('dombak.index') }}">Dombak</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Dombak</h2>
    <p class="section-lead">
        Detail data Dombak anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Dombak') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('konversi.index', $dombak->dombak_id) }}" class="btn btn-primary">
                            Tabel Konversi
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="dombak_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Dombak')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="dombak_id" type="text" readonly
                                        class="form-control @error('dombak_id') is-invalid @enderror" name="dombak_id"
                                        value="{{ $dombak->dombak_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>
        
                                <div class="col-md-6">
                                    <input readonly id="nama_divisi" type="text"
                                        class="form-control @error('nama_divisi') is-invalid @enderror" name="nama_divisi"
                                        value="{{ $dombak->spbu()->first()->nama_spbu }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_produk" class="col-md-4 col-form-label text-md-end">{{ __('Nama Produk')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="nama_dombak" type="text" readonly
                                        class="form-control @error('nama_dombak') is-invalid @enderror" name="nama_dombak"
                                        value="{{ $dombak->nama_dombak }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="is_aktif" class="col-md-4 col-form-label text-md-end">{{ __('Aktifkan')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input readonly id="is_aktif" type="text" readonly
                                        class="form-control @error('is_aktif') is-invalid @enderror" name="is_aktif"
                                        value="{{ $dombak->is_aktif == true ? 'Aktif' : 'Tidak Aktif' }}" autocomplete="name"
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
                                        autofocus>{{ $dombak->keterangan }}</textarea>
        
                                    @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('dombak.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('dombak.edit', $dombak->dombak_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('dombak.destroy', $dombak->dombak_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Dombak akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
