@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Absensi</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.jadwal_absensi') }}">Jadwal & Absensi</a></div>
        <div class="breadcrumb-item">Absensi</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Absensi</h2>
    <p class="section-lead">
        Kelola Absensi di tempat kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('List Absensi') }}</h4>
                    {{-- <div class="card-header-action">
                        <a href="{{ route('aset.create') }}" class="btn btn-primary">
                            Tambah
                        </a>
                    </div> --}}
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('absensi.index') }}" method="get"> @csrf
                        <div class="row mb-4">
                            <div class="col-12 col-sm-12 col-md-6 col-lg-4 mb-2">
                                <select name="status_kehadiran_id" id="status_kehadiran_id" class="custom-select">
                                    <option value="all" {{ 'all' === $statusKehadiranSelected ? 'selected' : '' }}>Semua Status</option>
                                    @foreach($statusKehadiran as $sk)
                                    <option value="{{ $sk->status_kehadiran_id }}" {{ $sk->status_kehadiran_id == $statusKehadiranSelected ? 'selected' : '' }}>{{ $sk->nama_kehadiran }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-4 mb-2">
                                <select name="jam_kerja_id" id="jam_kerja_id" class="custom-select">
                                    <option value="all" {{ 'all' == $jamKerjaSelected ? 'selected' : '' }}>Semua Jam Kerja</option>
                                    @foreach($jamKerja as $jk)
                                    <option value="{{ $jk->jam_kerja_id }}" {{ $jk->jam_kerja_id == $jamKerjaSelected ? 'selected' : '' }}>{{ $jk->nama_jam_kerja }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-4 mb-2">
                                <select name="jabatan_id" id="jabatan_id" class="custom-select">
                                    <option value="all" {{ 'all' == $jabatanSelected ? 'selected' : '' }}>Semua Jabatan</option>
                                    @foreach($jabatan as $j)
                                    <option value="{{ $j->jabatan_id }}" {{ $j->jabatan_id == $jabatanSelected ? 'selected' : '' }}>{{ $j->nama_jabatan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-4 mb-2">
                                <input type="date" name="waktu_mulai" id="waktuMulai" class="form-control" value="{{ $waktuMulai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-4 mb-2">
                                <input type="date" name="waktu_selesai" id="waktuSelesai" class="form-control" value="{{ $waktuSelesai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-6 col-lg-4 mb-2">
                                <button type="submit" class="btn btn-success btn-block">Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover table-absensi">
                            <thead>
                                <tr>
                                    <th>Status Kehadiran</th>
                                    <th>Tanggal</th>
                                    <th>Jam Kerja</th>
                                    <th>Jabatan</th>
                                    <th>Konfirmasi</th>
                                    <th>Hadir</th>
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
