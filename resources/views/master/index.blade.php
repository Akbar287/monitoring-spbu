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
    @foreach($allMenu as $menu)
    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1">
            <div class="card-icon bg-{{ $menu['color'] }}">
                <i class="fas fa-{{ $menu['icon'] }}"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                    <h4>{{ $menu['nama'] }}</h4>
                </div>
                <div class="card-body">
                    <a href="{{ $menu['url'] }}" class="btn btn-{{$menu['color']}}" type="button" title="Menu">Buka Menu</a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection