@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Setoran</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Setoran</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="section-title mb-0">Ringkasan Setoran</div>
            <p class="text-muted">Tren penjualan, status setoran, dan kontribusi tiap shift.</p>
        </div>
        @include('partials.chart-card', [
            'id' => 'chartPenjualanBulanan',
            'title' => 'Total Penjualan per Bulan',
            'icon' => 'chart-line',
            'color' => 'primary',
            'badge' => 'Rupiah',
            'col' => 'col-lg-6 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartStatusSetoran',
            'title' => 'Penjualan per Status',
            'icon' => 'tasks',
            'color' => 'info',
            'col' => 'col-lg-3 col-md-6 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartPenjualanShift',
            'title' => 'Penjualan per Shift',
            'icon' => 'business-time',
            'color' => 'success',
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
            window.spbuChart.bar('chartPenjualanBulanan', D.bulanan, { money: true, colorEach: false, colorIndex: 0, label: 'Penjualan' });
            window.spbuChart.doughnut('chartStatusSetoran', D.status);
            window.spbuChart.bar('chartPenjualanShift', D.jamKerja, { money: true });
        });
    </script>
@endpush
