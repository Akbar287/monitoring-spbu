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
                                        value="{{ $fileLo->detail_rencana_pembelian()->first()->rencana_pembelian()->first()->rencana_pembelian_id }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="nama_spbu" class="col-md-4 col-form-label text-md-end">{{ __('SPBU')}}</label>

                                <div class="col-md-6">
                                    <input readonly id="nama_spbu" type="text"
                                        class="form-control @error('nama_spbu') is-invalid @enderror" name="nama_spbu"
                                        value="{{ $fileLo->detail_rencana_pembelian()->first()->rencana_pembelian()->first()->spbu()->first()->nama_spbu }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="keterangan" class="col-md-4 col-form-label text-md-end">{{ __('Keterangan')
                                    }}</label>

                                <div class="col-md-6">
                                    <textarea id="keterangan" type="text" rows="3" cols="3" readonly
                                        class="form-control @error('keterangan') is-invalid @enderror" name="keterangan"
                                        value="" />{{ $fileLo->detail_rencana_pembelian()->first()->rencana_pembelian()->first()->keterangan }}</textarea>
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
                                        value="{{ $fileLo->detail_rencana_pembelian()->first()->detail_rencana_pembelian_id }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="produk_id" class="col-md-4 col-form-label text-md-end">{{ __('Produk')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="produk_id" type="text" readonly
                                        class="form-control" name="produk_id"
                                        value="{{ $fileLo->detail_rencana_pembelian()->first()->produk()->first()->nama_produk }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="rit" class="col-md-4 col-form-label text-md-end">{{ __('RIT')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="rit" type="text" readonly
                                        class="form-control @error('rit') is-invalid @enderror" name="rit"
                                        value="{{ number_format($fileLo->detail_rencana_pembelian()->first()->rit, 2, ".", ',') }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Jumlah / Quantity')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="jumlah" type="text" readonly
                                        class="form-control @error('jumlah') is-invalid @enderror" name="jumlah"
                                        value="{{ number_format($fileLo->detail_rencana_pembelian()->first()->jumlah, 6, ".", ',') }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="satuan_jumlah" class="col-md-4 col-form-label text-md-end">{{ __('Satuan Jumlah')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="satuan_jumlah" type="text" readonly
                                        class="form-control" name="satuan_jumlah"
                                        value="{{ $fileLo->detail_rencana_pembelian()->first()->satuan_jumlah }}" />
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
                                            value="{{ number_format($fileLo->detail_rencana_pembelian()->first()->harga, 6, ".", ",") }}" />
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
                                            value="{{ number_format($fileLo->detail_rencana_pembelian()->first()->total_harga, 6, ".", ",") }}" />
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
                                        value="{{ $fileLo->pengiriman()->first()->member()->first()->ktp()->first()->nama }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_do" class="col-md-4 col-form-label text-md-end">{{ __('No DO')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_do" type="text"
                                        class="form-control" name="no_do" readonly
                                        value="{{ $fileLo->pengiriman()->first()->no_do }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_pol" class="col-md-4 col-form-label text-md-end">{{ __('No Pol')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_pol" type="text" readonly
                                        class="form-control" name="no_pol"
                                        value="{{ $fileLo->pengiriman()->first()->no_pol }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="date" readonly
                                        class="form-control" name="tanggal"
                                        value="{{ $fileLo->pengiriman()->first()->tanggal }}" />
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
                                        value="{{ $fileLo->produk()->first()->nama_produk . ' (' . number_format($fileLo->jumlah, 6, ",", ".") . ' ' . $fileLo->satuan_jumlah .')' }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_faktur" class="col-md-4 col-form-label text-md-end">{{ __('No. Faktur')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_faktur" type="text" readonly
                                        class="form-control @error('no_faktur') is-invalid @enderror" name="no_faktur"
                                        value="{{ $fileLo->no_faktur }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="no_lo" class="col-md-4 col-form-label text-md-end">{{ __('No. LO')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="no_lo" type="text" readonly
                                        class="form-control @error('no_lo') is-invalid @enderror" name="no_lo"
                                        value="{{ $fileLo->no_lo }}">
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="gambar" class="col-md-4 col-form-label text-md-end">{{
                                    __('File')
                                    }}</label>

                                <div class="col-md-6">
                                    <img src="{{ asset('storage/file_lo/' . $fileLo->nama_file) }}" alt="{{ $fileLo->nama_dokumen }}" class="img img-thumbnail img-temporary" style="width: 800px; height: auto">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('penerimaan.store', $fileLo->file_lo_id) }}" method="POST"> @csrf
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('Penerimaan BBM') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-danger">Pastikan sudah diisi dengan benar. Setiap kesalahan akan memengaruhi stok dombak dan TiDAK BISA DIHAPUS/DIUBAH</div>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info">Stok Taking Shift Sebelumnya akan terclose. Sistem otomatis akan membuka shift baru.</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="accordion" id="accordionExample">
                                    @foreach($dombak as $d)
                                    <div class="card">
                                        <div class="card-header" id="heading-{{ $d->dombak_id }}">
                                            <h2 class="mb-0">
                                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#dombak-{{$d->dombak_id}}" aria-expanded="true" aria-controls="dombak-{{$d->dombak_id}}">
                                                    {{ $d->dombak()->first()->nama_dombak }}
                                                </button>
                                            </h2>
                                        </div>

                                        <div id="dombak-{{$d->dombak_id}}" class="collapse" aria-labelledby="heading-{{ $d->dombak_id }}" data-parent="#accordionExample">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <input type="hidden" name="dombak_id[]" value="{{ $d->dombak_id }}">
                                                        <div class="row mb-3 justify-content-center tinggi_dombak_sebelumnya-row">
                                                            <label for="tinggi_dombak_sebelumnya-{{ $d->dombak_id }}" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Sebelum nya')
                                                                }}</label>

                                                            <div class="col-md-6">
                                                                <input id="tinggi_dombak_sebelumnya-{{ $d->dombak_id }}" type="number" step=".1" data-dombak="{{ $d->dombak_id }}" class="form-control tinggi_dombak_sebelumnya @error('tinggi_dombak_sebelumnya') is-invalid @enderror" name="tinggi_dombak_sebelumnya[]" min="{{ $d->dombak()->first()->konversi()->select('tinggi')->orderBy('tinggi', 'asc')->limit(1)->first()->tinggi }}" max="{{ $d->dombak()->first()->konversi()->select('tinggi')->orderBy('tinggi', 'desc')->limit(1)->first()->tinggi }}" value="{{ old("tinggi_dombak_sebelumnya") }}" />

                                                                @error('tinggi_dombak_sebelumnya')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3 justify-content-center tinggi_dombak_setelahnya-row">
                                                            <label for="tinggi_dombak_setelahnya-{{ $d->dombak_id }}" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Setelah nya')
                                                                }}</label>

                                                            <div class="col-md-6">
                                                                <input id="tinggi_dombak_setelahnya-{{ $d->dombak_id }}" type="number" step=".1" data-dombak="{{ $d->dombak_id }}" class="form-control tinggi_dombak_setelahnya  @error('tinggi_dombak_setelahnya') is-invalid @enderror" name="tinggi_dombak_setelahnya[]" min="{{ $d->dombak()->first()->konversi()->select('tinggi')->orderBy('tinggi', 'asc')->limit(1)->first()->tinggi }}" max="{{ $d->dombak()->first()->konversi()->select('tinggi')->orderBy('tinggi', 'desc')->limit(1)->first()->tinggi }}" value="{{ old("tinggi_dombak_setelahnya") }}" />

                                                                @error('tinggi_dombak_setelahnya')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3 justify-content-center volume_sebelumnya-row">
                                                            <label for="volume_sebelumnya-{{ $d->dombak_id }}" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Sebelumnya')
                                                                }}</label>

                                                            <div class="col-md-6">
                                                                <input id="volume_sebelumnya-{{ $d->dombak_id }}" type="text" class="form-control volume_sebelumnya thousand-style @error('volume_sebelumnya') is-invalid @enderror" name="volume_sebelumnya[]" value="0" readonly />
                                                                <span class="text-muted">Volume Sebelumnya didapatkan dari tinggi dombak sebelum Penerimaan</span>

                                                                @error('volume_sebelumnya')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3 justify-content-center volume_penerimaan-row">
                                                            <label for="volume_penerimaan-{{ $d->dombak_id }}" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) (Penerimaan)')
                                                                }}</label>

                                                            <div class="col-md-6">
                                                                <input id="volume_penerimaan-{{ $d->dombak_id }}" type="text" class="form-control volume_penerimaan thousand-style @error('volume_penerimaan') is-invalid @enderror" name="volume_penerimaan" value="0" readonly />
                                                                <span class="text-muted">Volume Penerimaan didapatkan dari tinggi dombak Setelah Penerimaan</span>

                                                                @error('volume_penerimaan')
                                                                <span class="invalid-feedback" role="alert">
                                                                    <strong>{{ $message }}</strong>
                                                                </span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <div class="col-12">
                                        <div class="alert alert-info">Loss / Profit Penerimaan BBM akan ditempel ke Penerimaan Dombak Pertama</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="row mb-3 justify-content-center">
                                    <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="tanggal" type="date"
                                            class="form-control @error('tanggal') is-invalid @enderror" name="tanggal"
                                            value="{{ old("tanggal") != null ? old("tanggal") : date('Y-m-d') }}" />

                                        @error('tanggal')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="jam_kerja_id" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                        }}</label>

                                    <div class="col-md-6">
                                        <select class="custom-select @error('jam_kerja_id') is-invalid @enderror" name="jam_kerja_id" id="jam_kerja_id">
                                            @foreach($jamKerja as $s)
                                            <option {{ old('jam_kerja_id') == $s->jam_kerja_id ? 'selected' : '' }} value="{{ $s->jam_kerja_id }}">{{ $s->nama_jam_kerja . ' (' . $s->jam_datang_in . '-' . $s->jam_pulang_out . ')' }}</option>
                                            @endforeach
                                        </select>

                                        @error('jam_kerja_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="volume_aktual" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Aktual')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_aktual" type="text" class="form-control thousand-style @error('volume_aktual') is-invalid @enderror" name="volume_aktual" value="0" readonly />
                                        <span class="text-muted">Volume Aktual didapatkan dari Volume penerimaan dikurangi Volume Sebelum </span>

                                        @error('volume_aktual')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3 justify-content-center">
                                    <label for="volume_dibeli" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Yang Dibeli')
                                        }}</label>

                                    <div class="col-md-6">
                                        <input id="volume_dibeli" type="text"
                                            class="form-control thousand-style @error('volume_dibeli') is-invalid @enderror" name="volume_dibeli"
                                            value="{{ number_format($fileLo->jumlah, 6, ".", ',') }}" readonly />
                                        <span class="text-muted">Volume Yang Dibeli adalah jumlah volume yang seharusnya didapatkan/dibayarkan</span>

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
                                        <input id="volume_selisih" type="text"
                                            class="form-control thousand-style @error('volume_selisih') is-invalid @enderror" name="volume_selisih"
                                            value="0" readonly />
                                        <span class="text-muted">Volume Selisih didapatkan dari Volume Aktual dikurangi Volume dibeli</span>

                                        @error('volume_selisih')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row mb-0">
                                    <div class="col-md-6 offset-md-4 d-flex align-items-end">
                                        <a type="button" href="{{ route('penerimaan.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                        <button type="submit" class="btn btn-success"><i class="fas fa-pen"></i> Kirim</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
