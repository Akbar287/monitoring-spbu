@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Jabatan Member</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('member.index') }}">Member</a></div>
        <div class="breadcrumb-item"><a href="{{ route('member.show', $member->member_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Jabatan</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Jabatan Member</h2>
    <p class="section-lead">
        Kelola Jabatan Member.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Jabatan Member') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('member.jabatan.create', $member->member_id) }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="table-responsive">
                        <table class="table table-hover table-jabatan-member">
                            <thead>
                                <tr>
                                    <th>Id Jabatan</th>
                                    <th>Nama Divisi</th>
                                    <th>Nama Level</th>
                                    <th>Nama Jabatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-12"><a href="{{ route('member.show', $member->member_id) }}" class="btn btn-primary">Kembali</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
