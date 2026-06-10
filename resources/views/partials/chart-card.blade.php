{{--
    Reusable dashboard chart card.
    Params: id (canvas id), title, icon (FontAwesome name), color (stisla color),
            badge (optional pill text), col (optional column classes)
--}}
<div class="{{ $col ?? 'col-lg-6 col-12' }}">
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-{{ $icon ?? 'chart-bar' }} text-{{ $color ?? 'primary' }} mr-2"></i> {{ $title }}</h4>
            @isset($badge)
                <div class="card-header-action">
                    <span class="badge badge-{{ $color ?? 'primary' }}">{{ $badge }}</span>
                </div>
            @endisset
        </div>
        <div class="card-body">
            <div class="chart-shell">
                <canvas id="{{ $id }}"></canvas>
            </div>
        </div>
    </div>
</div>
