@extends('welcome.welcome')

@section('hero_title', 'Artikel Berdasarkan Tag')
@section('hero_subtitle', 'Jelajahi artikel yang berkaitan dengan topik yang Anda minati.')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8 order-2 order-lg-1">
            <div class="portal-heading">
                <div class="ph-ico"><i class="fas fa-hashtag"></i></div>
                <div>
                    <div class="ph-label">Tag</div>
                    <div class="ph-value">#{{ $tag->nama_tag }}</div>
                </div>
            </div>

            @if ($artikel->count() > 0)
                <div class="row">
                    @foreach ($artikel as $a)
                        @include('welcome.partials.article-card', ['a' => $a, 'i' => $loop->index])
                    @endforeach
                </div>
                <div class="d-flex justify-content-center mt-2">
                    {!! $artikel->links() !!}
                </div>
            @else
                <div class="empty-state">
                    <i class="far fa-folder-open"></i>
                    <p>Tidak ada artikel dengan tag <strong>#{{ $tag->nama_tag }}</strong>.</p>
                </div>
            @endif
        </div>
        <div class="col-12 col-lg-4 order-1 order-lg-2">
            @include('welcome.partials.sidebar')
        </div>
    </div>
@endsection
