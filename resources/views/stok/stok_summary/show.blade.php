@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h4 class="">{{ Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('d F Y') }}</h4>
    </div>
</div>

<div class="accordion" id="stok-summary">
    @for($i = 0; $i < count($temp); $i++)
    <div class="card">
        <div class="card-header" id="panel-{{ $produk[$i]['produk_id'] }}-heading">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#panel-{{ $produk[$i]['produk_id'] }}-collapse" aria-expanded="true" aria-controls="panel-{{ $produk[$i]['produk_id'] }}-collapse">
                    {{ $produk[$i]['nama_produk']}}
                </button>
            </h2>
        </div>

        <div id="panel-{{ $produk[$i]['produk_id'] }}-collapse" class="collapse card-body" aria-labelledby="panel-{{ $produk[$i]['produk_id'] }}-heading" data-parent="#stok-summary" style="background: #f2f5f8b4;">
            <div class="row">
                <div class="col-12">
                    @if(!$temp[$i]->isEmpty())
                    <div class="col-12 col-sm-12 col-md-12">
                        <div class="activities">
                            @foreach($temp[$i] as $t)
                            <div class="activity">
                                <div class="activity-icon bg-primary text-white shadow-primary">
                                    @if($t->type_movement == 'Commit Change From User')
                                    <i class="fas fa-comment-alt"></i>
                                    @endif
                                    @if($t->type_movement == 'Good Transfer Send' || $t->type_movement == 'Good Transfer Receive')
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-arrows-exchange"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 10h14l-4 -4" /><path d="M17 14h-14l4 4" /></svg>
                                    @endif
                                    @if($t->type_movement == 'Good Return Tera')
                                    <i class="fas fa-arrow-down"></i>
                                    @endif
                                    @if($t->type_movement == 'Loss')
                                    <i class="fas fa-cut"></i>
                                    @endif
                                    @if($t->type_movement == 'Stok Taking Good Receive Penerimaan' || $t->type_movement == 'Stok Taking Good Transfer')
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-wiper"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M3 9l5.5 5.5a5 5 0 0 1 7 0l5.5 -5.5a12 12 0 0 0 -18 0" /><path d="M12 18l-2.2 -12.8" /></svg>
                                    @endif
                                    @if($t->type_movement == 'Good Issue Sales')
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-coin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M14.8 9a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1" /><path d="M12 7v10" /></svg>
                                    @endif
                                    @if($t->type_movement == 'Good Receive Penerimaan')
                                    <i class="fas fa-arrow-down"></i>
                                    @endif
                                    @if($t->type_movement == 'Good Issue Tera')
                                    <i class="fas fa-arrow-up"></i>
                                    @endif
                                    @if($t->type_movement == 'Stok Taking')
                                    <i class="fas fa-thermometer-full"></i>
                                    @endif
                                </div>
                                <div class="activity-detail">
                                    <div class="mb-2">
                                        <span class="text-job text-primary">{{ $t->waktu }}</span>
                                        <span class="bullet"></span>
                                        <span class="text-job">{{ $t->type_movement }}</span>
                                        <span class="bullet"></span>
                                        @if($t->is_confirmation)<span class="text-job text-success">Terkonfirmasi</span> @else <span class="text-job text-danger">Belum terkonfirmasi</span> @endif
                                        <div class="float-right dropdown">
                                            <a href="#" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-h"></i></a>
                                            <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 20px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                <div class="dropdown-title">Pilihan</div>
                                                <a href="javascript:void(0)" role="button" class="dropdown-item has-icon see-more-window" data-id="{{ $t->dokumen_stok_id }}" ><i class="fas fa-eye"></i> Lihat</a>
                                                <a href="javascript:void(0)" role="button" class="dropdown-item has-icon edit-more-window" data-id="{{ $t->dokumen_stok_id }}" ><i class="fas fa-pen"></i> Ubah</a>
                                                <div class="dropdown-divider"></div>
                                                <a href="javascript:void(0)" role="button" class="dropdown-item has-icon text-danger trigger--fire-modal-1" data-confirm="Yakin mau hapus data ini? " data-confirm-text-yes="Ya"><i class="fas fa-trash-alt"></i> Hapus</a>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table-border">
                                        <thead><tr><th>Informasi</th><th>Besaran</th></tr></thead>
                                        <tbody>
                                            <tr>
                                                <th>Stok Awal</th>
                                                <td>{{ number_format($t->stok_awal, 6, '.', ',') }}</td>
                                            </tr>
                                            @if($t->type_movement == 'Stok Taking')
                                            <tr>
                                                <th>Losses / Profit</th>
                                                <td>{{ ($t->simbol == 'kurang' ? '-' : '+') . ' '. number_format($t->jumlah, 6, '.', ',') }}</td>
                                            </tr>
                                            @endif
                                            @if($t->type_movement == 'Good Transfer Send')
                                            <tr>
                                                <th>Stok Dipindah</th>
                                                <td>{{ number_format($t->stok_akhir - $t->stok_awal, 6, '.', ',') }}</td>
                                            </tr>
                                            @endif
                                            @if($t->type_movement == 'Good Transfer Receive')
                                            <tr>
                                                <th>Stok Terpindah</th>
                                                <td>{{ number_format($t->stok_akhir - $t->stok_awal, 6, '.', ',') }}</td>
                                            </tr>
                                            @endif
                                            @if($t->type_movement == 'Good Receive Penerimaan')
                                            <tr>
                                                <th>Stok Terisi</th>
                                                <td>{{ number_format($t->stok_akhir - $t->stok_awal, 6, '.', ',') }}</td>
                                            </tr>
                                            @endif
                                            @if($t->type_movement == 'Stok Taking Good Receive Penerimaan' || $t->type_movement == 'Stok Taking Good Transfer')
                                            <tr>
                                                <th>Penjualan / Losses</th>
                                                <td>{{ number_format($t->stok_akhir - $t->stok_awal, 6, '.', ',') }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <th>Stok Akhir</th>
                                                <td>{{ number_format($t->stok_akhir, 6, '.', ',') }}</td>
                                            </tr>
                                            @if($t->type_movement == 'Good Issue Sales')
                                            <tr>
                                                <th>Stand Meter Awal</th>
                                                <td>{{ number_format($t->stand_meter_awal, 6, '.', ',') }} Liter</td>
                                            </tr>
                                            <tr>
                                                <th>Penjualan</th>
                                                <td>{{ number_format($t->stand_meter_akhir - $t->stand_meter_awal, 6, '.', ',') }} Liter</td>
                                            </tr>
                                            <tr>
                                                <th>Stand Meter Awal</th>
                                                <td>{{ number_format($t->stand_meter_akhir, 6, '.', ',') }} Liter</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="d-none temp-depth">{!! $t !!}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info" role="alert">
                        Tidak ada data
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endfor
</div>
@endsection
