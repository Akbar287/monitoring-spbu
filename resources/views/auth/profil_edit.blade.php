@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Profil</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item">Profil</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Profil</h2>
    <p class="section-lead">
        Profil anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Profil') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('profil.store') }}" method="post">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-4">
                                        <img src="{{ asset('storage/profile_img/' . Auth::user()->member()->first()->avatar) }}" alt="{{ Auth::user()->username }}" width="90%">
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-8">
                                        <div class="row mb-3 justify-content-center">
                                            <label for="nik" class="col-md-12 col-form-label">{{ __('NIK')
                                                }}</label>

                                            <div class="col-12">
                                                <input id="nik" type="text" class="form-control @error('nik') is-invalid @enderror" name="nik" value="{{ Auth::user()->member()->first()->ktp()->first()->nik }}" />

                                                @error('nik')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3 justify-content-center">
                                            <label for="nama" class="col-md-12 col-form-label">{{ __('Nama')
                                                }}</label>

                                            <div class="col-12">
                                                <input id="nama" type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ Auth::user()->member()->first()->ktp()->first()->nama }}" />

                                                @error('nama')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3 justify-content-center">
                                            <label for="ttl" class="col-md-12 col-form-label">{{ __('TTL')
                                                }}</label>
                                            <div class="col-12">
                                                <div class="input-group">
                                                    <input id="tempat_lahir" type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" name="tempat_lahir" value="{{ Auth::user()->member()->first()->ktp()->first()->tempat_lahir }}" />
                                                    <input id="tanggal_lahir" type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{ Auth::user()->member()->first()->ktp()->first()->tanggal_lahir }}" />
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
                                            <label for="jenis_kelamin" class="col-md-12 col-form-label">{{ __('Jenis Kelamin')
                                                }}</label>

                                            <div class="col-12">
                                                <select name="jenis_kelamin" id="jenis_kelamin" class="custom-select" name="jenis_kelamin" >
                                                    <option {{ Auth::user()->member()->first()->ktp()->first()->jenis_kelamin == 'pria' ? 'selected' : '' }} value="pria">Pria</option>
                                                    <option {{ Auth::user()->member()->first()->ktp()->first()->jenis_kelamin == 'wanita' ? 'selected' : '' }} value="wanita">Wanita</option>
                                                </select>

                                                @error('jenis_kelamin')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3 justify-content-center">
                                            <label for="email" class="col-md-12 col-form-label">{{ __('Email')
                                                }}</label>

                                            <div class="col-12">
                                                <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ Auth::user()->member()->first()->email }}" />

                                                @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3 justify-content-center">
                                            <label for="no_hp" class="col-md-12 col-form-label">{{ __('No. HP')
                                                }}</label>

                                            <div class="col-12">
                                                <input id="no_hp" type="text" class="form-control @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ Auth::user()->member()->first()->no_hp }}" />

                                                @error('no_hp')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3 justify-content-center">
                                            <label for="no_wa" class="col-md-12 col-form-label">{{ __('No. Wa')
                                                }}</label>

                                            <div class="col-12">
                                                <input id="no_wa" type="text" class="form-control @error('no_wa') is-invalid @enderror" name="no_wa" value="{{ Auth::user()->member()->first()->no_wa }}" />

                                                @error('no_wa')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3 justify-content-center">
                                            <label for="username" class="col-md-12 col-form-label">{{ __('Username')
                                                }}</label>

                                            <div class="col-12">
                                                <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ Auth::user()->username }}" />


                                                @error('username')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                <a type="button" href="{{ route('profil') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                <button type="submit" class="btn btn-success mr-2"><i class="fas fa-pen"></i> Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
