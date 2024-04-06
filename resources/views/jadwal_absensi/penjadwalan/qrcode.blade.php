@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Qr Code</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.jadwal_absensi') }}">Jadwal & Absensi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('jadwal-kerja.index') }}">Penjadwalan</a></div>
        <div class="breadcrumb-item">Qr Code</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Qr Code</h2>
    <p class="section-lead">
        Qr Code.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
        @if(session('msg')){!! session('msg') !!} @endif
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Filter QR Code') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('jadwal-kerja.qr') }}" method="get" enctype="multipart/form-data"> @csrf
                        <div class="row">
                            <div class="col-12 col-sm-12 col-md-3 mb-2">
                                <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ $tanggal }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 mb-2">
                                <select name="status_kehadiran_id" id="status_kehadiran_id" class="custom-select" @if($statusKehadiran->count() == 0) disabled @endif>
                                    @if($statusKehadiran->count() > 0)
                                    <option value="all" {{ is_null($statusKehadiranSelected) ? ('all' == $statusKehadiranSelected ? 'selected' : '') : '' }}>Semua Status Kehadiran</option>
                                    @foreach($statusKehadiran as $s)
                                    <option value="{{ $s->status_kehadiran_id }}" {{ !is_null($statusKehadiranSelected) ? ($s->status_kehadiran_id == $statusKehadiranSelected ? 'selected' : '') : '' }}>{{ $s->nama_kehadiran }}</option>
                                    @endforeach
                                    @else
                                    <option value="0">Status kehadiran tidak ditemukan</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 mb-2">
                                <select name="member" id="member" class="custom-select" @if($member->count() == 0) disabled @endif>
                                    @if($member->count() > 0)
                                    <option value="all" {{ is_null($memberSelected) ? ('all' == $member ? 'selected' : '') : '' }}>Semua Pegawai</option>
                                    @foreach($member as $s)
                                    <option value="{{ $s->member_id }}" {{ !is_null($memberSelected) ? ($s->member_id == $memberSelected ? 'selected' : '') : '' }}>{{ $s->nama }}</option>
                                    @endforeach
                                    @else
                                    <option value="0">Pegawai tidak ditemukan</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 mb-2">
                                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-filter"></i> Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row justify-content-center">
                @if($data->count() > 0)
                @foreach($data as $d)
                <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-3">
                    <div class="card" style="overflow-x:scroll">
                        <div class="card-header">
                            <h4>{{ __($d->nama) }}</h4>
                        </div>
                        <div class="card-body">
                            {{ QrCode::size(150)->generate($d->barcode) }}
                        </div>
                        <div class="card-footer">
                            <table class="table-hover">
                                <tbody>
                                    <tr>
                                        <th>Tanggal / Jam Kerja</th>
                                        <td>{{ Carbon\Carbon::createFromFormat('Y-m-d', $d->tanggal)->format('d F Y') . ' (' .$d->nama_jam_kerja . ')' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Jabatan</th>
                                        <td>{{ $d->nama_jabatan }}</td>
                                    </tr>
                                    <tr>
                                        <th>Validasi Akhir</th>
                                        <td>{{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $d->finish)->diffForHumans() }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="col-12">
                    <div class="card-card-primary">
                        <div class="card-body">
                            <p>TIdak ada QR Code pada Selang waktu terpilih</p>
                        </div>
                    </div>
                </div>
                @endif
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
                                    <a type="button" href="{{ route('jadwal-kerja.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
