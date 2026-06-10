@extends('welcome.welcome')

@section('content')
    <div class="row">
        <div class="col-12 col-lg-8 order-2 order-lg-1">
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
                    <p>Belum ada artikel yang tersedia saat ini.</p>
                </div>
            @endif
        </div>
        <div class="col-12 col-lg-4 order-1 order-lg-2">
            @include('welcome.partials.sidebar')
        </div>
    </div>
@endsection
