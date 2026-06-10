@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Jadwal &amp; Absensi</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Jadwal &amp; Absensi</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="section-title mb-0">Ringkasan Kehadiran</div>
            <p class="text-muted">Komposisi status presensi, penjadwalan, dan tren kehadiran pegawai.</p>
        </div>
        @include('partials.chart-card', [
            'id' => 'chartPresensiStatus',
            'title' => 'Presensi per Status',
            'icon' => 'user-check',
            'color' => 'info',
            'col' => 'col-lg-4 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartPenjadwalanStatus',
            'title' => 'Penjadwalan per Status',
            'icon' => 'calendar-check',
            'color' => 'primary',
            'col' => 'col-lg-4 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartPresensiBulanan',
            'title' => 'Presensi per Bulan',
            'icon' => 'chart-line',
            'color' => 'success',
            'badge' => 'Presensi',
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
            window.spbuChart.doughnut('chartPresensiStatus', D.presensi);
            window.spbuChart.doughnut('chartPenjadwalanStatus', D.penjadwalan);
            window.spbuChart.bar('chartPresensiBulanan', D.bulanan, { colorEach: false, colorIndex: 3, label: 'Presensi' });
        });
    </script>
@endpush
