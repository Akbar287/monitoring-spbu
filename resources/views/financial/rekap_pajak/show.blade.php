@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Detail Pajak Pembelian</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.financial') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('rekap-pajak.index') }}">Rekap Pajak</a></div>
        <div class="breadcrumb-item">Detail Pajak Pembelian</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Pajak Pembelian</h2>
    <p class="section-lead">
        Detail Pajak Pembelian.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Pajak Pembelian') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="rencana_pembelian_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Rencana Pembelian')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="rencana_pembelian_id" type="text" readonly
                                        class="form-control @error('rencana_pembelian_id') is-invalid @enderror" name="rencana_pembelian_id"
                                        value="{{ $rencana->rencana_pembelian_id }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_spbu" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="nama_spbu" type="text"
                                        class="form-control @error('nama_spbu') is-invalid @enderror" name="nama_spbu"
                                        value="{{ $rencana->spbu()->first()->nama_spbu }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="" />{{ $rencana->keterangan }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="grand_total" class="col-md-4 col-form-label text-md-end">{{ __('Grand Total (Sblm Pajak)')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="grand_total" type="text" readonly
                                        class="form-control @error('grand_total') is-invalid @enderror" name="grand_total"
                                        value="{{ 'Rp. ' . number_format($rencana->grand_total, 2, '.', ',') }}" / />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="grand_total" class="col-md-4 col-form-label text-md-end">{{ __('Grand Total (Stlh Pajak)')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="grand_total" type="text" readonly class="form-control @error('grand_total') is-invalid @enderror" name="grand_total" value="{{ $rencana->pajak_pembelian()->count() > 0 ? 'Rp. '. number_format($rencana->pajak_pembelian()->first()->total_amount, 2, ".", ",")  : 'Rp. ' . number_format($rencana->grand_total + ($rencana->grand_total * ($pajak->ppn / 100)) + ($rencana->grand_total * ($pajak->ppbkb / 100)) + ($rencana->grand_total * ($pajak->pph / 100)), 2, '.', ',') }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pajak Pembelian') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3 justify-content-center">
                        <label for="net_price" class="col-md-4 col-form-label text-md-end">{{ __('Net Price')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" id="net_price" readonly class="form-control thousand-style @error('net_price') is-invalid @enderror" name="net_price" value="{{ $rencana->pajak_pembelian()->first()->net_price ?? $rencana->grand_total }}" />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-center">
                        <label for="ppn" class="col-md-4 col-form-label text-md-end">{{ __('PPn')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" id="ppn" readonly class="form-control thousand-style @error('ppn') is-invalid @enderror" name="ppn" value="{{ $rencana->pajak_pembelian()->first()->ppn ?? 0 }}" />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-center">
                        <label for="ppbkb" class="col-md-4 col-form-label text-md-end">{{ __('PPBKB')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" id="ppbkb" readonly class="form-control thousand-style @error('ppbkb') is-invalid @enderror" name="ppbkb" value="{{ $rencana->pajak_pembelian()->first()->ppbkb ?? 0 }}" />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-center">
                        <label for="pph" class="col-md-4 col-form-label text-md-end">{{ __('PPh')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" id="pph" readonly class="form-control thousand-style @error('pph') is-invalid @enderror" name="pph" value="{{ $rencana->pajak_pembelian()->first()->pph ?? 0 }}" />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3 justify-content-center">
                        <label for="total_amount" class="col-md-4 col-form-label text-md-end">{{ __('Total Amount (Gross Price)')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">Rp.</div>
                                </div>
                                <input type="text" id="total_amount" readonly class="form-control thousand-style @error('total_amount') is-invalid @enderror" name="total_amount" value="{{ $rencana->pajak_pembelian()->first()->total_amount ?? $rencana->grand_total }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pilihan') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4 d-flex align-items-end">
                            <a type="button" href="{{ route('rekap-pajak.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
