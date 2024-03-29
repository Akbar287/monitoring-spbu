@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Ubah Pajak</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.procurement') }}">Procurement</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembelian.index') }}">Pembelian</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembelian.show', $pembelian->rencana_pembelian_id) }}">Detail Pembelian</a></div>
        <div class="breadcrumb-item"><a href="{{ route('pembelian.detail.index', $pembelian->rencana_pembelian_id) }}">Detail Harga</a></div>
        <div class="breadcrumb-item"> Ubah Pajak</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title"> Ubah Pajak</h2>
    <p class="section-lead">
        Kelola Ubah Pajak.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pajak Pembelian') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('pembelian.detail.update_pajak', $pembelian->rencana_pembelian_id) }}" method="post"> @csrf @method('put')
                        <div class="row mb-3 justify-content-center">
                            <label for="net_price" class="col-md-4 col-form-label text-md-end">{{ __('Harga Dasar Sblm Pajak')
                                }}</label>

                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">Rp.</div>
                                    </div>
                                    <input type="text" readonly id="net_price" class="form-control @error('net_price') is-invalid @enderror" name="net_price" value="{{ number_format($pembelian->pajak_pembelian()->first()->net_price ?? $pembelian->grand_total, 0, ".", ",") }}" />
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
                                    <input type="text" id="ppn" class="form-control thousand-style @error('ppn') is-invalid @enderror" name="ppn" value="{{ number_format($pembelian->pajak_pembelian()->first()->ppn ?? $pembelian->grand_total * ($pajakLib->ppn / 100) ?? 0, 0, ".", ",") }}" />
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
                                    <input type="text" id="ppbkb" class="form-control thousand-style @error('ppbkb') is-invalid @enderror" name="ppbkb" value="{{ number_format($pembelian->pajak_pembelian()->first()->ppbkb ?? $pembelian->grand_total * ($pajakLib->ppbkb / 100), 0, ".", ",") }}" />
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
                                    <input type="text" id="pph" class="form-control thousand-style @error('pph') is-invalid @enderror" name="pph" value="{{ number_format($pembelian->pajak_pembelian()->first()->pph ?? $pembelian->grand_total * ($pajakLib->pph / 100), 0, ".", ",") }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3 justify-content-center">
                            <label for="total_amount" class="col-md-4 col-form-label text-md-end">{{ __('Harga (Stlh Pajak)')
                                }}</label>

                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">Rp.</div>
                                    </div>
                                    <input type="text" id="total_amount" readonly class="form-control @error('total_amount') is-invalid @enderror" name="total_amount" value="{{ number_format($pembelian->pajak_pembelian()->first()->total_amount ?? $pembelian->grand_total + ($pembelian->grand_total * ($pajakLib->ppn / 100)) + ($pembelian->grand_total * ($pajakLib->ppbkb / 100)) + ($pembelian->grand_total * ($pajakLib->pph / 100)), 0, ".", ",") }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3 justify-content-center">
                            <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                <a href="{{ route('pembelian.detail.index', $pembelian->rencana_pembelian_id) }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Kembali</a>
                                <button type="submit" class="btn btn-success mx-2"><i class="fas fa-pen"></i> Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
