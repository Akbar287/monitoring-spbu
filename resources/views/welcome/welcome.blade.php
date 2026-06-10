<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="route" content="{{ Route::currentRouteName() }}">

    <title>{{ config('app.name', 'Monitoring SPBU') }} — Portal Informasi</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        :root {
            --pri: #6777ef;
            --pri-dark: #4f5fdc;
            --ink: #1f2440;
            --muted: #8089a8;
            --bg: #f5f6fb;
        }

        body.portal {
            background: var(--bg);
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            color: var(--ink);
            margin: 0;
        }

        /* ===== Top bar ===== */
        .portal-nav {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, .85);
            backdrop-filter: saturate(180%) blur(12px);
            border-bottom: 1px solid rgba(31, 36, 64, .06);
        }

        .portal-nav .inner {
            max-width: 1140px;
            margin: 0 auto;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .portal-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 18px;
            color: var(--ink);
            text-decoration: none;
        }

        .portal-brand .logo {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 17px;
            background: linear-gradient(135deg, var(--pri), var(--pri-dark));
            box-shadow: 0 6px 16px rgba(103, 119, 239, .4);
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--pri), var(--pri-dark));
            color: #fff;
            border: 0;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            box-shadow: 0 6px 16px rgba(103, 119, 239, .35);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(103, 119, 239, .45);
            color: #fff;
            text-decoration: none;
        }

        /* ===== Hero ===== */
        .portal-hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #5b6bf0 0%, #6a4ff0 55%, #8b4ff0 100%);
            color: #fff;
            padding: 64px 20px 96px;
            text-align: center;
        }

        .portal-hero:before,
        .portal-hero:after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
        }

        .portal-hero:before {
            width: 280px;
            height: 280px;
            top: -120px;
            right: -60px;
        }

        .portal-hero:after {
            width: 200px;
            height: 200px;
            bottom: -110px;
            left: -40px;
        }

        .portal-hero .badge-pill-soft {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, .16);
            padding: 7px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .portal-hero h1 {
            font-weight: 800;
            font-size: clamp(28px, 5vw, 44px);
            margin: 0 0 12px;
            position: relative;
        }

        .portal-hero p {
            font-size: 16px;
            opacity: .92;
            max-width: 560px;
            margin: 0 auto;
            position: relative;
        }

        /* pull content up over the hero curve */
        .portal-main {
            max-width: 1140px;
            margin: -56px auto 60px;
            padding: 0 20px;
            position: relative;
            z-index: 2;
        }

        /* ===== Article cards ===== */
        .art-card {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            height: 100%;
            box-shadow: 0 8px 26px rgba(31, 36, 64, .07);
            border: 1px solid rgba(31, 36, 64, .04);
            transition: transform .2s ease, box-shadow .2s ease;
            display: flex;
            flex-direction: column;
        }

        .art-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 38px rgba(31, 36, 64, .13);
        }

        .art-ico {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 20px;
            margin-bottom: 16px;
            box-shadow: 0 8px 18px rgba(31, 36, 64, .18);
        }

        .art-title {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.35;
            margin: 0 0 12px;
            color: var(--ink);
        }

        .art-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: 12.5px;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .art-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .art-excerpt {
            font-size: 14px;
            color: #5c637f;
            line-height: 1.6;
            margin: 0 0 18px;
            flex: 1;
        }

        .art-read {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            align-self: flex-start;
            font-weight: 700;
            font-size: 14px;
            color: var(--pri);
            text-decoration: none;
        }

        .art-read i {
            transition: transform .18s ease;
        }

        .art-read:hover {
            color: var(--pri-dark);
            text-decoration: none;
        }

        .art-read:hover i {
            transform: translateX(4px);
        }

        /* ===== Filter / page heading chip ===== */
        .portal-heading {
            background: #fff;
            border-radius: 16px;
            padding: 18px 22px;
            margin-bottom: 22px;
            box-shadow: 0 8px 24px rgba(31, 36, 64, .06);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .portal-heading .ph-ico {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 18px;
            background: linear-gradient(135deg, var(--pri), var(--pri-dark));
        }

        .portal-heading .ph-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            font-weight: 700;
        }

        .portal-heading .ph-value {
            font-size: 18px;
            font-weight: 800;
            color: var(--ink);
        }

        .portal-heading .ph-detail {
            font-size: 13px;
            color: #5c637f;
            margin: 2px 0 0;
        }

        /* ===== Side widgets ===== */
        .widget {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 26px rgba(31, 36, 64, .07);
            margin-bottom: 22px;
        }

        .widget-cta {
            background: linear-gradient(135deg, #5b6bf0, #7a4ff0);
            color: #fff;
        }

        .widget h5 {
            font-weight: 800;
            font-size: 17px;
            margin: 0 0 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .widget p {
            font-size: 13.5px;
            line-height: 1.6;
            opacity: .92;
            margin: 0 0 16px;
        }

        .widget-cta .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: var(--pri-dark);
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: transform .18s ease;
        }

        .widget-cta .btn-cta:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: var(--pri-dark);
        }

        .tip-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tip-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13.5px;
            color: #5c637f;
            padding: 8px 0;
        }

        .tip-list li i {
            color: var(--pri);
            margin-top: 3px;
        }

        /* ===== Chips ===== */
        .chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            background: rgba(103, 119, 239, .1);
            color: var(--pri-dark);
            transition: background .15s ease;
        }

        .chip:hover {
            background: rgba(103, 119, 239, .2);
            text-decoration: none;
            color: var(--pri-dark);
        }

        .chip-tag {
            background: rgba(252, 84, 75, .1);
            color: #d63031;
        }

        .chip-tag:hover {
            background: rgba(252, 84, 75, .2);
            color: #d63031;
        }

        /* ===== Article detail ===== */
        .article-detail {
            background: #fff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 8px 26px rgba(31, 36, 64, .07);
        }

        .article-detail .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            margin-bottom: 18px;
        }

        .article-detail .back-btn:hover {
            color: var(--pri);
            text-decoration: none;
        }

        .article-detail h1 {
            font-weight: 800;
            font-size: clamp(24px, 4vw, 34px);
            line-height: 1.25;
            margin: 0 0 16px;
        }

        .article-detail .detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 13.5px;
            color: var(--muted);
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(31, 36, 64, .07);
            margin-bottom: 22px;
        }

        .article-detail .detail-meta a {
            color: var(--pri);
            font-weight: 600;
        }

        .article-detail .taxonomy {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .article-detail .taxonomy .lbl {
            font-size: 13px;
            font-weight: 700;
            color: var(--muted);
            margin-right: 2px;
        }

        .article-detail .content {
            font-size: 15.5px;
            line-height: 1.85;
            color: #3d4361;
            white-space: pre-line;
            margin-top: 18px;
        }

        /* ===== Empty state ===== */
        .empty-state {
            background: #fff;
            border-radius: 18px;
            padding: 48px 24px;
            text-align: center;
            box-shadow: 0 8px 26px rgba(31, 36, 64, .07);
        }

        .empty-state i {
            font-size: 44px;
            color: #cdd2e6;
            margin-bottom: 14px;
        }

        .empty-state p {
            color: var(--muted);
            font-size: 15px;
            margin: 0;
        }

        /* ===== Pagination ===== */
        .portal-main .pagination {
            gap: 6px;
            flex-wrap: wrap;
        }

        .portal-main .pagination .page-link {
            border: 0;
            border-radius: 10px;
            color: var(--ink);
            font-weight: 600;
            min-width: 40px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(31, 36, 64, .05);
        }

        .portal-main .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--pri), var(--pri-dark));
            color: #fff;
        }

        .portal-footer {
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            padding: 24px 20px 36px;
        }
    </style>
</head>

<body class="portal antialiased">
    <header class="portal-nav">
        <div class="inner">
            <a href="{{ route('welcome') }}" class="portal-brand">
                <span class="logo"><i class="fas fa-gas-pump"></i></span>
                Monitoring SPBU
            </a>
            <a href="{{ url('/login') }}" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</a>
        </div>
    </header>

    <section class="portal-hero">
        <span class="badge-pill-soft"><i class="fas fa-bullhorn"></i> Portal Informasi Publik</span>
        <h1>@yield('hero_title', 'Pengumuman & Berita Terkini')</h1>
        <p>@yield('hero_subtitle', 'Ikuti pemberitahuan, pengumuman, dan informasi resmi seputar layanan SPBU kami.')</p>
    </section>

    <main class="portal-main">
        @yield('content')
    </main>

    <footer class="portal-footer">
        &copy; {{ now()->year }} Monitoring SPBU &middot; Portal Informasi Publik
    </footer>
</body>

</html>
