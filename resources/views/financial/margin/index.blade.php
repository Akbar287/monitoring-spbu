@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Laporan Margin</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.financial') }}">Financial</a></div>
        <div class="breadcrumb-item">Laporan Margin</div>
    </div>
</div>

<div class="section-body">
    <h2 class="section-title">Margin</h2>
    <p class="section-lead">
        Laporan Margin.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Laporan Margin') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('margin.index') }}" method="get"> @csrf
                        <div class="row mb-4">
                            <div class="col-12 col-sm-12 col-md-3 mb-2">
                                <select name="spbu_id" id="spbu_id" class="custom-select" @if($spbu->count() == 0) disabled @endif>
                                    @if($spbu->count() > 0)
                                    @foreach($spbu as $s)
                                    <option value="{{ $s->spbu_id }}" {{ is_null($spbuSelected) ? ($s->spbu_id == $spbuSelected ? 'selected' : '') : '' }}>{{ $s->nama_spbu }}</option>
                                    @endforeach
                                    @else
                                    <option value="0">SPBU tidak ditemukan</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 mb-2">
                                <select name="type_report_id" id="type_report_id" class="custom-select" @if(count($typeReport) == 0) disabled @endif>
                                    @if(count($typeReport) > 0)
                                    @foreach($typeReport as $s)
                                    <option value="{{ $s['type_report_id'] }}" {{($s['type_report_id'] == $typeReportSelected ? 'selected' : '') }}>{{ $s['nama_type_report'] }}</option>
                                    @endforeach
                                    @else
                                    <option value="0">Jenis Laporan tidak ditemukan</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 mb-2">
                                <select name="produk_id" id="produk_id" class="custom-select" @if($produk->count() == 0) disabled @endif>
                                    @if($produk->count() > 0)
                                    <option value="all" {{ is_null($produkSelected) ? ('all' == $produkSelected ? 'selected' : '') : '' }}>Semua Produk</option>
                                    @foreach($produk as $s)
                                    <option value="{{ $s->produk_id }}" {{ is_null($produkSelected) ? ($s->produk_id == $produkSelected ? 'selected' : '') : '' }}>{{ $s->nama_produk }}</option>
                                    @endforeach
                                    @else
                                    <option value="0">Produk tidak ditemukan</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 mb-2">
                                <input type="month" name="month" id="month" class="form-control" value="{{ $monthSelected }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-3 mb-2">
                                <button type="unduh" title="Filter" class="btn btn-success btn-block">Unduh</button>
                            </div>
                        </div>
                    </form>
                    <div class="row mb-4">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
