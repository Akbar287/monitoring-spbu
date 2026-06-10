@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Master Data</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Master Data</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="section-title mb-0">Ringkasan Master Data</div>
            <p class="text-muted">Komposisi aset, persediaan, dan harga yang menjadi dasar operasional SPBU.</p>
        </div>
        @include('partials.chart-card', [
            'id' => 'chartRingkasan',
            'title' => 'Jumlah Entitas Master',
            'icon' => 'database',
            'color' => 'primary',
            'badge' => 'Unit',
            'col' => 'col-lg-4 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartStokProduk',
            'title' => 'Komposisi Stok per Produk',
            'icon' => 'gas-pump',
            'color' => 'info',
            'badge' => 'Liter',
            'col' => 'col-lg-4 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartHargaProduk',
            'title' => 'Harga Jual per Produk',
            'icon' => 'tags',
            'color' => 'success',
            'badge' => 'Rupiah',
            'col' => 'col-lg-4 col-12',
        ])
    </div>

    <div class="row">
        <div class="col-12">
            <div class="section-title">Menu</div>
        </div>
        @include('partials.menu-tiles')
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.spbuChart) return;
            const D = @json($charts);
            window.spbuChart.bar('chartRingkasan', D.ringkasan, { horizontal: true });
            window.spbuChart.doughnut('chartStokProduk', D.stok, { unit: 'L' });
            window.spbuChart.bar('chartHargaProduk', D.harga, { money: true });
        });
    </script>
@endpush
