@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Financial</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Financial</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="section-title mb-0">Ringkasan Keuangan</div>
            <p class="text-muted">Tren pendapatan, arus petty cash, dan komposisi transaksi kas kecil.</p>
        </div>
        @include('partials.chart-card', [
            'id' => 'chartPendapatan',
            'title' => 'Pendapatan Penjualan per Bulan',
            'icon' => 'chart-line',
            'color' => 'success',
            'badge' => 'Rupiah',
            'col' => 'col-lg-6 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartPettyArus',
            'title' => 'Arus Petty Cash per Bulan',
            'icon' => 'wallet',
            'color' => 'primary',
            'badge' => 'Rupiah',
            'col' => 'col-lg-3 col-md-6 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartPettyJenis',
            'title' => 'Petty Cash per Jenis',
            'icon' => 'random',
            'color' => 'warning',
            'badge' => 'Rupiah',
            'col' => 'col-lg-3 col-md-6 col-12',
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
            window.spbuChart.line('chartPendapatan', D.penjualan, { money: true, label: 'Pendapatan' });
            window.spbuChart.bar('chartPettyArus', D.pettyArus, { money: true, colorEach: false, colorIndex: 0, label: 'Petty Cash' });
            window.spbuChart.doughnut('chartPettyJenis', D.pettyJenis, { money: true });
        });
    </script>
@endpush
