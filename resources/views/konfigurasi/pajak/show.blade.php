@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Pajak</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.konfigurasi') }}">Konfigurasi</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pajak_pembelian_lib.index') }}">Pajak</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Pajak</h2>
    <p class="section-lead">
        Detail data Pajak anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Pajak') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="ppn" class="col-md-4 col-form-label text-md-end">{{ __('PPN')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="ppn" readonly type="text" class="form-control thousand-style" name="ppn" value="{{ $pajak->ppn }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="pph" class="col-md-4 col-form-label text-md-end">{{ __('PPH')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="pph" readonly type="text" class="form-control thousand-style" name="pph" value="{{ $pajak->pph }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="ppbkb" class="col-md-4 col-form-label text-md-end">{{ __('PPBKB')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="ppbkb" readonly type="text" class="form-control thousand-style" name="ppbkb" value="{{ $pajak->ppbkb }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="is_aktif" class="col-md-4 col-form-label text-md-end">{{ __('Aktifkan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="is_aktif" readonly type="text" class="form-control" name="is_aktif" value="{{ $pajak->is_aktif ? 'Aktifkan' : 'Jangan Aktifkan' }}" />
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('pajak_pembelian_lib.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('pajak_pembelian_lib.edit', [$pajak->pajak_pembelian_lib_id]) }}" class="btn btn-success mr-2"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('pajak_pembelian_lib.destroy', [$pajak->pajak_pembelian_lib_id]) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Pajak akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
