@extends('welcome.welcome')
@section('content')
<div class="row">
    <div class="col-12 col-sm-12 col-lg-8 order-2 order-md-2 order-lg-1">
        <div class="card card-primary">
            <div class="card-header d-flex">
                <a href="{{ url()->previous() }}" class="btn btn-primary"><i class="fas fa fa-arrow-left"></i></a>
                <h4 class="card-title ml-2">{{ $post->title }}</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-12 mb-1">
                        <p>Dibuat oleh <a href="{{ route('welcome.user', $post->member_id) }}">{{ $post->member()->first()->ktp()->first()->nama }}</a> pada tanggal {{ $post->created_at->format('d F Y') }}</p>
                    </div>
                    @if($post->kategori()->count() > 0)
                    <div class="col-12 col-sm-12 mb-2">
                        <div class="d-flex align-center">
                            <span class="mr-2">Kategori: </span>
                            @foreach($post->kategori()->get() as $b)
                            <a href="{{ route('welcome.kategori', $b->kategori_id) }}" class="mr-2">
                                {{ $b->nama_kategori }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if($post->tag()->count() > 0)
                    <div class="col-12 col-sm-12 ">
                        <div class="d-flex align-center">
                            <span class="mr-2">Tag: </span>
                            @foreach($post->tag()->get() as $b)
                            <a href="{{ route('welcome.tag', $b->tag_id) }}" class="badge badge-primary mr-2">#
                                {{ $b->nama_tag }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-12 col-sm-12 {{ $post->kategori()->count() > 0 || $post->tag()->count() > 0 ? 'mt-5' : '' }}">
                        <p class="card-content">{{ $post->content }}</p>
                    </div>
                </div>
            </div>
        </div>
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
