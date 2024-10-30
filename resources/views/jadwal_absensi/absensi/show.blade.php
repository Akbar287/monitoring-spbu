@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Absensi</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.jadwal_absensi') }}">Jadwal & Absensi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('absensi.index') }}">Absensi</a></div>
        <div class="breadcrumb-item">Detail Absensi</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Absensi</h2>
    <p class="section-lead">
        Detail Absensi.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Absensi') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="status_kehadiran" class="col-md-4 col-form-label text-md-end">{{ __('Status kehadiran')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="status_kehadiran" type="text"
                                        class="form-control @error('status_kehadiran') is-invalid @enderror" name="status_kehadiran"
                                        value="{{ $penjadwalan->status_kehadiran()->first()->nama_kehadiran }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="tanggal" type="text"
                                        class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                        value="{{ Carbon\Carbon::createFromFormat('Y-m-d', $penjadwalan->tanggal)->format('d F Y') }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jadwal_absen_masuk" class="col-md-4 col-form-label text-md-end">{{ __('Jam Absen Masuk')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <input readonly id="jadwal_absen_masuk" type="text"
                                        class="form-control @error('jadwal_absen_masuk') is-invalid @enderror" name="jadwal_absen_masuk"
                                        value="{{ is_null($penjadwalan->start_time) ? '' : Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $penjadwalan->start_time)->format('d F Y H:i:s') }}" >
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jadwal_absen_keluar" class="col-md-4 col-form-label text-md-end">{{ __('Jam Absen Keluar')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <input readonly id="jadwal_absen_keluar" type="text"
                                        class="form-control @error('jadwal_absen_keluar') is-invalid @enderror" name="jadwal_absen_keluar"
                                        value="{{ is_null($penjadwalan->finish_time) ? '' : Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $penjadwalan->finish_time)->format('d F Y H:i:s') }}" >
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jam_kerja" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <input readonly id="jam_kerja" type="text"
                                        class="form-control @error('jam_kerja') is-invalid @enderror" name="jam_kerja"
                                        value="{{ $penjadwalan->jam_kerja()->first()->nama_jam_kerja }}" >
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama" class="col-md-4 col-form-label text-md-end">{{ __('Nama Pegawai')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama" type="text" readonly
                                        class="form-control @error('nama') is-invalid @enderror" name="nama"
                                        value="{{ $penjadwalan->member()->first()->ktp()->first()->nama }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jabatan" class="col-md-4 col-form-label text-md-end">{{ __('Jabatan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="jabatan" type="text" readonly class="form-control @error('jabatan') is-invalid @enderror" name="jabatan" value="{{ $penjadwalan->jabatan()->first()->nama_jabatan }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="hadir" class="col-md-4 col-form-label text-md-end">{{ __('Hadir')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="hadir" type="text" readonly class="form-control @error('hadir') is-invalid @enderror" name="hadir" value="{{ $penjadwalan->is_attended ? 'Hadir' : 'Belum Hadir' }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="is_confirm" class="col-md-4 col-form-label text-md-end">{{ __('Terkonfirmasi Admin')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="is_confirm" type="text" readonly class="form-control @error('is_confirm') is-invalid @enderror" name="is_confirm" value="{{ $penjadwalan->is_confirm ? 'Terkonfirmasi' : 'Belum Terkonfirmasi' }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="deskripsi" class="col-md-4 col-form-label text-md-end">{{ __('Deskripsi')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea name="deskripsi" readonly id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" cols="30" rows="10">{{ $penjadwalan->deskripsi ?? '-' }}</textarea>
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
                    <h4>{{ __('Perangkat') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="ipv4" class="col-md-4 col-form-label text-md-end">{{ __('Ip Versi 4')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="ipv4" type="text" readonly class="form-control @error('ipv4') is-invalid @enderror" name="ipv4" value="{{ $penjadwalan->devices()->first()->ipv4 ?? '-' }}">
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="type_devices" class="col-md-4 col-form-label text-md-end">{{ __('Jenis Perangkat')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="type_devices" type="text" readonly class="form-control @error('type_devices') is-invalid @enderror" name="type_devices" value="{{ $penjadwalan->devices()->first()->type_devices ?? '-' }}">
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="model_devices" class="col-md-4 col-form-label text-md-end">{{ __('Model Perangkat')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="model_devices" type="text" readonly class="form-control @error('model_devices') is-invalid @enderror" name="model_devices" value="{{ $penjadwalan->devices()->first()->model_devices ?? '-' }}">
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="os" class="col-md-4 col-form-label text-md-end">{{ __('Sistem Operasi')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="os" type="text" readonly class="form-control @error('os') is-invalid @enderror" name="os" value="{{ $penjadwalan->devices()->first()->os ?? '-' }}">
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="versi_os" class="col-md-4 col-form-label text-md-end">{{ __('Versi Sistem Operasi')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="versi_os" type="text" readonly class="form-control @error('versi_os') is-invalid @enderror" name="versi_os" value="{{ $penjadwalan->devices()->first()->versi_os ?? '-' }}">
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="browser" class="col-md-4 col-form-label text-md-end">{{ __('Browser')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="browser" type="text" readonly class="form-control @error('browser') is-invalid @enderror" name="browser" value="{{ $penjadwalan->devices()->first()->browser ?? '-' }}">
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="versi_browser" class="col-md-4 col-form-label text-md-end">{{ __('Versi Browser')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="versi_browser" type="text" readonly class="form-control @error('versi_browser') is-invalid @enderror" name="versi_browser" value="{{ $penjadwalan->devices()->first()->versi_browser ?? '-' }}">
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
                    <h4>{{ __('Gambar Selfie') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="gambar" class="col-md-4 col-form-label text-md-end">{{
                                    __('Gambar Selfie')
                                }}</label>

                                <div class="col-md-6">
                                    @if(is_null($penjadwalan->image_selfie()->first()))
                                    <input id="gambar_selfie" type="text" readonly
                                        class="form-control @error('gambar_selfie') is-invalid @enderror" name="gambar_selfie"
                                        value="{{ 'Tidak Ada Gambar Selfie' }}">
                                    @else
                                    <img src="{{ asset('storage/absensi/' . $penjadwalan->image_selfie()->first()->nama_file) }}" alt="{{ $penjadwalan->image_selfie()->first()->nama_file }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto">
                                    @endif
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
                    <h4>{{ __('Lokasi') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="lokasi" class="col-md-4 col-form-label text-md-end">{{ __('Lokasi')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="lokasi" type="text" readonly class="form-control @error('lokasi') is-invalid @enderror" name="lokasi" value="{{ !is_null($penjadwalan->location()->first()) ? $penjadwalan->location()->first()->lattitude . ' - '. $penjadwalan->location()->first()->longitude : '-' }}">
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="gmt" class="col-md-4 col-form-label text-md-end">{{ __('Zona Waktu')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="gmt" type="text" readonly class="form-control @error('gmt') is-invalid @enderror" name="gmt" value="{{ !is_null($penjadwalan->location()->first()) ? $penjadwalan->location()->first()->gmt : '-' }}">
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="deskripsi" class="col-md-4 col-form-label text-md-end">{{ __('Deskripsi')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea name="deskripsi" readonly id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" cols="30" rows="10">{{ !is_null($penjadwalan->location()->first()) ? $penjadwalan->location()->first()->deskripsi : '-' }}</textarea>
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
                                    <a type="button" href="{{ route('absensi.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('absensi.edit', $penjadwalan->penjadwalan_id) }}" class="btn mr-2 btn-success"><i class="fas fa-pen"></i> Ubah Status</a>
                                    @if($penjadwalan->is_confirm)
                                    <form action="{{ route('absensi.unconfirm', $penjadwalan->penjadwalan_id) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Absensi akan di Batalkan konfirmasi!\n Lanjutkan?')" title="Unconfirm" class="btn btn-danger"><i class="fas fa-times"></i> Batalkan Konfirmasi</button>
                                    </form>
                                    @else
                                    <form action="{{ route('absensi.confirm', $penjadwalan->penjadwalan_id) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Absensi akan dikonfirmasi!\n Lanjutkan?')" title="Confirm" class="btn btn-success"><i class="fas fa-check"></i> Konfirmasi</button>
                                    </form>
                                    @endif
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
