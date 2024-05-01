@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Aset</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('aset.index') }}">Aset</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Aset</h2>
    <p class="section-lead">
        Detail data Aset anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Aset') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('aset.file.index', $aset->aset_id) }}" class="btn btn-primary">
                            File
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="aset_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Aset')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="aset_id" type="text"
                                        class="form-control @error('aset_id') is-invalid @enderror" name="aset_id"
                                        value="{{ $aset->aset_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="spbu_id" type="text"
                                        class="form-control @error('spbu_id') is-invalid @enderror" name="spbu_id"
                                        value="{{ $aset->spbu()->first()->nama_spbu }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_aset" class="col-md-4 col-form-label text-md-end">{{ __('Nama Aset')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_aset" type="text" readonly
                                        class="form-control @error('nama_aset') is-invalid @enderror" name="nama_aset"
                                        value="{{ $aset->nama_aset }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Jumlah')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="jumlah" type="text" readonly
                                        class="form-control @error('jumlah') is-invalid @enderror" name="jumlah"
                                        value="{{ $aset->jumlah }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal_digunakan" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal Digunakan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal_digunakan" type="date" readonly
                                        class="form-control @error('tanggal_digunakan') is-invalid @enderror" name="tanggal_digunakan"
                                        value="{{ $aset->tanggal_digunakan }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="harga" class="col-md-4 col-form-label text-md-end">{{ __('Harga')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Rp
                                            </div>
                                        </div>
                                        <input id="harga" type="currency" readonly
                                            class="form-control @error('harga') is-invalid @enderror" name="harga"
                                            value="{{ number_format($aset->harga, 6, '.', ',') }}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="penyusutan_per_hari" class="col-md-4 col-form-label text-md-end">{{ __('Penyusutan Per Hari')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Rp
                                            </div>
                                        </div>
                                        <input id="penyusutan_per_hari" type="currency" readonly
                                            class="form-control @error('penyusutan_per_hari') is-invalid @enderror" name="penyusutan_per_hari"
                                            value="{{ number_format($aset->penyusutan_per_hari, 6, '.', ',')}}" autocomplete="name"
                                            autofocus>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="is_digunakan" class="col-md-4 col-form-label text-md-end">{{ __('Digunakan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="is_digunakan" type="currency" readonly
                                            class="form-control @error('is_digunakan') is-invalid @enderror" name="is_digunakan"
                                            value="{{ $aset->is_digunakan == true ? 'Aktif' : "Tidak Aktif"}}" autocomplete="name"
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
                                        autofocus>{{ $aset->keterangan }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('aset.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('aset.edit', $aset->aset_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('aset.destroy', $aset->aset_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Aset akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
