@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Procurement</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Procurement</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="section-title mb-0">Ringkasan Pengadaan</div>
            <p class="text-muted">Status pembelian, pengiriman, dan tren nilai pengadaan BBM.</p>
        </div>
        @include('partials.chart-card', [
            'id' => 'chartNilai',
            'title' => 'Nilai Pengadaan per Bulan',
            'icon' => 'chart-line',
            'color' => 'primary',
            'badge' => 'Rupiah',
            'col' => 'col-lg-6 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartStatus',
            'title' => 'Rencana Pembelian per Status',
            'icon' => 'tasks',
            'color' => 'warning',
            'col' => 'col-lg-3 col-md-6 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartPengiriman',
            'title' => 'Status Pengiriman',
            'icon' => 'truck',
            'color' => 'info',
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
            window.spbuChart.line('chartNilai', D.nilai, { money: true, label: 'Nilai Pengadaan' });
            window.spbuChart.doughnut('chartStatus', D.status);
            window.spbuChart.doughnut('chartPengiriman', D.pengiriman);
        });
    </script>
@endpush
