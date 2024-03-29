@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Member</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item">Member</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Member</h2>
    <p class="section-lead">
        Kelola Member yang ingin anda monitoring.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Member') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('member.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="table-responsive">
                        <table class="table table-hover table-member">
                            <thead>
                                <tr>
                                    <th>Gambar</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Verifikasi</th>
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
