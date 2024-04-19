@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Absensi</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.jadwal_absensi') }}">Jadwal & Absensi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('absensi.index') }}">Absensi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('absensi.show', $penjadwalan->penjadwalan_id) }}">Detail Absensi</a></div>
        <div class="breadcrumb-item">Detail Absensi</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Absensi</h2>
    <p class="section-lead">
        Detail Absensi.
    </p>
    <form action="{{ route('absensi.update', $penjadwalan->penjadwalan_id) }}" method="post"> @csrf @method('put')
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Detail Absensi') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="status_kehadiran" class="col-md-4 col-form-label text-md-end">{{ __('Status kehadiran')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select name="status_kehadiran_id" id="status_kehadiran_id" class="custom-select">
                                            @foreach($statusKehadiran as $sk)
                                            <option value="{{ $sk->status_kehadiran_id }}" {{ $sk->status_kehadiran_id === $penjadwalan->status_kehadiran_id ? 'selected' : '' }}>{{ $sk->nama_kehadiran }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Pilihan') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('absensi.show', $penjadwalan->penjadwalan_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button type="submit" class="btn btn-success mx-2"><i class="fas fa-save"></i> Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
