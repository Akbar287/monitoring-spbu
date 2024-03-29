@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Nozzle</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('nozzle.index') }}">Nozzle</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Nozzle</h2>
    <p class="section-lead">
        Detail data Nozzle anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Nozzle') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="nozzle_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Nozzle')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nozzle_id" type="text" readonly
                                        class="form-control @error('nozzle_id') is-invalid @enderror" name="nozzle_id"
                                        value="{{ $nozzle->nozzle_id }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="dispenser_id" class="col-md-4 col-form-label text-md-end">{{ __('Dispenser')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="dispenser_id" type="text"
                                        class="form-control @error('dispenser_id') is-invalid @enderror" name="dispenser_id"
                                        value="{{ $nozzle->dispenser()->first()->nama_dispenser }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="produk_id" type="text"
                                        class="form-control @error('produk_id') is-invalid @enderror" name="produk_id"
                                        value="{{ $nozzle->produk()->first()->nama_produk }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_nozzle" class="col-md-4 col-form-label text-md-end">{{ __('Nama nozzle')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_nozzle" type="text" readonly
                                        class="form-control @error('nama_nozzle') is-invalid @enderror" name="nama_nozzle"
                                        value="{{ $nozzle->nama_nozzle }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="is_aktif" class="col-md-4 col-form-label text-md-end">{{ __('Aktifkan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="is_aktif" type="text" readonly
                                        class="form-control @error('is_aktif') is-invalid @enderror" name="is_aktif"
                                        value="{{ $nozzle->is_aktif == true ? 'Aktif' : 'Tidak Aktif' }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="" />{{ $nozzle->keterangan }}</textarea>

                                    @error('keterangan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('nozzle.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('nozzle.edit', $nozzle->nozzle_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('nozzle.destroy', $nozzle->nozzle_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Nozzle akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
