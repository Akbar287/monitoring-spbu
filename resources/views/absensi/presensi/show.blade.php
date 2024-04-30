@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Jadwal Kerja</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.kehadiran') }}">Kehadiran</a></div>
        <div class="breadcrumb-item">Jadwal Kerja</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Jadwal Kerja</h2>
    <p class="section-lead">
        Jadwal Kerja anda
    </p>
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Jadwal kerja Anda') }}</h4>
                </div>
                <div class="card-body">
                    <input type="hidden" name="jenis_jam" id="jenis_jam" value="{{ !is_null($penjadwalan->start_time) && is_null($penjadwalan->finish_time) ? 'out' : 'in' }}">
                    @if(!is_null($penjadwalan->start_time) && is_null($penjadwalan->finish_time))
                    <div class="row mt-4">
                        <div class="col-12 col-lg-8 offset-lg-2">
                            <div class="wizard-steps">
                                <div class="wizard-step close-one wizard-step-active">
                                    <div class="wizard-step-icon">
                                        <i class="far fa-clock"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Informasi Absen Masuk
                                    </div>
                                </div>
                                <div class="wizard-step close-two">
                                    <div class="wizard-step-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Absen Keluar
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form class="wizard-content close-one mt-2">
                        <div class="wizard-pane">
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Nama</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="nama" readonly value="{{ $penjadwalan->member()->first()->ktp()->select('nama')->first()->nama }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Jabatan</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="jabatan" readonly value="{{ $penjadwalan->jabatan()->select('nama_jabatan')->first()->nama_jabatan }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Tanggal</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="jabatan" readonly value="{{ Carbon\Carbon::createFromFormat('Y-m-d', $penjadwalan->tanggal)->format('d F Y') }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Jam Kerja</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="jam_kerja" readonly value="{{ $penjadwalan->jam_kerja()->select('nama_jam_kerja')->first()->nama_jam_kerja }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Jam Masuk</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="jam_masuk" readonly value="{{ $penjadwalan->start_time }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 text-md-right text-left mt-2">Deskripsi</label>
                                <div class="col-lg-4 col-md-6">
                                    <textarea class="form-control" readonly name="deskripsi">{{ $penjadwalan->deskripsi }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-12 col-sm-12 text-center">
                                    <button type="button" title="complete" class="btn btn-icon icon-right btn-primary close-btn-from-one-to-two">Absen Keluar <i class="fas fa-check"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form class="wizard-content close-complete mt-2 d-none">
                        <div class="wizard-pane">
                            <div class="row justify-content-center align-item-center">
                                <div class="col-12 col-sm-12 col-md-8 col-lg-6">
                                    <div class="text-center">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="80"  height="80"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor" style="color: green;"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                        <h5 class="mb-2">Anda telah berhasil Absen Keluar</h5>
                                        <p class="mb-10">Silahkan kembali ke menu <a href="{{ route('menu.kehadiran') }}">sebelumnya</a>, atau keluar aplikasi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @else
                    <div class="row mt-4">
                        <div class="col-12 col-lg-8 offset-lg-2">
                            <div class="wizard-steps">
                                <div class="wizard-step one wizard-step-active">
                                    <div class="wizard-step-icon">
                                        <i class="far fa-clock"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Informasi Jadwal
                                    </div>
                                </div>
                                <div class="wizard-step two">
                                    <div class="wizard-step-icon">
                                        <i class="fas fa-map-marker"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Lokasi dan Perangkat
                                    </div>
                                </div>
                                <div class="wizard-step three">
                                    <div class="wizard-step-icon">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <div class="wizard-step-label">
                                        Scan QR Code
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form class="wizard-content one mt-2">
                        <div class="wizard-pane">
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Nama</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="nama" readonly value="{{ $penjadwalan->member()->first()->ktp()->select('nama')->first()->nama }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Jabatan</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="jabatan" readonly value="{{ $penjadwalan->jabatan()->select('nama_jabatan')->first()->nama_jabatan }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Tanggal</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="jabatan" readonly value="{{ Carbon\Carbon::createFromFormat('Y-m-d', $penjadwalan->tanggal)->format('d F Y') }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Jam Kerja</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="jam_kerja" readonly value="{{ $penjadwalan->jam_kerja()->select('nama_jam_kerja')->first()->nama_jam_kerja }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 text-md-right text-left mt-2">Deskripsi</label>
                                <div class="col-lg-4 col-md-6">
                                    <textarea class="form-control" readonly name="deskripsi">{{ $penjadwalan->deskripsi }}</textarea>
                                </div>
                            </div>
                            @if($ongoing)
                            <div class="row">
                                <div class="col-md-4"></div>
                                <div class="col-lg-4 col-md-6 text-center">
                                    <div class="alert alert-danger">Anda perlu absen keluar terlebih dahulu</div>
                                </div>
                            </div>
                            @endif
                            <div class="form-group row">
                                <div class="col-md-4"></div>
                                <div class="col-lg-4 col-md-6 text-right">
                                    <button type="button" title="next" @if($ongoing) disabled @endif class="btn btn-icon icon-right btn-primary btn-from-one-to-two">Berikutnya <i class="fas fa-arrow-right"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form class="wizard-content two mt-2 d-none">
                        <div class="wizard-pane">
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Jenis Perangkat & Browser</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="nama" readonly value="{{ $utility['typeDevice'] . ' ('. $utility['browser'] .')' }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Sistem Operasi</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="jabatan" readonly value="{{ $utility['osAndVersionOs'] }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Alamat IP</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="jabatan" readonly value="{{ $utility['ip'] }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Kota</label>
                                <div class="col-lg-4 col-md-6">
                                    <input type="text" name="jam_kerja" readonly value="{{ $utility['city'] }}" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4"></div>
                                <div class="col-lg-4 col-md-6 text-right">
                                    <button type="button" title="next" class="btn btn-icon icon-right btn-danger btn-two-to-one"><i class="fas fa-arrow-left"></i> Sebelumnya</button>
                                    <button type="button" title="next" class="btn btn-icon icon-right btn-primary btn-two-to-three">Berikutnya <i class="fas fa-arrow-right"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form class="wizard-content three mt-2 d-none">
                        <div class="wizard-pane">
                            <div class="form-group row align-items-center">
                                <div class="col-md-4"></div>
                                <div class="col-lg-4 col-md-6">
                                    <div id="video-container example-style-2 text-md-right">
                                        <video id="qr-video" style="width: 100%!important;"></video>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Kamera</label>
                                <div class="col-lg-4 col-md-6">
                                    <select name="cam-list" id="cam-list" class="custom-select"></select>
                                </div>
                            </div>

                            <div class="form-group row align-items-center">
                                <label class="col-md-4 text-md-right text-left">Pilihan</label>
                                <div class="col-lg-4 col-md-6">
                                    <button type="button" class="btn btn-primary" id="start_cam">Mulai Kamera</button>
                                    <button type="button" class="btn btn-primary" disabled id="stop_cam">Matikan Kamera</button>
                                    <div class="alert alert-info waiting-alert d-none">Loading ...</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4"></div>
                                <div class="col-lg-4 col-md-6 text-right">
                                    <button type="button" title="next" class="btn btn-icon icon-right btn-danger btn-three-to-two"><i class="fas fa-arrow-left"></i> Sebelumnya</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form class="wizard-content complete mt-2 d-none">
                        <div class="wizard-pane">
                            <div class="row justify-content-center align-item-center">
                                <div class="col-12 col-sm-12 col-md-8 col-lg-6">
                                    <div class="text-center">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="80"  height="80"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor" style="color: green;"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                        <h5 class="mb-2">Anda telah berhasil Absen Masuk</h5>
                                        <p class="mb-10">Silahkan kembali ke menu <a href="{{ route('menu.kehadiran') }}">sebelumnya</a>, atau keluar aplikasi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
