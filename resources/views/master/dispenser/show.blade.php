@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Dispenser</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('dispenser.index') }}">Dispenser</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Dispenser</h2>
    <p class="section-lead">
        Detail data Dispenser anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Dispenser') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="dispenser_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Dispenser')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="dispenser_id" type="text" readonly
                                        class="form-control @error('dispenser_id') is-invalid @enderror" name="dispenser_id"
                                        value="{{ $dispenser->dispenser_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="payung_id" class="col-md-4 col-form-label text-md-end">{{ __('Payung')}}</label>
        
                                <div class="col-md-6">
                                    <input readonly id="payung_id" type="text"
                                        class="form-control @error('payung_id') is-invalid @enderror" name="payung_id"
                                        value="{{ $dispenser->payung()->first()->nama_payung }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_dispenser" class="col-md-4 col-form-label text-md-end">{{ __('Nama Dispenser')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="nama_dispenser" type="text" readonly
                                        class="form-control @error('nama_dispenser') is-invalid @enderror" name="nama_dispenser"
                                        value="{{ $dispenser->nama_dispenser }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="is_aktif" class="col-md-4 col-form-label text-md-end">{{ __('Aktifkan')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input readonly id="is_aktif" type="text" readonly
                                        class="form-control @error('is_aktif') is-invalid @enderror" name="is_aktif"
                                        value="{{ $dispenser->is_aktif == true ? 'Aktif' : 'Tidak Aktif' }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="" autocomplete="name"
                                        autofocus>{{ $dispenser->keterangan }}</textarea>
        
                                    @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('dispenser.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('dispenser.edit', $dispenser->dispenser_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('dispenser.destroy', $dispenser->dispenser_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Dispenser akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                    </form>
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
