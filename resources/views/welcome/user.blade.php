@extends('welcome.welcome')

@section('hero_title', 'Artikel Penulis')
@section('hero_subtitle', 'Semua artikel dan pengumuman yang ditulis oleh penulis ini.')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8 order-2 order-lg-1">
            <div class="portal-heading">
                <div class="ph-ico"><i class="fas fa-user-pen"></i></div>
                <div>
                    <div class="ph-label">Penulis</div>
                    <div class="ph-value">{{ optional($member->ktp()->first())->nama ?? 'Anonim' }}</div>
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
                    <p>Penulis ini belum memiliki artikel yang tersedia.</p>
                </div>
            @endif
        </div>
        <div class="col-12 col-lg-4 order-1 order-lg-2">
            @include('welcome.partials.sidebar')
        </div>
    </div>
@endsection
