@extends('welcome.welcome')

@section('hero_title', 'Kategori Artikel')
@section('hero_subtitle', 'Kumpulan artikel dan pengumuman dalam kategori pilihan Anda.')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8 order-2 order-lg-1">
            <div class="portal-heading">
                <div class="ph-ico"><i class="fas fa-folder"></i></div>
                <div>
                    <div class="ph-label">Kategori</div>
                    <div class="ph-value">{{ $kategori->nama_kategori }}</div>
                    @if ($kategori->detail)
                        <p class="ph-detail">{{ $kategori->detail }}</p>
                    @endif
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
                    <p>Tidak ada artikel pada kategori <strong>{{ $kategori->nama_kategori }}</strong>.</p>
                </div>
            @endif
        </div>
        <div class="col-12 col-lg-4 order-1 order-lg-2">
            @include('welcome.partials.sidebar')
        </div>
    </div>
@endsection
