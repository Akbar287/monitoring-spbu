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
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Pj Operasional</h2>
    <p class="section-lead">
        Detail data Pj Operasional.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Pj Operasional') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="jabatan_id" class="col-md-4 col-form-label text-md-end">{{ __('Nama Jabatan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input type="text" name="jabatan_id" class="form-control" readonly value="{{ $jabatan->nama_jabatan }}" id="jabatan_id">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Nama Pegawai')
                                    }}</label>

                                <div class="col-md-6">
                                    @foreach($penjualan->pj_operasional_setoran()->where('jabatan_id', $jabatan->jabatan_id)->get() as $pj)
                                    <div class="custom-control custom-checkbox" data-id="{{ $pj->member_id }}">
                                        <input type="checkbox" class="custom-control-input" checked disabled name="member_id[]" value="{{ $pj->member_id }}" id="{{ $pj->member_id }}">
                                        <label class="custom-control-label" for="{{ $pj->member_id }}">{{ $pj->member()->first()->ktp()->first()->nama }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('setoran.open.pj', $penjualan->penjualan_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <form action="{{ route('setoran.open.pj.delete', [$penjualan->penjualan_id, $pj->pj_operasional_setoran_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Pj Operasional akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
