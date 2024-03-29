@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Petty Cash</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.financial') }}">Financial</a></div>
        <div class="breadcrumb-item"><a href="{{ route('financial.petty-cash') }}">Petty Cash</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Petty Cash</h2>
    <p class="section-lead">
        Detail data Petty Cash anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Petty Cash') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('financial.petty-cash.file', $pettyCash->petty_cash_id) }}" class="btn btn-primary">
                            File
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="spbu_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="spbu_id" type="text" readonly
                                        class="form-control" name="spbu_id" value="{{ $pettyCash->spbu()->first()->nama_spbu . ' ('.$pettyCash->spbu()->first()->nomor_spbu.')' }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_kode" class="col-md-4 col-form-label text-md-end">{{ __('No. Kode')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="no_kode" type="text" readonly
                                        class="form-control" name="no_kode"
                                        value="{{ $pettyCash->no_kode }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_bukti" class="col-md-4 col-form-label text-md-end">{{ __('No. Bukti')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="no_bukti" type="text" readonly
                                        class="form-control" name="no_bukti"
                                        value="{{ $pettyCash->no_bukti }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jenis" class="col-md-4 col-form-label text-md-end">{{ __('Jenis')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="jenis" type="text" readonly class="form-control" name="jenis" value="{{ $pettyCash->jenis == 'debit' ? 'Debit' : "Kredit" }}" />
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Jumlah')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">Rp. </div>
                                            </div>
                                            <input id="jumlah" type="text" readonly class="form-control thousand-style @error('jumlah') is-invalid @enderror" name="jumlah" value="{{ $pettyCash->jumlah }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control" name="tanggal"
                                        value="{{ $pettyCash->tanggal }}" autocomplete="name"
                                        autofocus>
                                </div>
                            </div>
                            
                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>
        
                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control" name="keterangan" value="">{{ $pettyCash->keterangan }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('financial.petty-cash') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <a type="button" href="{{ route('petty-cash.edit', $pettyCash->petty_cash_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a>
                                    <form action="{{ route('petty-cash.delete', $pettyCash->petty_cash_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Petty Cash akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button>
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
