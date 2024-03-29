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
                    <form action="{{ route('profil.store_password') }}" method="post">@csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-4">
                                        <img src="{{ asset('storage/profile_img/' . Auth::user()->member()->first()->avatar) }}" alt="{{ Auth::user()->username }}" width="90%">
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-8">
                                        <div class="row mb-3 justify-content-center">
                                            <label for="password_lama" class="col-md-12 col-form-label">{{ __('Password Lama')
                                                }}</label>

                                            <div class="col-12">
                                                <div class="input-group mb-3">
                                                    <input id="password_lama" type="password" class="password form-control @error('password_lama') is-invalid @enderror" name="password_lama" value="" placeholder="Password Lama" />
                                                    <div class="input-group-append">
                                                        <button class="btn btn-primary see_password" type="button"><i class="fas fa-eye"></i></button>
                                                    </div>
                                                </div>

                                                @error('password_lama')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3 justify-content-center">
                                            <label for="password_baru" class="col-md-12 col-form-label">{{ __('Password Baru')
                                                }}</label>

                                            <div class="col-12">
                                                <div class="input-group mb-3">
                                                    <input id="password_baru" type="password" class="password form-control @error('password_baru') is-invalid @enderror" name="password_baru" value="" placeholder="Password Baru" />
                                                    <div class="input-group-append">
                                                        <button class="btn btn-primary see_password" type="button"><i class="fas fa-eye"></i></button>
                                                    </div>
                                                </div>

                                                @error('password_baru')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="row mb-3 justify-content-center">
                                            <label for="konfirmasi_password_baru" class="col-md-12 col-form-label">{{ __('Konfirmasi Password Baru')
                                                }}</label>

                                            <div class="col-12">
                                                <div class="input-group mb-3">
                                                    <input id="konfirmasi_password_baru" type="password" class="password form-control @error('konfirmasi_password_baru') is-invalid @enderror" name="konfirmasi_password_baru" value="" placeholder="Konfirmasi Password Baru" />
                                                    <div class="input-group-append">
                                                        <button class="btn btn-primary see_password" type="button"><i class="fas fa-eye"></i></button>
                                                    </div>
                                                </div>

                                                @error('konfirmasi_password_baru')
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
