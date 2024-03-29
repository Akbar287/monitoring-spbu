@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Kategori</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.artikel') }}">Artikel</a></div>
        <div class="breadcrumb-item"><a href="{{ route('kategori.index') }}">Kategori</a></div>
        <div class="breadcrumb-item"><a href="{{ route('kategori.show', $kategori->kategori_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Ubah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah Kategori</h2>
    <p class="section-lead">
        Ubah data Kategori anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah Kategori') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('kategori.update', $kategori->kategori_id) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="nama_kategori" class="col-md-4 col-form-label text-md-end">{{ __('Nama Kategori')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="nama_kategori" type="text"
                                            class="form-control @error('nama_kategori') is-invalid @enderror" name="nama_kategori"
                                            value="{{ $kategori->nama_kategori }}" autocomplete="name"
                                            autofocus>

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
                                            class="form-control @error('detail') is-invalid @enderror" name="detail"
                                            value="" autocomplete="name"
                                            autofocus>{{ $kategori->detail }}</textarea>

                                        @error('detail')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('kategori.show', $kategori->kategori_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
