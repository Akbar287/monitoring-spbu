@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Pj Operasional</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open') }}">Open Penjualan</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open.show', $penjualan->penjualan_id) }}">Detail</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.open.pj', $penjualan->penjualan_id) }}">Pj Operasional</a></div>
        <div class="breadcrumb-item">Tambah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Pj Operasional</h2>
    <p class="section-lead">
        Tambahkan data Pj Operasional anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Tambah Pj Operasional') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('setoran.open.pj.store', $penjualan->penjualan_id) }}" method="post">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="jabatan_id" class="col-md-4 col-form-label text-md-end">{{ __('Nama Jabatan')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('jabatan_id') is-invalid @enderror" name="jabatan_id" id="jabatan_id">
                                            <option value="0">Silakan Pilih Nama Jabatan</option>
                                            @foreach($jabatan as $s)
                                            <option {{ old('jabatan_id') == $s->jabatan_id ? 'selected' : '' }} value="{{ $s->jabatan_id }}">{{ $s->nama_jabatan }}</option>
                                            @endforeach
                                        </select>

                                        @error('jabatan_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Nama Pegawai')
                                        }}</label>

                                    <div class="col-md-6">
                                        <p class="msg-pj">Silakan Pilih Jabatan</p>
                                        @if($member->count() > 0)
                                        @foreach($member as $m)
                                        <div class="custom-control custom-checkbox d-none" data-id="{{ $m->member_id }}">
                                            <input type="checkbox" class="custom-control-input" name="member_id[]" value="{{ $m->member_id }}" id="{{ $m->member_id }}">
                                            <label class="custom-control-label" for="{{ $m->member_id }}">{{ $m->nama }}</label>
                                        </div>
                                        @endforeach
                                        @else
                                        <p>Tidak ada Data Pegawai</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('setoran.open.pj', $penjualan->penjualan_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
