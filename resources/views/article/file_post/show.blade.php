@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File Post</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.artikel') }}">Artikel</a></div>
        <div class="breadcrumb-item"><a href="{{ route('post.index') }}">Post</a></div>
        <div class="breadcrumb-item"><a href="{{ route('post.show', $post->post_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('file.post.index', $post->post_id) }}">File</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail File Post</h2>
    <p class="section-lead">
        Detail data File Post anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail File Post') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="file" class="col-md-4 col-form-label text-md-end">{{
                                    __('File')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="custom-file">
                                        <input type="file" disabled name="file" class="custom-file-input" id="file">
                                        <label class="custom-file-label" for="file">Pilih File</label>
                                    </div>

                                    @error('file')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror

                                    <img src="{{ asset('storage/post/' . $filePost->nama_file) }}" alt="{{ old("file") }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto">
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('file.post.index', $post->post_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('file.post.download', [$post->post_id, $filePost->file_post_id]) }}" class="btn mx-2 btn-info"><i class="fas fa-download"></i> Unduh</a>
                                    <a type="button" href="{{ route('file.post.edit', [$post->post_id, $filePost->file_post_id]) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('file.post.delete', [$post->post_id, $filePost->file_post_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('File Post akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
