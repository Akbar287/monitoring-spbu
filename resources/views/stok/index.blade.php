@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Manajemen Persediaan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Manajemen Persediaan</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="section-title mb-0">Ringkasan Persediaan</div>
            <p class="text-muted">Sebaran stok BBM, tren penerimaan, dan akumulasi losses.</p>
        </div>
        @include('partials.chart-card', [
            'id' => 'chartStok',
            'title' => 'Komposisi Stok per Produk',
            'icon' => 'gas-pump',
            'color' => 'primary',
            'badge' => 'Liter',
            'col' => 'col-lg-4 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartPenerimaan',
            'title' => 'Penerimaan BBM per Bulan',
            'icon' => 'arrow-down',
            'color' => 'info',
            'badge' => 'Dokumen',
            'col' => 'col-lg-5 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartLosses',
            'title' => 'Losses (Lebih / Kurang)',
            'icon' => 'balance-scale',
            'color' => 'danger',
            'badge' => 'Liter',
            'col' => 'col-lg-3 col-12',
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
            window.spbuChart.doughnut('chartStok', D.stok, { unit: 'L' });
            window.spbuChart.bar('chartPenerimaan', D.penerimaan, { colorEach: false, colorIndex: 4, label: 'Penerimaan' });
            window.spbuChart.bar('chartLosses', D.losses, { unit: 'L' });
        });
    </script>
@endpush
