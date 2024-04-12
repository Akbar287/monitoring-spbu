@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Penitipan Setoran</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.setoran') }}">Setoran</a></div>
        <div class="breadcrumb-item"><a href="{{ route('setoran.penitipan') }}">Penitipan Setoran</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Penitipan Setoran</h2>
    <p class="section-lead">
        Detail data Penitipan Setoran anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">@if(session('msg')){!! session('msg') !!} @endif </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Penitipan Setoran') }}</h4>
                    <div class="card-header-action">
                        {{-- <a href="{{ route('setoran.penitipan.index_penitipan', $penjualan->penjualan_id) }}" class="btn btn-primary">
                            Bank
                        </a> --}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="dispenser_id" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="spbu_id" type="text"
                                        class="form-control @error('spbu_id') is-invalid @enderror" name="spbu_id"
                                        value="{{ $penjualan->spbu()->first()->nama_spbu }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                        value="{{ $penjualan->tanggal }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jam_kerja_id" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="jam_kerja_id" type="text"  readonly
                                        class="form-control @error('jam_kerja_id') is-invalid @enderror" name="jam_kerja_id"
                                        value="{{ $penjualan->jam_kerja()->first()->nama_jam_kerja }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="total_uang_kotak" class="col-md-4 col-form-label text-md-end">{{ __('Total Uang Kotak')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Rp
                                            </div>
                                        </div>
                                        <input id="total_uang_kotak" type="text" readonly
                                            class="form-control thousand-style @error('total_uang_kotak') is-invalid @enderror" name="total_uang_kotak"
                                            value="{{ $penjualan->total_uang_kotak }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="total_penjualan" class="col-md-4 col-form-label text-md-end">{{ __('Total Penjualan')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Rp
                                            </div>
                                        </div>
                                        <input id="total_penjualan" type="text" readonly
                                            class="form-control thousand-style @error('total_penjualan') is-invalid @enderror" name="total_penjualan"
                                            value="{{ $penjualan->total_penjualan }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('PJ Operasional') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            @if($penjualan->pj_operasional_setoran()->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <th>Jabatan</th>
                                        <th>Nama</th>
                                    </thead>
                                    <tbody>
                                        @foreach($penjualan->pj_operasional_setoran()->distinct('jabatan_id')->get() as $pj)
                                        <tr>
                                            <td>{{ $pj->jabatan()->first()->nama_jabatan }}</td>
                                            <td>
                                                @if($pj->penjualan()->first()->pj_operasional_setoran()->where('jabatan_id', $pj->jabatan_id)->count() > 0)
                                                <ul>
                                                @foreach($pj->penjualan()->first()->pj_operasional_setoran()->where('jabatan_id', $pj->jabatan_id)->get() as $m)
                                                    <li>{{ $m->member()->first()->ktp()->first()->nama }}</li>
                                                @endforeach
                                                </ul>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p>PJ Operasional belum di tentukan</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 que-main">
            @if($penjualan->detail_penjualan()->count() == 0)
            <div class="card card-primary">
                <div class="card-header">
                    <h4>Detail Penjualan</h4>
                </div>
                <div class="card-body">
                    <p>Tidak ada Stand Meter yang ditautkan</p>
                </div>
            </div>
            @else
            @foreach($penjualan->detail_penjualan()->get() as $dp)
            <div class="card que-{{ $loop->iteration }}">
                <div class="card-header">
                    <h4>{{ __('Stand Meter #' . $loop->iteration) }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3 justify-content-center">
                        <label for="nozzle_id" class="col-md-4 col-form-label text-md-end">{{ __('Nozzle')
                            }}</label>

                        <div class="col-md-6">
                            <input id="nozzle_id" type="text" readonly class="form-control nozzle_id que-{{ $loop->iteration }}" name="nozzle_id" value="{{ $dp->stand_meter()->first()->nozzle()->first()->nama_nozzle . ' ('. $dp->stand_meter()->first()->nozzle()->first()->produk()->first()->nama_produk .')' }}" />
                        </div>
                    </div>

                    <div class="row mb-3 justify-content-center sm-1">
                        <label for="stand_meter_awal" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Awal')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group">
                                <input id="stand_meter_awal" type="text" readonly class="form-control stand_meter_awal thousand-style que-{{ $loop->iteration }}" name="stand_meter_awal" value="{{ number_format($dp->stand_meter()->first()->stand_meter_awal, 0, ".", ",") }}" />
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3 justify-content-center sm-2">
                        <label for="stand_meter_akhir" class="col-md-4 col-form-label text-md-end">{{ __('Stand Meter Akhir')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group">
                                <input id="stand_meter_akhir" type="text" readonly class="form-control stand_meter_akhir thousand-style que-{{ $loop->iteration }}" name="stand_meter_akhir" value="{{ number_format($dp->stand_meter()->first()->stand_meter_akhir, 0, ".", ",") }}" />
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3 justify-content-center sm-2">
                        <label for="selisih_stand_meter" class="col-md-4 col-form-label text-md-end">{{ __('Selisih Stand Meter')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group">
                                <input id="selisih_stand_meter" type="text" readonly class="form-control selisih_stand_meter thousand-style que-{{ $loop->iteration }}" name="selisih_stand_meter" value="{{ number_format($dp->stand_meter()->first()->stand_meter_akhir - $dp->stand_meter()->first()->stand_meter_awal, 0, ".", ",") }}" />
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3 justify-content-center sm-2">
                        <label for="harga_jual" class="col-md-4 col-form-label text-md-end">{{ __('Harga Jual per Liter')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input id="harga_jual" type="text" readonly class="form-control harga_jual thousand-style que-{{ $loop->iteration }}" name="harga_jual" value="{{ number_format($dp->stand_meter()->first()->nozzle()->first()->produk()->first()->harga()->where('is_default', true)->first()->harga_jual, 0, ".", ",") }}" />
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3 justify-content-center sm-2">
                        <label for="total_uang_penjualan" class="col-md-4 col-form-label text-md-end">{{ __('Total Uang Penjualan')
                            }}</label>

                        <div class="col-md-6">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input id="total_uang_penjualan" type="text" readonly class="form-control total_uang_penjualan thousand-style que-{{ $loop->iteration }}" name="total_uang_penjualan" value="{{ number_format($dp->stand_meter()->first()->stand_meter_akhir, 0, ".", ",") }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pilihan') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-0">
                                <div class="col-md-8 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('setoran.penitipan') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    <form action="{{ route('setoran.open.update.status_from', $penjualan->penjualan_id) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Penitipan Setoran akan di kembalikan ke Opening Setoran!\n Lanjutkan?')" title="Lanjutkan Data" class="btn btn-info mr-2"><i class="fas fa-arrow-left"></i> Kembali ke Opening</button>
                                    </form>
                                    <form action="{{ route('setoran.penitipan.update_closing_to', $penjualan->penjualan_id) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Penitipan Setoran akan di lanjutkan ke Closing!\n Lanjutkan?')" title="Lanjutkan Data" class="btn btn-success mr-2"><i class="fas fa-arrow-right"></i> Lanjut Ke Closing</button>
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
