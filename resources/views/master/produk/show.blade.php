@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Produk</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.master') }}">Master</a></div>
        <div class="breadcrumb-item"><a href="{{ route('produk.index') }}">Produk</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Produk</h2>
    <p class="section-lead">
        Detail data Produk anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Produk') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Produk')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="produk_id" type="text" readonly
                                        class="form-control @error('produk_id') is-invalid @enderror" name="produk_id"
                                        value="{{ $produk->produk_id }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="nama_divisi" type="text"
                                        class="form-control @error('nama_divisi') is-invalid @enderror" name="nama_divisi"
                                        value="{{ $produk->spbu()->first()->nama_spbu }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_produk" class="col-md-4 col-form-label text-md-end">{{ __('Nama Produk')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_produk" type="text" readonly
                                        class="form-control @error('nama_produk') is-invalid @enderror" name="nama_produk"
                                        value="{{ $produk->nama_produk }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="singkatan" class="col-md-4 col-form-label text-md-end">{{ __('Singkatan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="singkatan" type="text" readonly
                                        class="form-control @error('singkatan') is-invalid @enderror" name="singkatan"
                                        value="{{ $produk->singkatan }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_jenis" class="col-md-4 col-form-label text-md-end">{{ __('Nama Jenis Produk')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="nama_jenis" type="text" readonly
                                        class="form-control @error('nama_jenis') is-invalid @enderror" name="nama_jenis"
                                        value="{{ $produk->detail_produk()->first()->nama_jenis }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="oktan" class="col-md-4 col-form-label text-md-end">{{ __('Oktan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="oktan" type="text" readonly
                                        class="form-control @error('oktan') is-invalid @enderror" name="oktan"
                                        value="{{ $produk->detail_produk()->first()->oktan }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="is_aktif" class="col-md-4 col-form-label text-md-end">{{ __('Aktifkan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="is_aktif" type="text" readonly
                                        class="form-control @error('is_aktif') is-invalid @enderror" name="is_aktif"
                                        value="{{ $produk->is_aktif == true ? 'Aktif' : 'Tidak Aktif' }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('produk.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('produk.edit', $produk->produk_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('produk.destroy', $produk->produk_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Produk akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
