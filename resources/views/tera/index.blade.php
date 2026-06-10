@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Tera</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Tera</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="section-title mb-0">Ringkasan Tera</div>
            <p class="text-muted">Frekuensi dan nilai kegiatan tera dalam 12 bulan terakhir.</p>
        </div>
        @include('partials.chart-card', [
            'id' => 'chartTeraJumlah',
            'title' => 'Jumlah Tera per Bulan',
            'icon' => 'clipboard-check',
            'color' => 'primary',
            'badge' => 'Kegiatan',
            'col' => 'col-lg-6 col-12',
        ])
        @include('partials.chart-card', [
            'id' => 'chartTeraNilai',
            'title' => 'Nilai Tera per Bulan',
            'icon' => 'coins',
            'color' => 'success',
            'badge' => 'Rupiah',
            'col' => 'col-lg-6 col-12',
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
            window.spbuChart.bar('chartTeraJumlah', D.jumlah, { colorEach: false, colorIndex: 0, label: 'Tera' });
            window.spbuChart.line('chartTeraNilai', D.nilai, { money: true, label: 'Nilai Tera' });
        });
    </script>
@endpush
