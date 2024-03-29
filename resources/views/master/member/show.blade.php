@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Member</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('member.index') }}">Member</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Member</h2>
    <p class="section-lead">
        Detail data Member anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Member') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('member.jabatan.index', $member->member_id) }}" class="btn btn-primary">
                            Jabatan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="status_member_id" class="col-md-4 col-form-label text-md-end">{{ __('Status Member')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="status_member_id" type="text" readonly
                                            class="form-control @error('status_member_id') is-invalid @enderror" name="status_member_id"
                                            value="{{ $member->status_member()->first()->nama_status }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="nik" class="col-md-4 col-form-label text-md-end">{{ __('NIK')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="nik" type="text"
                                            class="form-control @error('nik') is-invalid @enderror" name="nik" readonly
                                            value="{{ $member->ktp()->first()->nik }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="nama" class="col-md-4 col-form-label text-md-end">{{ __('Nama')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="nama" type="text"
                                            class="form-control @error('nama') is-invalid @enderror" name="nama" readonly
                                            value="{{ $member->ktp()->first()->nama }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="jenis_kelamin" class="col-md-4 col-form-label text-md-end">{{ __('Jenis Kelamin')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="jenis_kelamin" type="text"
                                            class="form-control @error('jenis_kelamin') is-invalid @enderror" name="jenis_kelamin" readonly
                                            value="{{ $member->ktp()->first()->jenis_kelamin }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tempat_lahir" class="col-md-4 col-form-label text-md-end">{{ __('Tempat, Tanggal lahir')
                                        }}</label>

                                    <div class="col-md-6">
                                            <div class="input-group">
                                                <input id="tempat_lahir" type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" name="tempat_lahir" value="{{ $member->ktp()->first()->tempat_lahir }}" readonly autocomplete="name" placeholder="Kota Kelahiran">
                                                <input id="tanggal_lahir" type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{ $member->ktp()->first()->tanggal_lahir }}" readonly autocomplete="name">
                                            </div>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="email" type="text"
                                            class="form-control @error('email') is-invalid @enderror" name="email" readonly
                                            value="{{ $member->email }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_hp" class="col-md-4 col-form-label text-md-end">{{ __('Nomor HP')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_hp" type="text" placeholder="08XXX" readonly
                                            class="form-control @error('no_hp') is-invalid @enderror" name="no_hp"
                                            value="{{ $member->no_hp }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="no_wa" class="col-md-4 col-form-label text-md-end">{{ __('Nomor Whatsapp')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="no_wa" type="text" placeholder="08XXX" readonly
                                            class="form-control @error('no_wa') is-invalid @enderror" name="no_wa"
                                            value="{{ $member->no_wa }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="username" class="col-md-4 col-form-label text-md-end">{{ __('Username')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="username" type="text" readonly
                                            class="form-control @error('username') is-invalid @enderror" name="username"
                                            value="{{ $member->user_login()->first()->username }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="bergabung_sejak" class="col-md-4 col-form-label text-md-end">{{ __('Bergabung Sejak')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="bergabung_sejak" type="date" readonly
                                            class="form-control @error('bergabung_sejak') is-invalid @enderror" name="bergabung_sejak"
                                            value="{{ $member->bergabung_sejak }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="verified" class="col-md-4 col-form-label text-md-end">{{ __('Verifikasi')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="verified" type="text"
                                            class="form-control @error('verified') is-invalid @enderror" name="verified" readonly
                                            value="{{ $member->ktp()->first()->verified == true ? 'Terverifikasi' : "Tidak Terverifikasi" }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="is_aktif" class="col-md-4 col-form-label text-md-end">{{ __('Aktif')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="is_aktif" type="text"
                                            class="form-control @error('is_aktif') is-invalid @enderror" name="is_aktif" readonly
                                            value="{{ $member->user_login()->first()->is_aktif == true ? "Aktif" : "Tidak Aktif" }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('member.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        @if(Auth::user()->member()->first()->jabatan()->first()->nama_jabatan == 'Admin')
                                        <a type="button" href="{{ route('member.edit', $member->member_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                        <form action="{{ route('member.reset_pw', $member->member_id) }}" method="POST">@csrf @method('put')
                                            <button type="submit" onclick="return confirm('Password Pegawai akan di reset ke 12345678!\n Lanjutkan?')" title="Reset Password" class="btn btn-info mx-2"><i class="fas fa-unlock"></i> Reset</button>
                                        </form>
                                        <form action="{{ route('member.destroy', $member->member_id) }}" method="POST">@csrf @method('delete')
                                            <button type="submit" onclick="return confirm('Spbu akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
