@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Stok Taking</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.stok') }}">Stok</a></div>
        <div class="breadcrumb-item"><a href="{{ route('stok-transfer.index') }}">Stok Taking</a></div>
        <div class="breadcrumb-item">Detail</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Detail Stok Taking</h2>
    <p class="section-lead">
        Detail data Stok Taking anda.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(session('msg')){!! session('msg') !!} @endif
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Informasi Umum') }}</h4>
                    <div class="card-header-action">
                        <div class="badge badge-primary">{{ $dombakTransfer->is_confirmation ? 'Terkofirmasi' : 'Belum Terkonfirmasi' }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="tanggal" class="col-md-4 col-form-label text-md-end">{{ __('Tanggal')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tanggal" type="date" class="form-control @error('tanggal') is-invalid @enderror" name="tanggal" readonly value="{{ $dombakTransfer->tanggal }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="jam_kerja_id" class="col-md-4 col-form-label text-md-end">{{ __('Jam Kerja')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="jam_kerja_id" type="text" class="form-control @error('jam_kerja_id') is-invalid @enderror" readonly name="jam_kerja_id" value="{{ $dombakTransfer->jam_kerja()->first()->nama_jam_kerja .' ('. $dombakTransfer->jam_kerja()->first()->jam_datang_in .'-' . $dombakTransfer->jam_kerja()->first()->jam_pulang_out .')' }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Dombak Pertama') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('stok-transfer.file.index', [$dombakTransfer->dombak_transfer_id, $d1->dokumen_stok_id]) }}" class="btn btn-primary">
                            File
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="dombak_id" class="col-md-4 col-form-label text-md-end">{{ __('Dombak')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="dombak_id" type="text" readonly class="form-control" name="dombak_id" value="{{ $d1->dombak()->first()->nama_dombak }}" />
                                </div>
                            </div>

                            {{-- <div class="row mb-3 justify-content-center">
                                <label for="volume_tercatat" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Tercatat Terakhir')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_tercatat" type="text" class="form-control thousand-style" name="volume_tercatat" value="{{ '' }}" readonly />
                                </div>
                            </div> --}}

                            <div class="row mb-3 justify-content-center">
                                <label for="tinggi_dombak" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Sebelum')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tinggi_dombak" readonly type="number" step=".1" class="form-control thousand-style" name="tinggi_dombak" value="{{ $d1->dombak()->first()->konversi()->where('volume', $d1->stok_awal)->first()->tinggi ?? 0 }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tinggi_dombak_setelahnya" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Setelah')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tinggi_dombak_setelahnya" readonly type="number" step=".1" class="form-control thousand-style" name="tinggi_dombak_setelahnya" value="{{ $d1->dombak()->first()->konversi()->where('volume', $d1->stok_akhir)->first()->tinggi ?? 0 }}" >
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume_sebelumnya" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Sebelumnya')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_sebelumnya" type="text" class="form-control thousand-style" name="volume_sebelumnya" value="{{ $d1->stok_awal }}" readonly />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume_setelah" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Setelah')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_setelah" type="text" class="form-control thousand-style" name="volume_setelah" value="{{ $d1->stok_akhir }}" readonly />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Dipindahkan')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume" type="text" class="form-control thousand-style" name="volume" value="{{ $d1->stok_awal - $d1->stok_akhir }}" readonly />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Dombak Kedua') }}</h4>
                    <div class="card-header-action">
                        <a href="{{ route('stok-transfer.file.index', [$dombakTransfer->dombak_transfer_id, $d2->dokumen_stok_id]) }}" class="btn btn-primary">
                            File
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row mb-3 justify-content-center">
                                <label for="dombak_kedua_id" class="col-md-4 col-form-label text-md-end">{{ __('Dombak')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="dombak_kedua_id" readonly type="text" class="form-control" name="dombak_kedua_id" value="{{ $d2->dombak()->first()->nama_dombak }}" />
                                </div>
                            </div>

                            {{-- <div class="row mb-3 justify-content-center">
                                <label for="volume_sebelumnya_kedua_terakhir" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Tercatat Terakhir')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_sebelumnya_kedua_terakhir" type="text" class="form-control thousand-style" name="volume_sebelumnya_kedua_terakhir" value="{{ '' }}" readonly />
                                </div>
                            </div> --}}

                            <div class="row mb-3 justify-content-center">
                                <label for="tinggi_dombak_kedua" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Sebelum')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tinggi_dombak_kedua" type="number" step=".1" class="form-control thousand-style" name="tinggi_dombak_kedua" readonly value="{{ $d2->dombak()->first()->konversi()->where('volume', $d2->stok_awal)->first()->tinggi ?? 0 }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="tinggi_dombak_kedua_setelah" class="col-md-4 col-form-label text-md-end">{{ __('Tinggi Dombak (cm) Setelah')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="tinggi_dombak_kedua_setelah" type="number" step=".1" class="form-control thousand-style" name="tinggi_dombak_kedua_setelah" readonly value="{{ $d2->dombak()->first()->konversi()->where('volume', $d2->stok_akhir)->first()->tinggi ?? 0 }}" />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume_sebelumnya_kedua" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Sebelumnya')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_sebelumnya_kedua" type="text" class="form-control thousand-style" name="volume_sebelumnya_kedua" value="{{ $d2->stok_awal }}" readonly />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume_sebelumnya_kedua_setelah" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Setelah')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_sebelumnya_kedua_setelah" type="text" class="form-control thousand-style" name="volume_sebelumnya_kedua_setelah" value="{{ $d2->stok_akhir }}" readonly />
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume_kedua" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) Terpindah')
                                    }}</label>

                                <div class="col-md-6">
                                    <input id="volume_kedua" type="text" class="form-control thousand-style" name="volume_kedua" value="{{ $d2->stok_akhir - $d2->stok_awal }}" readonly>
                                </div>
                            </div>

                            <div class="row mb-3 justify-content-center">
                                <label for="volume_selisih_kedua" class="col-md-4 col-form-label text-md-end">{{ __('Volume (liter) (Loss/Profit)')
                                    }}</label>

                                <div class="col-md-6">

                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{ ($d1->stok_awal - $d1->stok_akhir) > ($d2->stok_akhir - $d2->stok_awal) ? '-' : '+' }}</span>
                                        </div>
                                        <input id="volume_selisih_kedua" type="text" class="form-control thousand-style" name="volume_selisih_kedua" value="{{ ($d1->stok_awal - $d1->stok_akhir ) - ($d2->stok_akhir - $d2->stok_awal) }}" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Aksi') }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-0">
                        <div class="col-md-6 offset-md-4 d-flex align-items-end">
                            <a type="button" href="{{ route('stok-transfer.index') }}" class="btn btn-primary mr-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                            @if(!$dombakTransfer->is_confirmation && Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                            {{-- <a type="button" href="{{ route('stok-transfer.edit', $dombakTransfer->dombak_transfer_id) }}" class="btn mx-2 btn-success"><i class="fas fa-pen"></i> Ubah</a> --}}
                            <form action="{{ route('stok-transfer.delete', $dombakTransfer->dombak_transfer_id) }}" method="POST">@csrf @method('delete')
                                <button type="submit" onclick="return confirm('Stok Transfer akan dihapus!\n Lanjutkan?')" title="Hapus Data" class="btn btn-danger mr-2"><i class="fas fa-trash"></i> Hapus</button>
                            </form>
                            <form action="{{ route('stok-transfer.confirmation', $dombakTransfer->dombak_transfer_id) }}" method="POST">@csrf @method('put')
                                <button type="submit" onclick="return confirm('Stok Transfer akan dikonfirmasi!\n Lanjutkan?')" title="Konfirmasi Data" class="btn btn-info"><i class="fas fa-check"></i> Konfirmasi</button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
