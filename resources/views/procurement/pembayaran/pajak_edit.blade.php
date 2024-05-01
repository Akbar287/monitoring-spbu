@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Pajak Pembelian</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Rencana Pembayaran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembayaran.show', $rencana->rencana_pembelian_id) }}">Detail</a></div>
        <div class="breadcrumb-item">Pajak Pembelian</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Pajak Pembelian</h2>
    <p class="section-lead">
        Detail data Pajak Pembelian anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif

            <form action="{{ route('pembayaran.pajak_update',$rencana->rencana_pembelian_id) }}" method="post">@csrf @method('put')
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
                                    <input type="text" readonly id="net_price" class="form-control @error('net_price') is-invalid @enderror" name="net_price" value="{{ number_format($rencana->pajak_pembelian()->first()->net_price ?? $rencana->grand_total, 6, ".", ",") }}" />
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
                                    <input type="text" id="ppn" class="form-control thousand-style @error('ppn') is-invalid @enderror" name="ppn" value="{{ number_format($rencana->pajak_pembelian()->first()->ppn ?? 0, 6, ".", ",") }}" />
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
                                    <input type="text" id="ppbkb" class="form-control thousand-style @error('ppbkb') is-invalid @enderror" name="ppbkb" value="{{ number_format($rencana->pajak_pembelian()->first()->ppbkb ?? 0, 6, ".", ",") }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3 justify-content-center">
                            <label for="pph" class="col-md-4 col-form-label text-md-end">{{ __('PPh') }}</label>

                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">Rp.</div>
                                    </div>
                                    <input type="text" id="pph" class="form-control thousand-style @error('pph') is-invalid @enderror" name="pph" value="{{ number_format($rencana->pajak_pembelian()->first()->pph ?? 0, 6, ".", ",") }}" />
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
                                    <input type="text" id="total_amount" readonly class="form-control @error('total_amount') is-invalid @enderror" name="total_amount" value="{{ number_format($rencana->pajak_pembelian()->first()->total_amount ?? $rencana->grand_total, 6, ".", ",") }}" />

                                    <input type="hidden" value="{{ $rencana->grand_total }}" data-type="gross_price_check">
                                </div>

                                <div class="alert alert-danger d-none">Gross Price tidak sama dengan Grand Total Pembelian !!</div>
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
                                <a type="button" href="{{ route('pembayaran.show', $rencana->rencana_pembelian_id) }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>

                                <button type="submit" href="{{ route('pembayaran.pajak_update', $rencana->rencana_pembelian_id) }}" class="btn mx-2 btn-success"><i class="fas fa-save"></i> Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
