@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Rekap Pajak</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
        <div class="breadcrumb-item"><a href="{{ route('menu.financial') }}">Financial</a></div>
        <div class="breadcrumb-item">Rekap Pajak</div>
    </div>
</div>
<div class="section-body">
    <h2 class="section-title">Rekap Pajak</h2>
    <p class="section-lead">
        Rekap Pajak.
    </p>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Rekap Pajak') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('rekap-pajak.index') }}" method="get"> @csrf
                        <div class="row mb-4">
                            <div class="col-12 col-sm-12 col-md-3">
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
                            <div class="col-12 col-sm-12 col-md-3">
                                <input type="date" name="waktu_mulai" id="waktuMulai" class="form-control" value="{{ $waktuMulai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <input type="date" name="waktu_selesai" id="waktuSelesai" class="form-control" value="{{ $waktuSelesai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <button type="submit" class="btn btn-success btn-block">Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-hover table-rekap-pajak">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Kode Pembelian</th>
                                            <th>PPN</th>
                                            <th>PPH</th>
                                            <th>PPBKB</th>
                                            <th>Total Pajak</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>{{ __('Export Rekap Pajak') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('msg')){!! session('msg') !!} @endif
                    <form action="{{ route('rekap-pajak.export') }}" method="post"> @csrf
                        <div class="row mb-4">
                            <div class="col-12 col-sm-12 col-md-3">
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
                            <div class="col-12 col-sm-12 col-md-3">
                                <input type="date" name="waktu_mulai" id="waktuMulai" class="form-control" value="{{ $waktuMulai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <input type="date" name="waktu_selesai" id="waktuSelesai" class="form-control" value="{{ $waktuSelesai }}" />
                            </div>
                            <div class="col-12 col-sm-12 col-md-3">
                                <button type="submit" class="btn btn-success btn-block">Export Ke Excel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
