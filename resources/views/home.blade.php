@extends('layouts.app')

@section('content')
@if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur')
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary">
                <i class="far fa-user"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Total Member</h4>
                </div>
                <div class="card-body">
                    {{$data['member']}}
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-danger">
                <i class="fas fa-tint"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Produk</h4>
                </div>
                <div class="card-body">
                    {{$data['produk']}}
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-warning">
                <i class="fas fa-truck"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Pengiriman</h4>
                </div>
                <div class="card-body">
                    {{$data['pengiriman']}}
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-success">
                <i class="fas fa-thermometer-half"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Dombak</h4>
                </div>
                <div class="card-body">
                    {{$data['dombak']}}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Apa yang Ingin saya lakukan ? </h4>
            </div>
            <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-sm-12 col-12 col-md-6">
                            <select name="doing" id="doing" class="custom-select">
                                <option value="0">Saya Ingin Memperoleh data ... </option>
                                <option value="how_many_stok_in_dombak">Berapa Stok Saya di Dombak Hari Ini</option>
                                <option value="how_many_stok_in_pertamina">Berapa Stok Saya di Pertamina Hari Ini</option>
                                <option value="how_many_sales_today">Berapa Banyak Penjualan Minyak Hari Ini</option>
                                <option value="how_many_profit_or_loss_today">Berapa Akumulasi Profit / Loss Hari Ini</option>
                                <option value="download_margin">Saya Ingin Mengunduh Laporan Margin Bulanan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-sm-12 col-12 col-md-6">
                            <div class="value_ai d-none py-2"></div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
{{-- <div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Filter</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('home') }}" method="get">
                    <div class="row">
                        <div class="col-sm-12 col-12 col-md-6 col-lg-3">
                            <input type="date" name="waktu_mulai" id="waktuMulai" class="form-control" value="{{ $waktuMulai }}" />
                        </div>
                        <div class="col-sm-12 col-12 col-md-6 col-lg-3">
                            <input type="date" name="waktu_selesai" id="waktuSelesai" class="form-control" value="{{ $waktuSelesai }}" />
                        </div>
                        <div class="col-sm-12 col-12 col-md-6 col-lg-3">
                            <button type="submit" class="btn btn-success btn-block">Filter</button>
                        </div>
                        <div class="col-sm-12 col-12 col-md-6 col-lg-3"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> --}}
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><h4>{{ __('Dashboard Stok Hari Ini') }}</h4></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-6 col-xl-6">
                        <canvas id="chart-stok"></canvas>
                    </div>
                    <div class="col-12 col-md-12 col-lg-6 col-xl-6">
                        <canvas id="chart-stok-split"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><h4>{{ __('Dashboard Penjualan Hari Ini') }}</h4></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-6 col-xl-6">
                        <canvas id="chart-penjualan"></canvas>
                    </div>
                    <div class="col-12 col-md-12 col-lg-6 col-xl-6">
                        <canvas id="chart-penjualan-split"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Menu</h4>
            </div>
        </div>
    </div>
    @foreach($allMenu as $menu)
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-{{ $menu['color'] }}">
                <i class="fas fa-{{ $menu['icon'] }}"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>{{ $menu['nama'] }}</h4>
                </div>
                <div class="card-body">
                    <a href="{{ $menu['url'] }}" class="btn btn-{{$menu['color']}}" type="button" title="Menu">Buka Menu</a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Pembayaran</h4>
                <div class="card-header-action">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-danger">Lihat lebih <i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive table-invoice">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID Pembayaran</th>
                                <th>Tanggal</th>
                                <th>Nama Produk</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($pembayaran->count() > 0)
                            @foreach($pembayaran as $pe)
                            <tr>
                                <td><a href="{{ route('pembayaran.show', $pe->rencana_pembelian_id) }}">INV-52354</a></td>
                                <td>{{ $pe->tanggal_pembelian }}</td>
                                <td class="font-weight-600">
                                    @php
                                        $temp = [];
                                        foreach($pe->detail_rencana_pembelian()->select('produk.nama_produk')->join('produk', 'produk.produk_id', 'detail_rencana_pembelian.produk_id')->get() as $p) {
                                            $temp[] = $p['nama_produk'];
                                        }
                                        echo join(', ', $temp);
                                    @endphp
                                </td>
                                <td>Rp. {{ number_format($pe->grand_total, 2, ".", ",") }}</td>
                                <td><div class="badge badge-warning">Unpaid</div></td>
                                <td><a href="{{ route('pembayaran.show', $pe->rencana_pembelian_id) }}" class="btn btn-primary">Detail</a></td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="6"><p class="text-center">Tidak ada yang perlu dibayarkan</p></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-hero">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h4>{{ $delivery->count() }}</h4>
                <div class="card-description">Dalam Pengiriman</div>
            </div>
            <div class="card-body p-0">
                <div class="tickets-list">
                    @if($delivery->count() > 0)
                    @foreach($delivery as $d)
                    @foreach($d->file_lo as $lo)
                    <a href="{{ route('pengiriman.file_lo.show', [$lo->pengiriman()->first()->pengiriman_id, $lo->file_lo_id]) }}" class="ticket-item">
                        <div class="ticket-title">
                            <h4>{{ $lo->produk()->first()->nama_produk }}</h4>
                        </div>
                        <div class="ticket-info">
                            <div>{{ number_format($lo->jumlah, 0, ".", ',') .' ' . $lo->satuan_jumlah }}</div>
                            <div class="bullet"></div>
                            <div>{{ $lo->tanggal_upload }}</div>
                        </div>
                    </a>
                    @endforeach
                    @endforeach
                    @else
                    <p class="text-center my-5">Tidak ada Pengiriman</p>
                    @endif
                    <a href="{{ route('pengiriman.index') }}" class="ticket-item ticket-more">View All <i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator')
<div class="row">
    <div class="col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-primary">
                <i class="far fa-user"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Selamat Datang</h4>
                </div>
                <div class="card-body">
                    {{ Auth::user()->member()->first()->ktp()->first()->nama }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-danger">
                <i class="fas fa-address-card"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>Jabatan</h4>
                </div>
                <div class="card-body">
                    {{Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4>Menu</h4>
            </div>
        </div>
    </div>
    @foreach($allMenu as $menu)
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-{{ $menu['color'] }}">
                <i class="fas fa-{{ $menu['icon'] }}"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>{{ $menu['nama'] }}</h4>
                </div>
                <div class="card-body">
                    <a href="{{ $menu['url'] }}" class="btn btn-{{$menu['color']}}" type="button" title="Menu">Buka Menu</a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
