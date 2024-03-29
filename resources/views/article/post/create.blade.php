@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Post</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.artikel') }}">Artikel</a></div>
        <div class="breadcrumb-item"><a href="{{ route('post.index') }}">Post</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Tambah Post</h2>
    <p class="section-lead">
        Tambahkan data Post anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Post') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('post.store') }}" method="post" enctype="multipart/form-data">@csrf
                        <div class="row">
                            <div class="col-md-12">

                                <div class="row mb-3 justify-content-center">
                                    <label for="tag" class="col-md-4 col-form-label text-md-end">{{ __('Tag')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select id="tag" class="custom-select select2 @error('tag') is-invalid @enderror" @if(count($tag) == 0) disable @endif name="tag[]" multiple="">
                                            @if(count($tag) > 0)
                                            @foreach($tag as $k)
                                            <option value="{{ $k->tag_id }}">{{ $k->nama_tag }}</option>
                                            @endforeach
                                            @else
                                            <option value="">Tidak Ada Tag</option>
                                            @endif
                                        </select>

                                        @error('tag')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="kategori" class="col-md-4 col-form-label text-md-end">{{ __('Kategori')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select id="kategori" class="custom-select select2 @error('kategori') is-invalid @enderror" @if(count($kategori) == 0) disable @endif name="kategori[]" multiple="">
                                            @if(count($kategori) > 0)
                                            @foreach($kategori as $k)
                                            <option value="{{ $k->kategori_id }}">{{ $k->nama_kategori }}</option>
                                            @endforeach
                                            @else
                                            <option value="">Tidak Ada Kategori</option>
                                            @endif
                                        </select>

                                        @error('kategori')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="title" class="col-md-4 col-form-label text-md-end">{{ __('Judul')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="title" type="text"
                                            class="form-control @error('title') is-invalid @enderror" name="title"
                                            value="{{ old("title") }}" autocomplete="name"
                                            autofocus>

                                        @error('title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="content" class="col-md-4 col-form-label text-md-end">{{ __('Konten')
                                        }}</label>

                                    <div class="col-md-6">
                                        <textarea id="content" type="text" rows="3" cols="3"
                                            class="form-control @error('content') is-invalid @enderror" name="content"
                                            value="" autocomplete="name"
                                            autofocus>{{ old("content") }}</textarea>

                                        @error('content')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="active" class="col-md-4 col-form-label text-md-end">{{ __('Status')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select id="active" type="text" rows="3" cols="3" class="custom-select @error('active') is-invalid @enderror" name="active">
                                            <option value="true">Aktifkan</option>
                                            <option value="false">Jangan Aktifkan</option>
                                        </select>

                                        @error('active')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('post.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
