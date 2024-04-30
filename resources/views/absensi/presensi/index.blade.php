@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Kehadiran</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item">Kehadiran</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Kehadiran</h2>
    <p class="section-lead">
        Kelola Kehadiran di tempat kerja.
    </p>
    <div class="row justify-content-center">
        @if($ongoing)
        <div class="col-md-12">
            <div class="card author-box card-primary">
                <div class="card-body">
                    <div class="author-box-left">
                        {{ QrCode::size(100)->generate($ongoing->random_string_barcode()->where('is_done', true)->where('is_active', false)->first()->barcode ?? '-') }}
                        <div class="clearfix"></div>
                        <a href="{{ route('kehadiran.show', $ongoing->penjadwalan_id) }}" class="btn btn-primary mt-3">Absen Keluar</a>
                    </div>
                    <div class="author-box-details">
                        <div class="author-box-name">
                            {{ $ongoing->member()->first()->ktp()->first()->nama }}
                        </div>
                        <div class="author-box-job">{{ Carbon\Carbon::createFromFormat('Y-m-d', $ongoing->tanggal)->format('d F Y') . ' ('. $ongoing->jam_kerja()->first()->nama_jam_kerja .')' }}</div>
                        <div class="author-box-description">
                            <p>Anda sudah absen masuk di jam {{ $ongoing->start_time }} dan jangan lupa untuk melakukan absen keluar saat selesai bekerja. absen keluar juga penting untuk menutup catatan pekerjaan yang sudah dilakukan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Jadwal kerja Anda') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <form action="{{ route('menu.kehadiran') }}" method="get"> @csrf
                                <div class="row mb-4">
                                    <div class="col-12 col-sm-12 col-md-5">
                                        <input type="date" name="waktu_mulai" id="waktuMulai" class="form-control" value="{{ $waktuMulai }}" />
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-5">
                                        <input type="date" name="waktu_selesai" id="waktuSelesai" class="form-control" value="{{ $waktuSelesai }}" />
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-2">
                                        <button type="submit" class="btn btn-success btn-block">Filter</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-jadwal-kerja">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th>Jam Kerja</th>
                                            <th>Nozzle</th>
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
    </div>
</div>
@endsection
