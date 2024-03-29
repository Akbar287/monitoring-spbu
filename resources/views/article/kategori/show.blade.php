@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Kategori</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.artikel') }}">Artikel</a></div>
        <div class="breadcrumb-item"><a href="{{ route('kategori.index') }}">Kategori</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Kategori</h2>
    <p class="section-lead">
        Detail data Kategori anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Kategori') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="nama_kategori" class="col-md-4 col-form-label text-md-end">{{ __('Nama Kategori')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_kategori" type="text"
                                        class="form-control @error('nama_kategori') is-invalid @enderror" name="nama_kategori" readonly
                                        value="{{ $kategori->nama_kategori }}">

                                    @error('nama_kategori')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="detail" class="col-md-4 col-form-label text-md-end">{{ __('Detail')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="detail" type="text" rows="3" cols="3"
                                        class="form-control @error('detail') is-invalid @enderror" name="detail" readonly
                                        >{{ $kategori->detail }}</textarea>

                                    @error('detail')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('kategori.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('kategori.edit', $kategori->kategori_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('kategori.delete', $kategori->kategori_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Kategori akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
