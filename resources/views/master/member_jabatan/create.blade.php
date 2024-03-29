@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Jabatan Member</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('member.index') }}">Member</a></div>
        <div class="breadcrumb-item"><a href="{{ route('member.show', [$member->member_id]) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('member.jabatan.index', [$member->member_id]) }}">Jabatan</a></div>
        <div class="breadcrumb-item">Pilih Jabatan</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Pilih Jabatan</h2>
    <p class="section-lead">
        Pilih Jabatan yang akan di relasikan dengan Member {{ $member->ktp()->first()->nama }}.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Jabatan') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-jabatan-member-create">
                                <thead>
                                    <tr>
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
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <a href="{{ route('member.jabatan.index', $member->member_id) }}" class="btn btn-primary">Kembali</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
