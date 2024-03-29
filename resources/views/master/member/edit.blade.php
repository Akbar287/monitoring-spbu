@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Member</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('member.index') }}">Member</a></div>
        <div class="breadcrumb-item"><a href="{{ route('member.show', $member->member_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Ubah</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Ubah Member</h2>
    <p class="section-lead">
        Ubah data Member anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Ubah Member') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('member.update', $member->member_id) }}" method="post" enctype="multipart/form-data">@csrf @method('put')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="status_member_id" class="col-md-4 col-form-label text-md-end">{{ __('Status Member')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select id="status_member_id" type="text" class="custom-select @error('status_member_id') is-invalid @enderror" name="status_member_id">
                                            @foreach($statusMember as $s)
                                            <option {{ $member->status_member()->first()->status_member_id === $s->status_member_id ? 'selected' : '' }} value="{{ $s->status_member_id }}">{{ $s->nama_status }}</option>
                                            @endforeach
                                        </select>
            
                                        @error('status_member_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="nik" class="col-md-4 col-form-label text-md-end">{{ __('NIK')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="nik" type="text"
                                            class="form-control @error('nik') is-invalid @enderror" name="nik"
                                            value="{{ $member->ktp()->first()->nik }}" autocomplete="name"
                                            autofocus>
            
                                        @error('nik')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="nama" class="col-md-4 col-form-label text-md-end">{{ __('Nama')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="nama" type="text"
                                            class="form-control @error('nama') is-invalid @enderror" name="nama"
                                            value="{{ $member->ktp()->first()->nama }}" autocomplete="name"
                                            autofocus>
            
                                        @error('nama')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="jenis_kelamin" class="col-md-4 col-form-label text-md-end">{{ __('Jenis Kelamin')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select id="jenis_kelamin" type="text" class="custom-select @error('jenis_kelamin') is-invalid @enderror" name="jenis_kelamin">
                                            <option {{ $member->ktp()->first()->jenis_kelamin === 'pria' ? 'selected' : '' }} value="pria">Pria</option>
                                            <option {{ $member->ktp()->first()->jenis_kelamin === 'wanita' ? 'selected' : '' }} value="wanita">Wanita</option>
                                        </select>
            
                                        @error('jenis_kelamin')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="tempat_lahir" class="col-md-4 col-form-label text-md-end">{{ __('Tempat, Tanggal lahir')
                                        }}</label>
            
                                    <div class="col-md-6">
                                            <div class="input-group">
                                                <input id="tempat_lahir" type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" name="tempat_lahir" value="{{ $member->ktp()->first()->tempat_lahir }}" autocomplete="name" placeholder="Kota Kelahiran">
                                                <input id="tanggal_lahir" type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{ $member->ktp()->first()->tanggal_lahir }}" autocomplete="name">
                                            </div>
            
                                        @error('tempat_lahir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                        @error('tanggal_lahir')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="email" type="text" 
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ $member->email }}" autocomplete="name"
                                            autofocus>
            
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="no_hp" class="col-md-4 col-form-label text-md-end">{{ __('Nomor HP')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="no_hp" type="text" placeholder="08XXX"
                                            class="form-control @error('no_hp') is-invalid @enderror" name="no_hp"
                                            value="{{ $member->no_hp }}" autocomplete="name"
                                            autofocus>
            
                                        @error('no_hp')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="no_wa" class="col-md-4 col-form-label text-md-end">{{ __('Nomor Whatsapp')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="no_wa" type="text" placeholder="08XXX"
                                            class="form-control @error('no_wa') is-invalid @enderror" name="no_wa"
                                            value="{{ $member->no_wa }}" autocomplete="name"
                                            autofocus>
            
                                        @error('no_wa')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="username" class="col-md-4 col-form-label text-md-end">{{ __('Username')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="username" type="text" 
                                            class="form-control @error('username') is-invalid @enderror" name="username"
                                            value="{{ $member->user_login()->first()->username }}" autocomplete="name"
                                            autofocus>
            
                                        @error('username')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="password" type="text" placeholder="Masukan Password jika ingin diganti" 
                                            class="form-control @error('password') is-invalid @enderror" name="password"
                                            value="{{ old('password') }}" autocomplete="name"
                                            autofocus>
            
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="bergabung_sejak" class="col-md-4 col-form-label text-md-end">{{ __('Bergabung Sejak')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <input id="bergabung_sejak" type="date"
                                            class="form-control @error('bergabung_sejak') is-invalid @enderror" name="bergabung_sejak"
                                            value="{{ $member->bergabung_sejak }}" autocomplete="name"
                                            autofocus>
            
                                        @error('bergabung_sejak')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="verified" class="col-md-4 col-form-label text-md-end">{{ __('Verifikasi')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select id="verified" type="text" class="custom-select @error('verified') is-invalid @enderror" name="verified">
                                            <option {{ $member->ktp()->first()->verified === true ? 'selected' : '' }} value="true">Terverifikasi</option>
                                            <option {{ $member->ktp()->first()->verified === false ? 'selected' : '' }} value="false">Tidak Terverifikasi</option>
                                        </select>
            
                                        @error('verified')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mb-3 justify-content-center">
                                    <label for="is_aktif" class="col-md-4 col-form-label text-md-end">{{ __('Aktif')
                                        }}</label>
            
                                    <div class="col-md-6">
                                        <select id="is_aktif" type="text" class="custom-select @error('is_aktif') is-invalid @enderror" name="is_aktif">
                                            <option {{ $member->user_login()->first()->is_aktif === true ? 'selected' : '' }} value="true">Aktif</option>
                                            <option {{ $member->user_login()->first()->is_aktif === false ? 'selected' : '' }} value="false">Tidak Aktif</option>
                                        </select>
            
                                        @error('is_aktif')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('member.show', $member->member_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
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
