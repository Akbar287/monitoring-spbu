@extends('welcome.welcome')
@section('content')
<div class="row">
    <div class="col-12 col-sm-12 col-lg-8 order-2 order-md-2 order-lg-1">
        @if($artikel->count() > 0)
        <div class="row">
            @foreach ($artikel as $a)
            <div class="col-12 col-sm-12 ">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $a->title }}</h5>
                        <p class="card-subtitle">Dibuat oleh {{ $a->member()->first()->ktp()->first()->nama }} pada tanggal {{ $a->created_at->format('d F Y') }}</p>
                        <p class="card-text">{{ substr($a->content, 0, 50) }}</p>
                        <a href="{{ route('welcome.show', $a->post_id) }}" class="btn btn-primary">Baca</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-12 col-sm-12">
                <div class="d-flex justify-content-center">
                    {!! $artikel->links() !!}
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-info">Tidak Ada Artikel yang tersedia</div>
        @endif
    </div>
    <div class="col-12 col-sm-12 col-lg-4 order-1 order-md-1 order-lg-2">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Login ke Aplikasi</h5>
                <p class="card-text">Gunakan Username dan Password</p>
                <a href="{{ url('/login') }}" class="btn btn-primary">Login</a>
            </div>
        </div>
    </div>
</div>

@endsection
