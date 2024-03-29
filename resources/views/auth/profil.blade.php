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
        @php $role = Auth::user()->member()->first()->jabatan()->select('jabatan.nama_jabatan')->addSelect('level.nama_level')->addSelect('divisi.nama_divisi')->join('level', 'level.level_id', 'jabatan.level_id')->join('divisi', 'divisi.divisi_id', 'level.divisi_id')->first() @endphp
        Divisi <b>{{ $role->nama_divisi }}</b> Level <b>{{ $role->nama_level }}</b> Jabatan <b>{{ $role->nama_jabatan }}</b>
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Profil') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-0">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-12 col-sm-12 col-md-4">
                                            <img src="{{ asset('storage/profile_img/' . Auth::user()->member()->first()->avatar) }}" alt="{{ Auth::user()->username }}" width="90%">
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-8">
                                            <div class="row mb-3 justify-content-center">
                                                <label for="nik" class="col-md-12 col-form-label">{{ __('NIK')
                                                    }}</label>

                                                <div class="col-12">
                                                    <input id="nik" type="text" readonly class="form-control" name="nik" value="{{ Auth::user()->member()->first()->ktp()->first()->nik }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-3 justify-content-center">
                                                <label for="nama" class="col-md-12 col-form-label">{{ __('Nama')
                                                    }}</label>

                                                <div class="col-12">
                                                    <input id="nama" type="text" readonly class="form-control" name="nama" value="{{ Auth::user()->member()->first()->ktp()->first()->nama }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-3 justify-content-center">
                                                <label for="ttl" class="col-md-12 col-form-label">{{ __('TTL')
                                                    }}</label>
                                                <div class="col-12">
                                                    <div class="input-group">
                                                        <input id="ttl" type="text" readonly class="form-control" name="ttl" value="{{ Auth::user()->member()->first()->ktp()->first()->tempat_lahir }}" />
                                                        <input id="ttl" type="text" readonly class="form-control" name="ttl" value="{{ Carbon\Carbon::createFromFormat('Y-m-d', Auth::user()->member()->first()->ktp()->first()->tanggal_lahir)->format('d F Y') }}" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3 justify-content-center">
                                                <label for="jenis_kelamin" class="col-md-12 col-form-label">{{ __('Jenis Kelamin')
                                                    }}</label>

                                                <div class="col-12">
                                                    <input id="jenis_kelamin" readonly type="text" class="form-control" name="jenis_kelamin" value="{{ Auth::user()->member()->first()->ktp()->first()->jenis_kelamin }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-3 justify-content-center">
                                                <label for="email" class="col-md-12 col-form-label">{{ __('Email')
                                                    }}</label>

                                                <div class="col-12">
                                                    <input id="email" type="text" readonly class="form-control" name="email" value="{{ Auth::user()->member()->first()->email }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-3 justify-content-center">
                                                <label for="no_hp" class="col-md-12 col-form-label">{{ __('No. HP')
                                                    }}</label>

                                                <div class="col-12">
                                                    <input id="no_hp" type="text" readonly class="form-control" name="no_hp" value="{{ Auth::user()->member()->first()->no_hp }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-3 justify-content-center">
                                                <label for="no_wa" class="col-md-12 col-form-label">{{ __('No. Wa')
                                                    }}</label>

                                                <div class="col-12">
                                                    <input id="no_wa" type="text" readonly class="form-control" name="no_wa" value="{{ Auth::user()->member()->first()->no_wa }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-3 justify-content-center">
                                                <label for="username" class="col-md-12 col-form-label">{{ __('Username')
                                                    }}</label>

                                                <div class="col-12">
                                                    <input id="username" type="text" readonly class="form-control" name="username" value="{{ Auth::user()->username }}" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('home') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('profil.edit_password') }}" class="btn btn-info mr-2"><i class="fas fa-lock"></i> Ubah Password</a>
                                    <a type="button" href="{{ route('profil.edit') }}" class="btn btn-success mr-2"><i class="fas fa-pen"></i> Ubah</a>
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
