<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- General Info -->
    <meta name="nama" content="{{ Auth::user()->member()->first()->ktp()->first()->nama }}" />
    <meta name="route" content="{{ Route::currentRouteName() }}" />
    <meta name="id_user" content="{{ Auth::user()->member()->first()->id_member }}" />

    <title>{{ config('app.name', 'Monitoring SPBU') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Icon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Google Chart -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <!-- Dashboard / menu-tile shared styles -->
    <style>
        .menu-tile {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 20px;
            height: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
            transition: transform .2s ease, box-shadow .2s ease;
            color: inherit;
            text-decoration: none;
        }

        .menu-tile:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.12);
            color: inherit;
            text-decoration: none;
        }

        .menu-tile .menu-ico {
            flex: 0 0 auto;
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.18);
        }

        .menu-tile-body {
            min-width: 0;
        }

        .menu-tile-body h4 {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 2px;
            color: #34395e;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .menu-tile-body span {
            font-size: 12px;
            color: #6c757d;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .menu-tile-body span i {
            font-size: 10px;
            transition: transform .2s ease;
        }

        .menu-tile:hover .menu-tile-body span i {
            transform: translateX(4px);
        }

        .chart-shell {
            position: relative;
            height: 300px;
        }

        .chart-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #adb5bd;
            font-size: 13px;
        }

        /* ============================================================
           GLOBAL MODERN THEME — navbar · sidebar · content
           ============================================================ */
        :root {
            --pri: #6777ef;
            --pri-dark: #4f5fdc;
            --ink: #1f2440;
            --muted: #8089a8;
        }

        body {
            background: #f2f4fc !important;
            font-family: 'Plus Jakarta Sans', 'Nunito', 'Segoe UI', sans-serif;
            color: #2b3052;
        }

        /* soft colorful glow behind the content area */
        .main-content {
            position: relative;
            margin-top: 28px;
        }

        .main-content:before {
            content: "";
            position: fixed;
            top: 0;
            right: 0;
            width: 60vw;
            height: 320px;
            pointer-events: none;
            z-index: 0;
            background:
                radial-gradient(circle at 80% 0%, rgba(103, 119, 239, .14), transparent 60%),
                radial-gradient(circle at 100% 20%, rgba(155, 89, 182, .12), transparent 55%);
        }

        .main-content > * {
            position: relative;
            z-index: 1;
        }

        /* ---- Cards ---- */
        .main-content .card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 6px 22px rgba(31, 36, 64, .06);
        }

        .main-content .card .card-header {
            border-bottom: 1px solid rgba(31, 36, 64, .06);
        }

        .main-content .card .card-header h4 {
            font-weight: 700;
            font-size: 16px;
            color: var(--ink);
        }

        .section-header h1,
        .section-title {
            font-weight: 800;
            color: var(--ink);
        }

        .main-footer,
        .main-content .main-footer {
            background: transparent;
            box-shadow: none;
        }

        /* ============================================================
           TOP NAVBAR
           ============================================================ */
        .navbar-bg {
            background: transparent !important;
        }

        .main-navbar {
            top: 14px;
            background: rgba(255, 255, 255, .88) !important;
            backdrop-filter: saturate(180%) blur(10px);
            box-shadow: 0 2px 18px rgba(31, 36, 64, .06);
            border: 0 !important;
        }

        .main-navbar .navbar-nav .nav-link {
            color: #5a6488 !important;
        }

        /* hamburger + action icons -> rounded buttons */
        .main-navbar .navbar-nav .nav-link-lg {
            width: 42px;
            height: 42px;
            display: grid !important;
            place-items: center;
            border-radius: 12px;
            background: #f1f3fb;
            margin-left: 8px;
            font-size: 16px;
            transition: background .18s ease, color .18s ease, transform .18s ease;
        }

        .main-navbar .navbar-nav .nav-link-lg:hover {
            background: rgba(103, 119, 239, .14);
            color: var(--pri) !important;
            transform: translateY(-1px);
        }

        .main-navbar .navbar-right .nav-link-user img {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border: 2px solid var(--pri);
            margin: 0 !important;
        }

        /* notification dot on the bell */
        .main-navbar a:has(.fa-bell) {
            position: relative;
        }

        .main-navbar a:has(.fa-bell):after {
            content: "";
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fc544b;
            border: 2px solid #fff;
        }

        .main-navbar a#logout-btn:hover {
            background: rgba(252, 84, 75, .14);
            color: #fc544b !important;
        }

        /* ============================================================
           SIDEBAR — white & minimal with colorful icon chips
           ============================================================ */
        .main-sidebar {
            background: #ffffff !important;
            box-shadow: 2px 0 22px rgba(31, 36, 64, .05);
            border: 0 !important;
        }

        .main-sidebar .sidebar-brand,
        .main-sidebar .sidebar-brand.sidebar-brand-sm {
            background: #ffffff !important;
            height: 64px;
        }

        .main-sidebar .sidebar-brand a {
            color: var(--ink) !important;
            font-weight: 800;
            letter-spacing: .4px;
        }

        .main-sidebar .sidebar-brand img {
            border-radius: 9px;
        }

        #sidebar-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: rgba(31, 36, 64, .12);
            border-radius: 6px;
        }

        .main-sidebar .sidebar-menu {
            padding: 6px 12px 30px;
        }

        .main-sidebar .sidebar-menu li.menu-header {
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 18px 14px 8px;
        }

        .main-sidebar .sidebar-menu > li {
            margin-bottom: 3px;
        }

        .main-sidebar .sidebar-menu > li > a {
            display: flex;
            align-items: center;
            padding: 9px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 13.5px;
            color: #4d5476 !important;
            transition: background .18s ease, color .18s ease;
        }

        .main-sidebar .sidebar-menu > li > a > i {
            width: 36px;
            min-width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-grid;
            place-items: center;
            margin-right: 12px;
            font-size: 15px;
            background: rgba(103, 119, 239, .12);
            color: var(--pri);
            transition: background .18s ease, color .18s ease;
        }

        .main-sidebar .sidebar-menu > li > a:hover {
            background: #f3f4fd !important;
            color: var(--ink) !important;
        }

        /* colorful icon chips, cycled per item */
        .main-sidebar .sidebar-menu > li:nth-of-type(8n+1) > a > i { background: rgba(103, 119, 239, .12); color: #6777ef; }
        .main-sidebar .sidebar-menu > li:nth-of-type(8n+2) > a > i { background: rgba(252, 84, 75, .12); color: #fc544b; }
        .main-sidebar .sidebar-menu > li:nth-of-type(8n+3) > a > i { background: rgba(255, 164, 38, .14); color: #f6820d; }
        .main-sidebar .sidebar-menu > li:nth-of-type(8n+4) > a > i { background: rgba(71, 195, 99, .14); color: #2f9e4f; }
        .main-sidebar .sidebar-menu > li:nth-of-type(8n+5) > a > i { background: rgba(58, 186, 244, .14); color: #1d9bd7; }
        .main-sidebar .sidebar-menu > li:nth-of-type(8n+6) > a > i { background: rgba(155, 89, 182, .14); color: #9b59b6; }
        .main-sidebar .sidebar-menu > li:nth-of-type(8n+7) > a > i { background: rgba(0, 201, 167, .14); color: #00a98c; }
        .main-sidebar .sidebar-menu > li:nth-of-type(8n+8) > a > i { background: rgba(245, 83, 118, .14); color: #f55376; }

        /* active item -> gradient pill (higher specificity wins over chips) */
        .main-sidebar .sidebar-menu > li.active > a {
            background: linear-gradient(135deg, var(--pri), var(--pri-dark)) !important;
            color: #ffffff !important;
            box-shadow: 0 8px 18px rgba(103, 119, 239, .4);
        }

        .main-sidebar .sidebar-menu > li.active > a > i {
            background: rgba(255, 255, 255, .22) !important;
            color: #ffffff !important;
        }

        .main-sidebar .sidebar-menu > li > a.has-dropdown:after {
            color: var(--muted);
        }

        .main-sidebar .sidebar-menu > li.active > a.has-dropdown:after {
            color: rgba(255, 255, 255, .85);
        }

        /* ---- submenu (sub-nav): indented list with a connecting rail ---- */
        /* high-specificity selectors below also neutralise stisla's gray
           active-state background + deep 65px padding on submenu items */
        .main-sidebar .sidebar-menu li ul.dropdown-menu,
        .main-sidebar .sidebar-menu li.active ul.dropdown-menu {
            position: relative;
            background: transparent !important;
            padding: 2px 0 6px !important;
            margin: 2px 0 8px 28px !important;
        }

        /* the vertical rail */
        .main-sidebar .sidebar-menu li ul.dropdown-menu:before {
            content: "";
            position: absolute;
            left: 7px;
            top: 4px;
            bottom: 10px;
            width: 2px;
            border-radius: 2px;
            background: #e8eaf4;
        }

        .main-sidebar .sidebar-menu li ul.dropdown-menu li a {
            position: relative;
            display: flex;
            align-items: center;
            height: auto !important;
            min-height: 36px;
            padding: 8px 12px 8px 26px !important;
            margin: 1px 10px 1px 0;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 500;
            color: #7a829f !important;
            background: transparent !important;
            transition: background .18s ease, color .18s ease;
        }

        /* node on the rail */
        .main-sidebar .sidebar-menu li ul.dropdown-menu li a:before {
            content: "";
            position: absolute;
            left: 3px;
            top: 50%;
            transform: translateY(-50%);
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #cdd2e6;
            transition: border-color .18s ease, background .18s ease, box-shadow .18s ease;
        }

        .main-sidebar .sidebar-menu li ul.dropdown-menu li a:hover {
            background: #f1f2fc !important;
            color: var(--ink) !important;
        }

        .main-sidebar .sidebar-menu li.active ul.dropdown-menu li a:hover {
            background: #f1f2fc !important;
        }

        .main-sidebar .sidebar-menu li ul.dropdown-menu li.active > a {
            background: rgba(103, 119, 239, .12) !important;
            color: var(--pri-dark) !important;
            font-weight: 700;
        }

        .main-sidebar .sidebar-menu li ul.dropdown-menu li.active > a:before,
        .main-sidebar .sidebar-menu li ul.dropdown-menu li a:hover:before {
            background: var(--pri);
            border-color: var(--pri);
            box-shadow: 0 0 0 3px rgba(103, 119, 239, .18);
        }

        /* collapsed (mini) sidebar */
        body.sidebar-mini .main-sidebar .sidebar-menu > li > a {
            justify-content: center;
        }

        body.sidebar-mini .main-sidebar .sidebar-menu > li > a > i {
            margin-right: 0;
        }

        body.sidebar-mini .main-sidebar .sidebar-menu > li > ul.dropdown-menu {
            background: #ffffff !important;
            padding: 8px !important;
            border-radius: 12px;
            box-shadow: 0 14px 32px rgba(31, 36, 64, .16);
        }
    </style>
</head>

<body>
    <div id="app">
        <div class="main-wrapper">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                <div class="div-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
                    </ul>
                </div>
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown d-flex">
                        <a href="{{ url('/profil') }}" class="nav-link nav-link-lg nav-link-user">
                            <img alt="profile"
                                src="{{ asset('/storage/profile_img/' . Auth::user()->member()->first()->avatar) }}"
                                class="rounded-circle mr-1">
                        </a>
                        <a href="{{ url('/notifikasi') }}" class="nav-link nav-link-lg nav-link-user">
                            <i class="fas fa-bell"></i>
                        </a>
                        <a href="{{ route('logout') }}" class="nav-link nav-link-lg nav-link-user" id="logout-btn"
                            onclick="event.preventDefault();">
                            <i class="fas fa-sign-out-alt"></i>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf
                            </form>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="main-sidebar">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand align-items-center">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('/images/logo.jpg') }}" alt="logo" class="img-responsive" width="30">
                            SPBU</a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('/images/logo.jpg') }}" alt="logo" class="img-responsive" width="30">
                        </a>
                    </div>
                    <ul class="sidebar-menu">
                        @auth
                        <li class="menu-header">{{ Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level }}</li>
                        @else
                        <li class="menu-header">Menu Utama</li>
                        @endauth
                        <li class="{{ (Route::currentRouteName() == 'home') ? 'active': '' }}"><a class="nav-link" href="{{ url('/home') }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>

                        @auth
                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                        <li class="dropdown {{ (Route::currentRouteName() == 'aset.index' || Route::currentRouteName() == 'aset.create' || Route::currentRouteName() == 'aset.show' || Route::currentRouteName() == 'aset.edit' || Route::currentRouteName() == 'fasilitas.index' || Route::currentRouteName() == 'fasilitas.create' || Route::currentRouteName() == 'fasilitas.show' || Route::currentRouteName() == 'fasilitas.edit' || Route::currentRouteName() == 'produk.index' || Route::currentRouteName() == 'produk.create' || Route::currentRouteName() == 'produk.show' || Route::currentRouteName() == 'produk.edit' || Route::currentRouteName() == 'member.index' || Route::currentRouteName() == 'member.create' || Route::currentRouteName() == 'member.show' || Route::currentRouteName() == 'member.edit' || Route::currentRouteName() == 'dombak.index' || Route::currentRouteName() == 'dombak.create' || Route::currentRouteName() == 'dombak.show' || Route::currentRouteName() == 'dombak.edit' || Route::currentRouteName() == 'payung.index' || Route::currentRouteName() == 'payung.create' || Route::currentRouteName() == 'payung.show' || Route::currentRouteName() == 'payung.edit' || Route::currentRouteName() == 'dispenser.index' || Route::currentRouteName() == 'dispenser.create' || Route::currentRouteName() == 'dispenser.show' || Route::currentRouteName() == 'dispenser.edit' || Route::currentRouteName() == 'nozzle.index' || Route::currentRouteName() == 'nozzle.create' || Route::currentRouteName() == 'nozzle.show' || Route::currentRouteName() == 'nozzle.edit' || Route::currentRouteName() == 'fasilitas.file.index' || Route::currentRouteName() == 'fasilitas.file.create' || Route::currentRouteName() == 'fasilitas.file.show' || Route::currentRouteName() == 'fasilitas.file.edit' || Route::currentRouteName() == 'aset.file.index' || Route::currentRouteName() == 'aset.file.create' || Route::currentRouteName() == 'aset.file.show' || Route::currentRouteName() == 'aset.file.edit' || Route::currentRouteName() == 'payung.dombak'|| Route::currentRouteName() == 'payung.dombak.create' || Route::currentRouteName() == 'konversi.index' || Route::currentRouteName() == 'konversi.create' || Route::currentRouteName() == 'konversi.show' || Route::currentRouteName() == 'konversi.edit' || Route::currentRouteName() == 'member.jabatan.index' || Route::currentRouteName() == 'member.jabatan.create'|| Route::currentRouteName() == 'konversi.upload_show' || Route::currentRouteName() == 'harga.index' || Route::currentRouteName() == 'harga.create' || Route::currentRouteName() == 'harga.show' || Route::currentRouteName() == 'harga.edit' || Route::currentRouteName() == 'harga.riwayat.index' || Route::currentRouteName() == 'harga.riwayat.show' || Route::currentRouteName() == 'menu.master') ? 'active': '' }}">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-th"></i> <span>Master Data</span></a>
                            <ul class="dropdown-menu">
                                <li class="{{ (Route::currentRouteName() == 'aset.index' || Route::currentRouteName() == 'aset.create' || Route::currentRouteName() == 'aset.show' || Route::currentRouteName() == 'aset.edit' || Route::currentRouteName() == 'aset.file.index' || Route::currentRouteName() == 'aset.file.create' || Route::currentRouteName() == 'aset.file.show' || Route::currentRouteName() == 'aset.file.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/master/aset') }}">Aset</a></li>
                                <li class="{{ (Route::currentRouteName() == 'fasilitas.index' || Route::currentRouteName() == 'fasilitas.create' || Route::currentRouteName() == 'fasilitas.show' || Route::currentRouteName() == 'fasilitas.edit' || Route::currentRouteName() == 'fasilitas.file.index' || Route::currentRouteName() == 'fasilitas.file.create' || Route::currentRouteName() == 'fasilitas.file.show' || Route::currentRouteName() == 'fasilitas.file.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/master/fasilitas') }}">Fasilitas</a></li>
                                <li class="{{ (Route::currentRouteName() == 'produk.index' || Route::currentRouteName() == 'produk.create' || Route::currentRouteName() == 'produk.show' || Route::currentRouteName() == 'produk.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/master/produk') }}">Produk</a></li>
                                <li class="{{ (Route::currentRouteName() == 'member.index' || Route::currentRouteName() == 'member.create' || Route::currentRouteName() == 'member.show' || Route::currentRouteName() == 'member.edit' || Route::currentRouteName() == 'member.jabatan.index' || Route::currentRouteName() == 'member.jabatan.create') ? 'active': '' }}"><a class="nav-link" href="{{ url('/master/member') }}">Pegawai</a></li>
                                <li class="{{ (Route::currentRouteName() == 'harga.index' || Route::currentRouteName() == 'harga.create' || Route::currentRouteName() == 'harga.show' || Route::currentRouteName() == 'harga.edit' || Route::currentRouteName() == 'harga.riwayat.index' || Route::currentRouteName() == 'harga.riwayat.show') ? 'active': '' }}"><a class="nav-link" href="{{ url('/master/harga') }}">Harga</a></li>
                                <li class="{{ (Route::currentRouteName() == 'dombak.index' || Route::currentRouteName() == 'dombak.create' || Route::currentRouteName() == 'dombak.show' || Route::currentRouteName() == 'dombak.edit' || Route::currentRouteName() == 'konversi.index' || Route::currentRouteName() == 'konversi.create' || Route::currentRouteName() == 'konversi.show' || Route::currentRouteName() == 'konversi.edit' || Route::currentRouteName() == 'konversi.upload_show') ? 'active': '' }}"><a class="nav-link" href="{{ url('/master/dombak') }}">Dombak</a></li>
                                <li class="{{ (Route::currentRouteName() == 'payung.index' || Route::currentRouteName() == 'payung.create' || Route::currentRouteName() == 'payung.show' || Route::currentRouteName() == 'payung.edit' || Route::currentRouteName() == 'payung.dombak'|| Route::currentRouteName() == 'payung.dombak.create') ? 'active': '' }}"><a class="nav-link" href="{{ url('/master/payung') }}">Payung</a></li>
                                <li class="{{ (Route::currentRouteName() == 'dispenser.index' || Route::currentRouteName() == 'dispenser.create' || Route::currentRouteName() == 'dispenser.show' || Route::currentRouteName() == 'dispenser.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/master/dispenser') }}">Dispenser</a></li>
                                <li class="{{ (Route::currentRouteName() == 'nozzle.index' || Route::currentRouteName() == 'nozzle.create' || Route::currentRouteName() == 'nozzle.show' || Route::currentRouteName() == 'nozzle.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/master/nozzle') }}">Nozzle</a></li>
                            </ul>
                        </li>
                        @endif

                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur')
                        <li class="dropdown {{ (Route::currentRouteName() == 'perencanaan.index' || Route::currentRouteName() == 'perencanaan.create' || Route::currentRouteName() == 'perencanaan.show' || Route::currentRouteName() == 'perencanaan.edit' || Route::currentRouteName() ==  'perencanaan.ditolak' ||  Route::currentRouteName() == 'perencanaan.ditolak.show' || Route::currentRouteName() == 'pembelian.index' || Route::currentRouteName() == 'pembelian.show' || Route::currentRouteName() == 'pembelian.edit' ||Route::currentRouteName() == 'pembelian.detail.index' || Route::currentRouteName() == 'pembelian.detail.show' || Route::currentRouteName() == 'pembelian.detail.edit' ||  Route::currentRouteName() == 'pembayaran.index' || Route::currentRouteName() == 'pembayaran.create' || Route::currentRouteName() == 'pembayaran.show' || Route::currentRouteName() == 'pembayaran.edit' || Route::currentRouteName() == 'pembayaran.detail' || Route::currentRouteName() == 'pembayaran.file.create' || Route::currentRouteName() == 'pembayaran.file.show' || Route::currentRouteName() == 'pembayaran.file.edit' || Route::currentRouteName() == 'pengiriman.index' || Route::currentRouteName() == 'pengiriman.file_lo.show' || Route::currentRouteName() == 'pengiriman.show' ||  Route::currentRouteName() == 'perencanaan.detail.index' ||  Route::currentRouteName() == 'perencanaan.detail.create' ||  Route::currentRouteName() == 'perencanaan.detail.show' ||  Route::currentRouteName() == 'perencanaan.detail.edit' || Route::currentRouteName() == 'ms2.pengiriman' || Route::currentRouteName() == 'ms2.pengiriman.create' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_show' || Route::currentRouteName() == 'ms2.pengiriman.show' || Route::currentRouteName() == 'ms2.produk.index' || Route::currentRouteName() == 'ms2.produk.show' || Route::currentRouteName() == 'ms2.detail.show' || Route::currentRouteName() == 'ms2.detail.edit' || Route::currentRouteName() == 'ms2.detail.update' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_create' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_show.lampiran.index' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_show.lampiran.create' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_show.lampiran.show' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_show.lampiran.edit' || Route::currentRouteName() == 'menu.procurement') ? 'active' : "" }}">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-filter"></i> <span>Procurement</span></a>
                            <ul class="dropdown-menu">
                                <li class="{{ (Route::currentRouteName() == 'perencanaan.index' || Route::currentRouteName() == 'perencanaan.create' || Route::currentRouteName() == 'perencanaan.show' || Route::currentRouteName() == 'perencanaan.edit'|| Route::currentRouteName() ==  'perencanaan.ditolak' ||  Route::currentRouteName() == 'perencanaan.ditolak.show' ||  Route::currentRouteName() == 'perencanaan.detail.index' ||  Route::currentRouteName() == 'perencanaan.detail.create' ||  Route::currentRouteName() == 'perencanaan.detail.show' ||  Route::currentRouteName() == 'perencanaan.detail.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/procurement/perencanaan') }}">Perencanaan</a></li>
                                <li class="{{ (Route::currentRouteName() == 'pembelian.index' || Route::currentRouteName() == 'pembelian.show' || Route::currentRouteName() == 'pembelian.edit' ||Route::currentRouteName() == 'pembelian.detail.index' || Route::currentRouteName() == 'pembelian.detail.show' || Route::currentRouteName() == 'pembelian.detail.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/procurement/pembelian') }}">Pembelian</a></li>
                                <li class="{{ (Route::currentRouteName() == 'pembayaran.index' || Route::currentRouteName() == 'pembayaran.create' || Route::currentRouteName() == 'pembayaran.show' || Route::currentRouteName() == 'pembayaran.edit' || Route::currentRouteName() == 'pembayaran.detail' || Route::currentRouteName() == 'pembayaran.file.create' || Route::currentRouteName() == 'pembayaran.file.show' || Route::currentRouteName() == 'pembayaran.file.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/procurement/pembayaran') }}">Pembayaran</a></li>
                                <li class="{{ (Route::currentRouteName() == 'ms2.produk.index' || Route::currentRouteName() == 'ms2.produk.show' || Route::currentRouteName() == 'ms2.detail.show' || Route::currentRouteName() == 'ms2.detail.edit' || Route::currentRouteName() == 'ms2.detail.update' || Route::currentRouteName() == 'ms2.pengiriman' || Route::currentRouteName() == 'ms2.pengiriman.create' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_show' || Route::currentRouteName() == 'ms2.pengiriman.show' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_create' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_show.lampiran.index' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_show.lampiran.create' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_show.lampiran.show' || Route::currentRouteName() == 'ms2.pengiriman.file_lo_show.lampiran.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/procurement/ms2') }}">MS2</a></li>
                                <li class="{{ (Route::currentRouteName() == 'pengiriman.index' || Route::currentRouteName() == 'pengiriman.show' || Route::currentRouteName() == 'pengiriman.file_lo.show') ? 'active': '' }}"><a class="nav-link" href="{{ url('/procurement/delivery') }}">Delivery</a></li>
                            </ul>
                        </li>
                        @endif

                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur')
                        <li class="dropdown {{ (Route::currentRouteName() == 'monitoring.index' || Route::currentRouteName() == 'monitoring.create' || Route::currentRouteName() == 'monitoring.show' || Route::currentRouteName() == 'monitoring.edit' || Route::currentRouteName() == 'monitoring.dombak.index' || Route::currentRouteName() == 'monitoring.dombak.create' || Route::currentRouteName() == 'stok-taking.index' || Route::currentRouteName() == 'stok-taking.create' || Route::currentRouteName() == 'stok-taking.edit' || Route::currentRouteName() == 'stok-taking.show' || Route::currentRouteName() == 'stok-taking.file.index' || Route::currentRouteName() == 'stok-taking.file.create' || Route::currentRouteName() == 'stok-taking.file.edit' || Route::currentRouteName() == 'stok-taking.file.show' || Route::currentRouteName() == 'penerimaan.index' || Route::currentRouteName() == 'penerimaan.show' || Route::currentRouteName() == 'penerimaan.show_penerimaan' || Route::currentRouteName() == 'penerimaan.riwayat' || Route::currentRouteName() == 'penerimaan.riwayat.show' || Route::currentRouteName() == 'penerimaan.file.index' || Route::currentRouteName() == 'penerimaan.file.show' || Route::currentRouteName() == 'penerimaan.file.create' || Route::currentRouteName() == 'penerimaan.file.edit' || Route::currentRouteName() == 'menu.stok' || Route::currentRouteName() == 'monitoring.show_riwayat' || Route::currentRouteName() == 'monitoring.show_detail_riwayat' || Route::currentRouteName() == 'penerimaan.verifikasi'  || Route::currentRouteName() == 'penerimaan.verifikasi.show'  || Route::currentRouteName() == 'penerimaan.file.riwayat.index' || Route::currentRouteName() == 'penerimaan.file.riwayat.show' || Route::currentRouteName() == 'stok-transfer.index' || Route::currentRouteName() == 'stok-transfer.create' || Route::currentRouteName() == 'stok-transfer.edit' || Route::currentRouteName() == 'stok-transfer.show' || Route::currentRouteName() == 'stok-transfer.file.index' || Route::currentRouteName() == 'stok-transfer.file.create' || Route::currentRouteName() == 'stok-transfer.file.edit' || Route::currentRouteName() == 'stok-transfer.file.show' || Route::currentRouteName() == 'stok-summary.index' || Route::currentRouteName() == 'stok-summary.create' || Route::currentRouteName() == 'stok-summary.edit' || Route::currentRouteName() == 'stok-summary.show' ? 'active' : '') }}">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-tint"></i> <span>Manajemen Persediaan</span></a>
                            <ul class="dropdown-menu">
                                @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur')
                                <li class="{{ (Route::currentRouteName() == 'monitoring.index' || Route::currentRouteName() == 'monitoring.create' || Route::currentRouteName() == 'monitoring.show' || Route::currentRouteName() == 'monitoring.edit' || Route::currentRouteName() == 'monitoring.dombak.index' || Route::currentRouteName() == 'monitoring.dombak.create' || Route::currentRouteName() == 'monitoring.show_riwayat' || Route::currentRouteName() == 'monitoring.show_detail_riwayat') ? 'active': '' }}"><a class="nav-link" href="{{ url('/stok/monitoring') }}">Monitoring Stok</a></li>
                                @endif
                                @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur')
                                <li class="{{ (Route::currentRouteName() == 'penerimaan.index' || Route::currentRouteName() == 'penerimaan.show' || Route::currentRouteName() == 'penerimaan.show_penerimaan' || Route::currentRouteName() == 'penerimaan.riwayat' || Route::currentRouteName() == 'penerimaan.riwayat.show' || Route::currentRouteName() == 'penerimaan.file.index' || Route::currentRouteName() == 'penerimaan.file.show' || Route::currentRouteName() == 'penerimaan.file.create' || Route::currentRouteName() == 'penerimaan.file.edit' || Route::currentRouteName() == 'penerimaan.verifikasi'  || Route::currentRouteName() == 'penerimaan.verifikasi.show'  || Route::currentRouteName() == 'penerimaan.file.riwayat.index' || Route::currentRouteName() == 'penerimaan.file.riwayat.show') ? 'active': '' }}"><a class="nav-link" href="{{ url('/stok/penerimaan') }}">Penerimaan BBM</a></li>
                                @endif
                                @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur')
                                <li class="{{ (Route::currentRouteName() == 'stok-taking.index' || Route::currentRouteName() == 'stok-taking.create' || Route::currentRouteName() == 'stok-taking.edit' || Route::currentRouteName() == 'stok-taking.show' || Route::currentRouteName() == 'stok-taking.file.index' || Route::currentRouteName() == 'stok-taking.file.create' || Route::currentRouteName() == 'stok-taking.file.edit' || Route::currentRouteName() == 'stok-taking.file.show') ? 'active': '' }}"><a class="nav-link" href="{{ url('/stok/stok-taking') }}">Stok Taking</a></li>
                                @endif
                                @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur')
                                <li class="{{ (Route::currentRouteName() == 'stok-transfer.index' || Route::currentRouteName() == 'stok-transfer.create' || Route::currentRouteName() == 'stok-transfer.edit' || Route::currentRouteName() == 'stok-transfer.show' || Route::currentRouteName() == 'stok-transfer.file.index' || Route::currentRouteName() == 'stok-transfer.file.create' || Route::currentRouteName() == 'stok-transfer.file.edit' || Route::currentRouteName() == 'stok-transfer.file.show') ? 'active': '' }}"><a class="nav-link" href="{{ url('/stok/stok-transfer') }}">Stok Transfer</a></li>
                                @endif
                                @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur')
                                <li class="{{ (Route::currentRouteName() == 'stok-summary.index' || Route::currentRouteName() == 'stok-summary.create' || Route::currentRouteName() == 'stok-summary.edit' || Route::currentRouteName() == 'stok-summary.show') ? 'active': '' }}"><a class="nav-link" href="{{ url('/stok/stok-summary') }}">Stok Summary</a></li>
                                @endif
                            </ul>
                        </li>
                        @endif

                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                        <li class="dropdown">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-upload"></i> <span>Tera</span></a>
                            <ul class="dropdown-menu">
                                <li class="{{ (Route::currentRouteName() == 'stok-peminjaman') ? 'active': '' }}"><a class="nav-link" href="{{ url('/tera/stok-peminjaman') }}">Stok Peminjaman Tera</a></li>
                                <li class="{{ (Route::currentRouteName() == 'stok-pengembalian') ? 'active': '' }}"><a class="nav-link" href="{{ url('/tera/stok-pengembalian') }}">Stok Pengembalian Tera</a></li>
                            </ul>
                        </li>
                        @endif

                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama'|| Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur')
                        <li class="{{ (Route::currentRouteName() == 'totalisator.index' || Route::currentRouteName() == 'totalisator.create' || Route::currentRouteName() == 'totalisator.edit' || Route::currentRouteName() == 'totalisator.show'||Route::currentRouteName() == 'totalisator.file.index' || Route::currentRouteName() == 'totalisator.file.create' || Route::currentRouteName() == 'totalisator.file.edit' || Route::currentRouteName() == 'totalisator.file.show' || Route::currentRouteName() == 'totalisator.file.riwayat.index' || Route::currentRouteName() == 'totalisator.file.riwayat.create' || Route::currentRouteName() == 'totalisator.file.riwayat.edit' || Route::currentRouteName() == 'totalisator.file.riwayat.show' || Route::currentRouteName() == 'totalisator.riwayat' || Route::currentRouteName() == 'totalisator.riwayat_show') ? 'active': '' }}"><a class="nav-link" href="{{ url('/stok/totalisator') }}"><i class="fas fa-thermometer-full"></i><span>Totalisator</i></span></a></li>
                        @endif

                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator')
                        <li class="dropdown {{ (Route::currentRouteName() == 'setoran.open' || Route::currentRouteName() == 'setoran.open.create' || Route::currentRouteName() == 'setoran.open.store' || Route::currentRouteName() == 'setoran.open.show' || Route::currentRouteName() == 'setoran.open.edit' || Route::currentRouteName() == 'setoran.open.update' || Route::currentRouteName() == 'setoran.open.update.status' || Route::currentRouteName() == 'setoran.open.delete' || Route::currentRouteName() == 'setoran.open.detail' || Route::currentRouteName() == 'setoran.open.detail.create' || Route::currentRouteName() == 'setoran.open.detail.create.api' || Route::currentRouteName() == 'setoran.open.detail.store' || Route::currentRouteName() == 'setoran.open.detail.show' || Route::currentRouteName() == 'setoran.open.detail.edit' || Route::currentRouteName() == 'setoran.open.detail.update' || Route::currentRouteName() == 'setoran.open.detail.delete' || Route::currentRouteName() == 'setoran.penitipan' || Route::currentRouteName() == 'setoran.penitipan.show' || Route::currentRouteName() == 'setoran.penitipan.update_closing' || Route::currentRouteName() == 'setoran.penitipan.index_penitipan' || Route::currentRouteName() == 'setoran.penitipan.create' || Route::currentRouteName() == 'setoran.penitipan.store' || Route::currentRouteName() == 'setoran.penitipan.show_penitipan' || Route::currentRouteName() == 'setoran.penitipan.edit' || Route::currentRouteName() == 'setoran.penitipan.update' || Route::currentRouteName() == 'setoran.penitipan.update_confirmation' || Route::currentRouteName() == 'setoran.penitipan.delete' || Route::currentRouteName() == 'setoran.penitipan.file.index' || Route::currentRouteName() == 'setoran.penitipan.file.create' || Route::currentRouteName() == 'setoran.penitipan.file.store' || Route::currentRouteName() == 'setoran.penitipan.file.delete' || Route::currentRouteName() == 'setoran.penitipan.file.download' || Route::currentRouteName() == 'setoran.closing' || Route::currentRouteName() == 'setoran.closing.show' || Route::currentRouteName() == 'setoran.closing.update' || Route::currentRouteName() == 'setoran.closing.delete' || Route::currentRouteName() == 'setoran.setoran_bank.index_setoran_bank_penjualan' || Route::currentRouteName() == 'setoran.setoran_bank.show_setoran_bank_penjualan' || Route::currentRouteName() == 'setoran.setoran_bank.bank.index' || Route::currentRouteName() == 'setoran.setoran_bank.bank.create' || Route::currentRouteName() == 'setoran.setoran_bank.bank.store' || Route::currentRouteName() == 'setoran.setoran_bank.bank.show' || Route::currentRouteName() == 'setoran.setoran_bank.bank.edit' || Route::currentRouteName() == 'setoran.setoran_bank.bank.update_confirmation_0x002' || Route::currentRouteName() == 'setoran.setoran_bank.bank.update_confirmation_0x003' || Route::currentRouteName() == 'setoran.setoran_bank.bank.update' || Route::currentRouteName() == 'setoran.setoran_bank.bank.destroy' || Route::currentRouteName() == 'setoran.setoran_bank.file.index' || Route::currentRouteName() == 'setoran.setoran_bank.file.create' || Route::currentRouteName() == 'setoran.setoran_bank.file.store' || Route::currentRouteName() == 'setoran.setoran_bank.file.delete' || Route::currentRouteName() == 'setoran.setoran_bank.file.download') ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-percent"></i> <span>Setoran</span></a>
                            <ul class="dropdown-menu">
                                <li class="{{ (Route::currentRouteName() == 'setoran.open' || Route::currentRouteName() == 'setoran.open.create' || Route::currentRouteName() == 'setoran.open.store' || Route::currentRouteName() == 'setoran.open.show' || Route::currentRouteName() == 'setoran.open.edit' || Route::currentRouteName() == 'setoran.open.update' || Route::currentRouteName() == 'setoran.open.update.status' || Route::currentRouteName() == 'setoran.open.delete' || Route::currentRouteName() == 'setoran.open.detail' || Route::currentRouteName() == 'setoran.open.detail.create' || Route::currentRouteName() == 'setoran.open.detail.create.api' || Route::currentRouteName() == 'setoran.open.detail.store' || Route::currentRouteName() == 'setoran.open.detail.show' || Route::currentRouteName() == 'setoran.open.detail.edit' || Route::currentRouteName() == 'setoran.open.detail.update' || Route::currentRouteName() == 'setoran.open.detail.delete') ? 'active': '' }}"><a class="nav-link" href="{{ url('/setoran/open-penjualan') }}"><span>Open Penjualan</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'setoran.penitipan' || Route::currentRouteName() == 'setoran.penitipan.show' || Route::currentRouteName() == 'setoran.penitipan.update_closing' || Route::currentRouteName() == 'setoran.penitipan.index_penitipan' || Route::currentRouteName() == 'setoran.penitipan.create' || Route::currentRouteName() == 'setoran.penitipan.store' || Route::currentRouteName() == 'setoran.penitipan.show_penitipan' || Route::currentRouteName() == 'setoran.penitipan.edit' || Route::currentRouteName() == 'setoran.penitipan.update' || Route::currentRouteName() == 'setoran.penitipan.update_confirmation' || Route::currentRouteName() == 'setoran.penitipan.delete' || Route::currentRouteName() == 'setoran.penitipan.file.index' || Route::currentRouteName() == 'setoran.penitipan.file.create' || Route::currentRouteName() == 'setoran.penitipan.file.store' || Route::currentRouteName() == 'setoran.penitipan.file.delete' || Route::currentRouteName() == 'setoran.penitipan.file.download') ? 'active': '' }}"><a class="nav-link" href="{{ url('/setoran/penitipan-setoran') }}"><span>Penitipan Setoran</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'setoran.closing' || Route::currentRouteName() == 'setoran.closing.show' || Route::currentRouteName() == 'setoran.closing.update' || Route::currentRouteName() == 'setoran.closing.delete') ? 'active': '' }}"><a class="nav-link" href="{{ url('/setoran/closing-penjualan') }}"><span>Closing Penjualan</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'setoran.setoran_bank.index_setoran_bank_penjualan' || Route::currentRouteName() == 'setoran.setoran_bank.show_setoran_bank_penjualan' || Route::currentRouteName() == 'setoran.setoran_bank.bank.index' || Route::currentRouteName() == 'setoran.setoran_bank.bank.create' || Route::currentRouteName() == 'setoran.setoran_bank.bank.store' || Route::currentRouteName() == 'setoran.setoran_bank.bank.show' || Route::currentRouteName() == 'setoran.setoran_bank.bank.edit' || Route::currentRouteName() == 'setoran.setoran_bank.bank.update_confirmation_0x002' || Route::currentRouteName() == 'setoran.setoran_bank.bank.update_confirmation_0x003' || Route::currentRouteName() == 'setoran.setoran_bank.bank.update' || Route::currentRouteName() == 'setoran.setoran_bank.bank.destroy' || Route::currentRouteName() == 'setoran.setoran_bank.file.index' || Route::currentRouteName() == 'setoran.setoran_bank.file.create' || Route::currentRouteName() == 'setoran.setoran_bank.file.store' || Route::currentRouteName() == 'setoran.setoran_bank.file.delete' || Route::currentRouteName() == 'setoran.setoran_bank.file.download') ? 'active': '' }}"><a class="nav-link" href="{{ url('/setoran/setoran-bank') }}"><span>Setoran Bank</span></a></li>
                            </ul>
                        </li>
                        @endif

                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                        <li class="dropdown {{ (Route::currentRouteName() == 'menu.artikel' || Route::currentRouteName() == 'kategori.index' || Route::currentRouteName() == 'kategori.create' || Route::currentRouteName() == 'kategori.store' || Route::currentRouteName() == 'kategori.show' || Route::currentRouteName() == 'kategori.edit' || Route::currentRouteName() == 'kategori.update' || Route::currentRouteName() == 'kategori.delete' || Route::currentRouteName() == 'tag.index' || Route::currentRouteName() == 'tag.create' || Route::currentRouteName() == 'tag.store' || Route::currentRouteName() == 'tag.show' || Route::currentRouteName() == 'tag.edit' || Route::currentRouteName() == 'tag.update' || Route::currentRouteName() == 'tag.delete' || Route::currentRouteName() == 'post.index' || Route::currentRouteName() == 'post.index' || Route::currentRouteName() == 'post.create' || Route::currentRouteName() == 'post.store' || Route::currentRouteName() == 'post.show' || Route::currentRouteName() == 'post.edit' || Route::currentRouteName() == 'post.update' || Route::currentRouteName() == 'post.delete' || Route::currentRouteName() == 'file.post.index' || Route::currentRouteName() == 'file.post.create' || Route::currentRouteName() == 'file.post.store' || Route::currentRouteName() == 'file.post.download' || Route::currentRouteName() == 'file.post.show' || Route::currentRouteName() == 'file.post.edit' || Route::currentRouteName() == 'file.post.update' || Route::currentRouteName() == 'file.post.delete') ? 'active': '' }}">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-newspaper"></i> <span>Artikel</span></a>
                            <ul class="dropdown-menu">
                                <li class="{{ (Route::currentRouteName() == 'kategori.index' || Route::currentRouteName() == 'kategori.create' || Route::currentRouteName() == 'kategori.store' || Route::currentRouteName() == 'kategori.show' || Route::currentRouteName() == 'kategori.edit' || Route::currentRouteName() == 'kategori.update' || Route::currentRouteName() == 'kategori.delete') ? 'active': '' }}"><a class="nav-link" href="{{ url('/artikel/kategori') }}">Kategori</a></li>
                                <li class="{{ (Route::currentRouteName() == 'tag.index' || Route::currentRouteName() == 'tag.create' || Route::currentRouteName() == 'tag.store' || Route::currentRouteName() == 'tag.show' || Route::currentRouteName() == 'tag.edit' || Route::currentRouteName() == 'tag.update' || Route::currentRouteName() == 'tag.delete') ? 'active': '' }}"><a class="nav-link" href="{{ url('/artikel/tag') }}">Tag</a></li>
                                <li class="{{ (Route::currentRouteName() == 'post.index' || Route::currentRouteName() == 'post.index' || Route::currentRouteName() == 'post.create' || Route::currentRouteName() == 'post.store' || Route::currentRouteName() == 'post.show' || Route::currentRouteName() == 'post.edit' || Route::currentRouteName() == 'post.update' || Route::currentRouteName() == 'post.delete' || Route::currentRouteName() == 'file.post.index' || Route::currentRouteName() == 'file.post.create' || Route::currentRouteName() == 'file.post.store' || Route::currentRouteName() == 'file.post.download' || Route::currentRouteName() == 'file.post.show' || Route::currentRouteName() == 'file.post.edit' || Route::currentRouteName() == 'file.post.update' || Route::currentRouteName() == 'file.post.delete') ? 'active': '' }}"><a class="nav-link" href="{{ url('/artikel/post') }}">Post</a></li>
                            </ul>
                        </li>
                        @endif

                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Komisaris' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur Utama' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Direktur')
                        <li class="dropdown {{ Route::currentRouteName() == 'rekap-pajak.index' || Route::currentRouteName() == 'rekap-pajak.show' || Route::currentRouteName() == 'margin.index' || Route::currentRouteName() == 'financial.petty-cash' || Route::currentRouteName() == 'petty-cash.show'|| Route::currentRouteName() == 'financial.petty-cash.file' || Route::currentRouteName() == 'petty-cash.file.create' || Route::currentRouteName() == 'petty-cash.file.show' || Route::currentRouteName() == 'petty-cash.file.edit' ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-file"></i> <span>Financial</span></a>
                            <ul class="dropdown-menu">
                                <li class="{{ (Route::currentRouteName() == 'financial.petty-cash'|| Route::currentRouteName() == 'petty-cash.show'|| Route::currentRouteName() == 'financial.petty-cash.file'|| Route::currentRouteName() == 'petty-cash.file.create'|| Route::currentRouteName() == 'petty-cash.file.show' || Route::currentRouteName() == 'petty-cash.file.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/financial/petty-cash') }}">Petty Cash</a></li>
                                <li class="{{ (Route::currentRouteName() == 'rekap-pajak.index' || Route::currentRouteName() == 'rekap-pajak.show') ? 'active': '' }}"><a class="nav-link" href="{{ url('/financial/rekap-pajak') }}">Rekap Pajak</a></li>
                                <li class="{{ (Route::currentRouteName() == 'margin.index') ? 'active': '' }}"><a class="nav-link" href="{{ url('/financial/margin') }}">Margin</a></li>
                                <li class="{{ (Route::currentRouteName() == 'laba-rugi') ? 'active': '' }}"><a class="nav-link" href="{{ url('/financial/laba-rugi') }}">Laba Rugi</a></li>
                                <li class="{{ (Route::currentRouteName() == 'neraca') ? 'active': '' }}"><a class="nav-link" href="{{ url('/financial/neraca') }}">Neraca</a></li>
                                <li class="{{ (Route::currentRouteName() == 'buku-besar') ? 'active': '' }}"><a class="nav-link" href="{{ url('/financial/buku-besar') }}">Buku Besar</a></li>
                                <li class="{{ (Route::currentRouteName() == 'arus-kas') ? 'active': '' }}"><a class="nav-link" href="{{ url('/financial/arus-kas') }}">Arus Kas</a></li>
                                <li class="{{ (Route::currentRouteName() == 'perubahan-modal') ? 'active': '' }}"><a class="nav-link" href="{{ url('/financial/perubahan-modal') }}">Perubahan Modal</a></li>
                            </ul>
                        </li>
                        @endif

                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                        <li class="dropdown {{ Route::currentRouteName() == 'jadwal-kerja.index' || Route::currentRouteName() == 'jadwal-kerja.upload_file.template' || Route::currentRouteName() == 'jadwal-kerja.upload_file' || Route::currentRouteName() == 'jadwal-kerja.upload_file.store' || Route::currentRouteName() == 'jadwal-kerja.upload_file.delete' || Route::currentRouteName() == 'jadwal-kerja.qr' || Route::currentRouteName() == 'absensi.index' || Route::currentRouteName() == 'laporan-absensi.index' ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-calendar"></i> <span>Jadwal & Absensi</span></a>
                            <ul class="dropdown-menu">
                                <li class="{{ (Route::currentRouteName() == 'jadwal-kerja.index' || Route::currentRouteName() == 'jadwal-kerja.upload_file.template' || Route::currentRouteName() == 'jadwal-kerja.upload_file' || Route::currentRouteName() == 'jadwal-kerja.upload_file.store' || Route::currentRouteName() == 'jadwal-kerja.upload_file.delete' || Route::currentRouteName() == 'jadwal-kerja.qr') ? 'active': '' }}"><a class="nav-link" href="{{ route('jadwal-kerja.index') }}">Penjadwalan Pegawai</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'absensi.index') ? 'active': '' }}"><a class="nav-link" href="{{ route('absensi.index') }}">Absensi</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'laporan-absensi.index') ? 'active': '' }}"><a class="nav-link" href="{{ route('laporan-absensi.index') }}">Laporan Rekap</span></a></li>
                            </ul>
                        </li>
                        @endif

                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                        <li class="dropdown {{ Route::currentRouteName() == 'spbu.index' || Route::currentRouteName() == 'spbu.create' || Route::currentRouteName() == 'spbu.show' || Route::currentRouteName() == 'spbu.edit' || Route::currentRouteName() == 'divisi.index' || Route::currentRouteName() == 'divisi.create' || Route::currentRouteName() == 'divisi.show' || Route::currentRouteName() == 'divisi.edit' || Route::currentRouteName() == 'hari.index' || Route::currentRouteName() == 'hari.create' || Route::currentRouteName() == 'hari.show' || Route::currentRouteName() == 'hari.edit' || Route::currentRouteName() == 'jam_kerja.index' || Route::currentRouteName() == 'jam_kerja.create' || Route::currentRouteName() == 'jam_kerja.show' || Route::currentRouteName() == 'jam_kerja.edit' || Route::currentRouteName() == 'level.index' || Route::currentRouteName() == 'level.create' || Route::currentRouteName() == 'level.show' || Route::currentRouteName() == 'level.edit' || Route::currentRouteName() == 'jabatan.index' || Route::currentRouteName() == 'jabatan.create' || Route::currentRouteName() == 'jabatan.show' || Route::currentRouteName() == 'jabatan.edit' || Route::currentRouteName() == 'pajak_pembelian_lib.index' || Route::currentRouteName() == 'pajak_pembelian_lib.create' || Route::currentRouteName() == 'pajak_pembelian_lib.show' || Route::currentRouteName() == 'pajak_pembelian_lib.edit' || Route::currentRouteName() == 'import.index' || Route::currentRouteName() == 'menu.konfigurasi' ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown"><i class="fas fa-wrench"></i> <span>Konfigurasi</span></a>
                            <ul class="dropdown-menu">
                                <li class="{{ (Route::currentRouteName() == 'spbu.index' || Route::currentRouteName() == 'spbu.create' || Route::currentRouteName() == 'spbu.show' || Route::currentRouteName() == 'spbu.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/konfigurasi/spbu') }}">SPBU</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'divisi.index' || Route::currentRouteName() == 'divisi.create' || Route::currentRouteName() == 'divisi.show' || Route::currentRouteName() == 'divisi.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/konfigurasi/divisi') }}">Divisi</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'level.index' || Route::currentRouteName() == 'level.create' || Route::currentRouteName() == 'level.show' || Route::currentRouteName() == 'level.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/konfigurasi/level') }}">Level</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'jabatan.index' || Route::currentRouteName() == 'jabatan.create' || Route::currentRouteName() == 'jabatan.show' || Route::currentRouteName() == 'jabatan.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/konfigurasi/jabatan') }}">Jabatan</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'pajak_pembelian_lib.index' || Route::currentRouteName() == 'pajak_pembelian_lib.create' || Route::currentRouteName() == 'pajak_pembelian_lib.show' || Route::currentRouteName() == 'pajak_pembelian_lib.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/konfigurasi/pajak') }}">Pajak</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'hari.index' || Route::currentRouteName() == 'hari.create' || Route::currentRouteName() == 'hari.show' || Route::currentRouteName() == 'hari.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/konfigurasi/hari') }}">Hari</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'jam_kerja.index' || Route::currentRouteName() == 'jam_kerja.create' || Route::currentRouteName() == 'jam_kerja.show' || Route::currentRouteName() == 'jam_kerja.edit') ? 'active': '' }}"><a class="nav-link" href="{{ url('/konfigurasi/jam_kerja') }}">Jam Kerja</span></a></li>
                                <li class="{{ (Route::currentRouteName() == 'import.index') ? 'active': '' }}"><a class="nav-link" href="{{ url('/konfigurasi/import') }}">Import Excel</span></a></li>
                            </ul>
                        </li>
                        @endif

                        @if(Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Keamanan' || Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab')
                        <li class="{{ (Route::currentRouteName() == 'menu.kehadiran') ? 'active': '' }}"><a class="nav-link" href="{{ route('menu.kehadiran') }}"><i class="fas fa-calendar"></i><span>Kehadiran</span></a></li>
                        @endif
                        @endauth
                    </ul>
                </aside>
            </div>
            <div class="main-content">
                <section class="section">
                    @yield('content')
                </section>
            </div>
            <footer class="main-footer">
                <div class="footer-left">
                    SPBU <div class="bullet"></div> {{ now()->year }} Sistem Monitoring
                </div>
            </footer>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
