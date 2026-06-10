@extends('welcome.welcome')

@section('hero_title', $post->title)
@section('hero_subtitle', 'Detail pengumuman & informasi resmi SPBU.')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8 order-2 order-lg-1">
            <article class="article-detail">
                <a href="{{ url()->previous() }}" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali</a>
                <h1>{{ $post->title }}</h1>

                <div class="detail-meta">
                    <span><i class="far fa-user"></i>
                        Oleh <a href="{{ route('welcome.user', $post->member_id) }}">{{ optional(optional($post->member()->first())->ktp()->first())->nama ?? 'Anonim' }}</a>
                    </span>
                    <span><i class="far fa-calendar"></i> {{ $post->created_at->format('d F Y') }}</span>
                </div>

                @if ($post->kategori()->count() > 0)
                    <div class="taxonomy">
                        <span class="lbl"><i class="fas fa-folder"></i> Kategori:</span>
                        @foreach ($post->kategori()->get() as $b)
                            <a href="{{ route('welcome.kategori', $b->kategori_id) }}" class="chip">{{ $b->nama_kategori }}</a>
                        @endforeach
                    </div>
                @endif

                @if ($post->tag()->count() > 0)
                    <div class="taxonomy">
                        <span class="lbl"><i class="fas fa-hashtag"></i> Tag:</span>
                        @foreach ($post->tag()->get() as $b)
                            <a href="{{ route('welcome.tag', $b->tag_id) }}" class="chip chip-tag">#{{ $b->nama_tag }}</a>
                        @endforeach
                    </div>
                @endif

                <div class="content">{{ $post->content }}</div>
            </article>
        </div>
        <div class="col-12 col-lg-4 order-1 order-lg-2">
            @include('welcome.partials.sidebar')
        </div>
    </div>
@endsection
