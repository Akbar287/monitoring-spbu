@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Upload Jadwal Kerja</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.jadwal_absensi') }}">Jadwal & Absensi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('jadwal-kerja.index') }}">Penjadwalan</a></div>
        <div class="breadcrumb-item">Upload</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Hapus Data & Upload Jadwal Kerja</h2>
    <p class="section-lead">
        Hapus Data Jadwal dan Upload Jadwal Kerja.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Hapus Data Jadwal') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <form action="{{ route('jadwal-kerja.upload_file.delete') }}" method="post" enctype="multipart/form-data"> @csrf @method('delete')
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-6 mb-2">
                                        <input type="date" name="waktu_mulai" id="waktuMulai" class="form-control" value="{{ $waktuMulai }}" />
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 mb-2">
                                        <input type="date" name="waktu_selesai" id="waktuSelesai" class="form-control" value="{{ $waktuSelesai }}" />
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 mb-2">
                                        <select name="jam_kerja" id="jam_kerja" class="custom-select" @if($jamKerja->count() == 0) disabled @endif>
                                            @if($jamKerja->count() > 0)
                                            <option value="all" {{ is_null($jamKerjaSelected) ? ('all' == $jamKerja ? 'selected' : '') : '' }}>Semua Jam Kerja</option>
                                            @foreach($jamKerja as $s)
                                            <option value="{{ $s->jam_kerja_id }}" {{ is_null($jamKerjaSelected) ? ($s->jam_kerja_id == $jamKerja ? 'selected' : '') : '' }}>{{ $s->nama_jam_kerja }}</option>
                                            @endforeach
                                            @else
                                            <option value="0">Jam Kerja tidak ditemukan</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-6 mb-2">
                                        <select name="member" id="member" class="custom-select" @if($member->count() == 0) disabled @endif>
                                            @if($member->count() > 0)
                                            <option value="all" {{ is_null($memberSelected) ? ('all' == $member ? 'selected' : '') : '' }}>Semua Pegawai</option>
                                            @foreach($member as $s)
                                            <option value="{{ $s->member_id }}" {{ is_null($memberSelected) ? ($s->member_id == $member ? 'selected' : '') : '' }}>{{ $s->nama }}</option>
                                            @endforeach
                                            @else
                                            <option value="0">Pegawai tidak ditemukan</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-danger"><i class="fas fa-eraser"></i> Hapus Data</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Upload Jadwal Kerja') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <form action="{{ route('jadwal-kerja.upload_file.store') }}" method="post" enctype="multipart/form-data"> @csrf
                                <div class="row mb-3 justify-content-center">
                                    <label for="file" class="col-md-4 col-form-label text-md-end">{{
                                        __('File')
                                        }}</label>

                                    <div class="col-md-6">
                                        <div class="custom-file">
                                            <input type="file" name="file" class="custom-file-input" id="file">
                                            <label class="custom-file-label" for="file">Pilih File</label>
                                        </div>

                                        @error('file')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('jadwal-kerja.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <a type="button" target="_blank" href="{{ route('jadwal-kerja.upload_file.template') }}" class="btn btn-info mr-2"><i class="fas fa-file"></i> Unduh Template</a>
                                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Upload File</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
