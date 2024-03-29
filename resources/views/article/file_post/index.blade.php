@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>File</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.artikel') }}">Artikel</a></div>
        <div class="breadcrumb-item"><a href="{{ route('post.index') }}">Post</a></div>
        <div class="breadcrumb-item"><a href="{{ route('post.show', $post->post_id) }}">Detail</a></div>
        <div class="breadcrumb-item">File</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">File</h2>
    <p class="section-lead">
        Kelola File di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List File') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('file.post.create', $post->post_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-file-post">
                            <thead>
                                <tr>
                                    <th>Nama Dokumen</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('post.show', $post->post_id) }}" class="btn btn-info"><i class="fas fa fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
