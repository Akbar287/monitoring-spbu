@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Penerimaan BBM</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('penerimaan.index') }}">Penerimaan BBM</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Penerimaan BBM</h2>
    <p class="section-lead">
        Detail data Penerimaan BBM anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-12">
            @if(session('msg')){!! session('msg') !!} @endif
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Rencana Pembelian') }}</h4>
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
                                        value="{{ $penerimaan->file_lo()->first()->detail_rencana_pembelian()->first()->rencana_pembelian()->first()->rencana_pembelian_id }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_spbu" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="nama_spbu" type="text"
                                        class="form-control @error('nama_spbu') is-invalid @enderror" name="nama_spbu"
                                        value="{{ $penerimaan->file_lo()->first()->detail_rencana_pembelian()->first()->rencana_pembelian()->first()->spbu()->first()->nama_spbu }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="" />{{ $penerimaan->file_lo()->first()->detail_rencana_pembelian()->first()->rencana_pembelian()->first()->keterangan }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Detail Produk Rencana Pembelian') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="detail_rencana_pembelian_id" class="col-md-4 col-form-label text-md-end">{{ __('Id Detail Rencana Pembelian')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="detail_rencana_pembelian_id" type="text" readonly
                                        class="form-control" name="detail_rencana_pembelian_id"
                                        value="{{ $penerimaan->file_lo()->first()->detail_rencana_pembelian()->first()->detail_rencana_pembelian_id }}" />
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="produk_id" type="text" readonly
                                        class="form-control" name="produk_id"
                                        value="{{ $penerimaan->file_lo()->first()->detail_rencana_pembelian()->first()->produk()->first()->nama_produk }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="rit" class="col-md-4 col-form-label text-md-end">{{ __('RIT')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="rit" type="text" readonly
                                        class="form-control @error('rit') is-invalid @enderror" name="rit"
                                        value="{{ number_format($penerimaan->file_lo()->first()->detail_rencana_pembelian()->first()->rit, 2, ".", ',') }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Jumlah / Quantity')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="jumlah" type="text" readonly
                                        class="form-control @error('jumlah') is-invalid @enderror" name="jumlah"
                                        value="{{ number_format($penerimaan->file_lo()->first()->detail_rencana_pembelian()->first()->jumlah, 6, ".", ',') }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="satuan_jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Satuan Jumlah')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="satuan_jumlah" type="text" readonly
                                        class="form-control" name="satuan_jumlah"
                                        value="{{ $penerimaan->file_lo()->first()->detail_rencana_pembelian()->first()->satuan_jumlah }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="harga" class="col-md-4 col-form-label text-md-end">{{ __('Harga')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">Rp</div>
                                        </div>
                                        <input id="harga" type="text" readonly
                                            class="form-control" name="harga"
                                            value="{{ number_format($penerimaan->file_lo()->first()->detail_rencana_pembelian()->first()->harga, 6, ".", ",") }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="total_harga" class="col-md-4 col-form-label text-md-end">{{ __('Total Harga')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">Rp</div>
                                        </div>
                                        <input id="total_harga" type="text" readonly
                                            class="form-control" name="total_harga"
                                            value="{{ number_format($penerimaan->file_lo()->first()->detail_rencana_pembelian()->first()->total_harga, 6, ".", ",") }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pengiriman') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="member_id" class="col-md-4 col-form-label text-md-end">{{ __('Yang Menerima')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="member_id" type="text" readonly
                                        class="form-control" name="member_id"
                                        value="{{ $penerimaan->file_lo()->first()->pengiriman()->first()->member()->first()->ktp()->first()->nama }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_do" class="col-md-4 col-form-label text-md-end">{{ __('No DO')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_do" type="text"
                                        class="form-control" name="no_do" readonly
                                        value="{{ $penerimaan->file_lo()->first()->pengiriman()->first()->no_do }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_pol" class="col-md-4 col-form-label text-md-end">{{ __('No Pol')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_pol" type="text" readonly
                                        class="form-control" name="no_pol"
                                        value="{{ $penerimaan->file_lo()->first()->pengiriman()->first()->no_pol }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control" name="tanggal"
                                        value="{{ $penerimaan->file_lo()->first()->pengiriman()->first()->tanggal }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('File Lo') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="detail_rencana_pembelian_id" class="col-md-4 col-form-label text-md-end">{{ __('Detail Rencana Pembelian')
                                    }}</label>

                                <div class="col-md-6">
                                    <input readonly id="detail_rencana_pembelian_id" type="text"
                                        class="form-control @error('detail_rencana_pembelian_id') is-invalid @enderror" name="detail_rencana_pembelian_id"
                                        value="{{ $penerimaan->file_lo()->first()->produk()->first()->nama_produk . ' (' . number_format($penerimaan->file_lo()->first()->jumlah, 6, ",", ".") . ' ' . $penerimaan->file_lo()->first()->satuan_jumlah .')' }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_faktur" class="col-md-4 col-form-label text-md-end">{{ __('No. Faktur')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_faktur" type="text" readonly
                                        class="form-control @error('no_faktur') is-invalid @enderror" name="no_faktur"
                                        value="{{ $penerimaan->file_lo()->first()->no_faktur }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_lo" class="col-md-4 col-form-label text-md-end">{{ __('No. LO')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_lo" type="text" readonly
                                        class="form-control @error('no_lo') is-invalid @enderror" name="no_lo"
                                        value="{{ $penerimaan->file_lo()->first()->no_lo }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="gambar" class="col-md-4 col-form-label text-md-end">{{
                                    __('File')
                                    }}</label>

                                <div class="col-md-6">
                                    <img src="{{ asset('storage/file_lo/' . $penerimaan->file_lo()->first()->nama_file) }}" alt="{{ $penerimaan->file_lo()->first()->nama_dokumen }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Penerimaan BBM') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="dombak_id" class="col-md-4 col-form-label text-md-end">{{ __('Dombak')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="dombak_id" type="text" readonly
                                        class="form-control" name="dombak_id"
                                        value="{{ $penerimaan->dokumen_stok()->first()->dombak()->first()->nama_dombak }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>
                                <div class="col-md-6">
                                    <input id="tanggal" type="text" readonly
                                        class="form-control" name="tanggal"
                                        value="{{ $penerimaan->dokumen_stok()->first()->tanggal }}" />
                                </div>
                            </div>
                            <div class="row mb-3 justify-content-center">
                                <label for="jam_kerja" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                    }}</label>
                                <div class="col-md-6">
                                    <input id="jam_kerja" type="text" readonly
                                        class="form-control" name="jam_kerja"
                                        value="{{ $penerimaan->dokumen_stok()->first()->jam_kerja()->first()->nama_jam_kerja }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jam_penerimaan" class="col-md-4 col-form-label text-md-end">{{ __('Jam Penerimaan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="jam_penerimaan" type="time" readonly class="form-control" name="jam_penerimaan" value="{{ $penerimaan->dokumen_stok()->first()->waktu ?? '' }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tinggi_dombak" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Sebelum Penerimaan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tinggi_dombak" type="number" step=".1" readonly
                                        class="form-control thousand-style" name="tinggi_dombak"
                                        value="{{ $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->konversi()->first()->tinggi ?? 0 }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tinggi_dombak" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Setelah Penerimaan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tinggi_dombak" type="number" step=".1" readonly
                                        class="form-control thousand-style" name="tinggi_dombak"
                                        value="{{ $penerimaan->dokumen_stok()->first()->konversi()->first()->tinggi ?? 0 }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume_sebelumnya" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Sebelumnya')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_sebelumnya" type="text"
                                        class="form-control thousand-style" name="volume_sebelumnya"
                                        value="{{ $penerimaan->dokumen_stok()->first()->stok_awal ?? 0 }}" readonly >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume_penerimaan" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) (Penerimaan)')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_penerimaan" type="text" readonly
                                        class="form-control thousand-style" name="volume_penerimaan"
                                        value="{{ $penerimaan->dokumen_stok()->first()->stok_akhir ?? 0 }}" readonly
                                        >
                                    @error('volume_penerimaan')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Aktual')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume" type="text" readonly
                                        class="form-control thousand-style" name="volume"
                                        value="{{ $penerimaan->dokumen_stok()->first()->stok_akhir - $penerimaan->dokumen_stok()->first()->stok_awal ?? 0 }}" readonly
                                        >
                                    @error('volume')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume_dibeli" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) (Dibeli)')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_dibeli" type="text" readonly
                                        class="form-control thousand-style" name="volume_dibeli"
                                        value="{{ number_format($penerimaan->file_lo()->first()->jumlah, 6, ".", ',') }}" readonly
                                        >
                                    @error('volume_dibeli')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume_selisih" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) (Loss/Profit)')
                                    }}</label>

                                <div class="col-md-6">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">{{ $penerimaan->dokumen_stok()->first()->losses()->first()->simbol == 'kurang' ? '-' : '+' }}</div>
                                        </div>
                                        <input id="volume_selisih" type="text" readonly
                                            class="form-control thousand-style @error('volume_selisih') is-invalid @enderror" name="volume_selisih"
                                            value="{{ $penerimaan->dokumen_stok()->first()->losses()->first()->jumlah }}" readonly
                                            >
                                    </div>

                                    @error('volume_selisih')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            @if(!$penerimaan->dokumen_stok()->first()->is_confirmation)
                            @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                            <div class="row mb-3 justify-content-center">
                                <div class="alert alert-danger">Konfirmasi Jika Datanya sudah benar dan Fixed. dikonfirmasi akan mengubah stok produk dan stok dombak dan tidak bisa di undo.</div>
                            </div>
                            @else
                            <div class="row mb-3 justify-content-center">
                                <div class="alert alert-info">Tunggu hingga Admin mengonfirmasi data penerimaan BBM.</div>
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Pilihan') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                    <a type="button" href="{{ route('penerimaan.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>

                                    @if(!$penerimaan->dokumen_stok()->first()->is_confirmation)
                                    <form action="{{ route('penerimaan.delete', $penerimaan->penerimaan_id) }}" method="POST">@csrf @method('delete')
                                        <button type="submit" onclick="return confirm('Penerimaan akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger mr-2"><i class="fas fa-trash"></i> Hapus</button>
                                    </form>
                                    @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                                    <form action="{{ route('penerimaan.show_confirmation', $penerimaan->penerimaan_id) }}" method="POST">@csrf @method('put')
                                        <button type="submit" onclick="return confirm('Penerimaan ini akan dikonfirmasi!\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-success"><i class="fas fa-check"></i> Konfirmasi</button>
                                    </form>
                                    @endif
                                    @endif
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
