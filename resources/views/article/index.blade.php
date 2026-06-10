@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Artikel</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Artikel</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="section-title mb-0">Ringkasan Konten</div>
            <p class="text-muted">Jumlah konten, status publikasi, dan tren publikasi artikel.</p>
        </div>
        @include('partials.chart-card', [
            'id' => 'chartKontenRingkasan',
            'title' => 'Jumlah Konten',
            'icon' => 'newspaper',
            'color' => 'primary',
            'badge' => 'Item',
            'col' => 'col-lg-4 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartPostStatus',
            'title' => 'Status Publikasi Post',
            'icon' => 'toggle-on',
            'color' => 'success',
            'col' => 'col-lg-3 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartPostBulanan',
            'title' => 'Post Diterbitkan per Bulan',
            'icon' => 'chart-line',
            'color' => 'info',
            'badge' => 'Post',
            'col' => 'col-lg-5 col-12',
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
            window.spbuChart.bar('chartKontenRingkasan', D.ringkasan, { horizontal: true });
            window.spbuChart.doughnut('chartPostStatus', D.status);
            window.spbuChart.line('chartPostBulanan', D.bulanan, { label: 'Post' });
        });
    </script>
@endpush
