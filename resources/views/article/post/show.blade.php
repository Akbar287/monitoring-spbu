@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Post</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.artikel') }}">Artikel</a></div>
        <div class="breadcrumb-item"><a href="{{ route('post.index') }}">Post</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Post</h2>
    <p class="section-lead">
        Detail data Post anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Post') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="tag" class="col-md-4 col-form-label text-md-end">{{ __('Tag')
                                    }}</label>

                                <div class="col-md-6">
                                    @foreach($post->tag()->get() as $tag)
                                    <input readonly id="tag[{{ $loop->iteration - 1 }}]" type="text" class="form-control mb-2" readonly name="tag[{{ $loop->iteration - 1 }}]" value="{{ $tag->nama_tag }}">
                                    @endforeach
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="kategori" class="col-md-4 col-form-label text-md-end">{{ __('Kategori')
                                    }}</label>

                                <div class="col-md-6">
                                    @foreach($post->kategori()->get() as $kategori)
                                    <input readonly id="kategori[{{ $loop->iteration - 1 }}]" type="text" class="form-control mb-2" readonly name="kategori[{{ $loop->iteration - 1 }}]" value="{{ $kategori->nama_kategori }}">
                                    @endforeach
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="title" class="col-md-4 col-form-label text-md-end">{{ __('Judul')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" readonly name="title" value="{{ $post->title }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="content" class="col-md-4 col-form-label text-md-end">{{ __('Konten')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="content" type="text" rows="3" cols="3" class="form-control @error('content') is-invalid @enderror" name="content" readonly>{{ $post->content }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="active" class="col-md-4 col-form-label text-md-end">{{ __('Status')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="active" type="text" class="form-control @error('title') is-invalid @enderror" name="active" value="{{ $post->active ? 'Aktifkan' : 'Jangan Aktifkan' }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('post.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('file.post.index', $post->post_id) }}" class="btn mx-2 btn-info"><i class="fas fa-file"></i> File</a>
                                    <a type="button" href="{{ route('post.edit', $post->post_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('post.delete', $post->post_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Post akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
