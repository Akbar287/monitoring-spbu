@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Post</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.artikel') }}">Artikel</a></div>
        <div class="breadcrumb-item">Post</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Post</h2>
    <p class="section-lead">
        Kelola Post di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Post') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('post.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-post">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Judul</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
