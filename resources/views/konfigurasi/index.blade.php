@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Konfigurasi</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Konfigurasi</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="section-title mb-0">Ringkasan Konfigurasi</div>
            <p class="text-muted">Struktur organisasi, jabatan, dan pengaturan hari kerja.</p>
        </div>
        @include('partials.chart-card', [
            'id' => 'chartKonfigRingkasan',
            'title' => 'Jumlah Data Konfigurasi',
            'icon' => 'cogs',
            'color' => 'primary',
            'badge' => 'Data',
            'col' => 'col-lg-5 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartJabatanLevel',
            'title' => 'Jabatan per Level',
            'icon' => 'sitemap',
            'color' => 'warning',
            'badge' => 'Jabatan',
            'col' => 'col-lg-4 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartHariKerja',
            'title' => 'Hari Kerja vs Libur',
            'icon' => 'calendar-day',
            'color' => 'info',
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
            window.spbuChart.bar('chartKonfigRingkasan', D.ringkasan, { horizontal: true });
            window.spbuChart.bar('chartJabatanLevel', D.jabatanLevel, { colorEach: false, colorIndex: 2, label: 'Jabatan' });
            window.spbuChart.doughnut('chartHariKerja', D.hari);
        });
    </script>
@endpush
