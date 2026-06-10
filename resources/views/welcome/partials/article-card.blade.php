@php
    $palette = ['#6777ef', '#fc544b', '#ffa426', '#47c363', '#3abaf4', '#9b59b6'];
    $accent = $palette[($i ?? 0) % count($palette)];
    $penulis = optional(optional($a->member()->first())->ktp()->first())->nama ?? 'Anonim';
@endphp
<div class="col-12 col-md-6 mb-4">
    <article class="art-card">
        <div class="art-ico" style="background: linear-gradient(135deg, {{ $accent }}, {{ $accent }}cc);">
            <i class="fas fa-newspaper"></i>
        </div>
        <h3 class="art-title">{{ $a->title }}</h3>
        <div class="art-meta">
            <span><i class="far fa-user"></i> {{ $penulis }}</span>
            <span><i class="far fa-calendar"></i> {{ $a->created_at->format('d M Y') }}</span>
        </div>
        <p class="art-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($a->content), 120) }}</p>
        <a href="{{ route('welcome.show', $a->post_id) }}" class="art-read">
            Baca selengkapnya <i class="fas fa-arrow-right"></i>
        </a>
    </article>
</div>
