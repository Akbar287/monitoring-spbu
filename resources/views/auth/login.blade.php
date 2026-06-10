<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="route" content="login">

    <title>{{ config('app.name', 'Login') }} — Login</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Icon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <style>
        :root {
            --pri: #6777ef;
            --pri-dark: #4f5fdc;
            --ink: #1f2440;
            --muted: #8089a8;
        }

        body.auth {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            color: var(--ink);
            background: linear-gradient(135deg, #eef0fb 0%, #f6f7fb 100%);
        }

        .auth-card {
            width: 100%;
            max-width: 920px;
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            box-shadow: 0 30px 70px rgba(31, 36, 64, .18);
        }

        /* ===== Brand panel ===== */
        .auth-brand {
            position: relative;
            overflow: hidden;
            padding: 48px 40px;
            color: #fff;
            background: linear-gradient(150deg, #5b6bf0 0%, #6a4ff0 55%, #8b4ff0 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-brand:before,
        .auth-brand:after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
        }

        .auth-brand:before {
            width: 240px;
            height: 240px;
            top: -90px;
            right: -70px;
        }

        .auth-brand:after {
            width: 160px;
            height: 160px;
            bottom: -70px;
            left: -50px;
        }

        .auth-logo {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: rgba(255, 255, 255, .18);
            display: grid;
            place-items: center;
            font-size: 26px;
            margin-bottom: 22px;
            position: relative;
        }

        .auth-brand h2 {
            font-weight: 800;
            font-size: 26px;
            margin: 0 0 10px;
            position: relative;
        }

        .auth-brand p {
            font-size: 14px;
            line-height: 1.6;
            opacity: .9;
            margin: 0 0 26px;
            position: relative;
        }

        .auth-features {
            list-style: none;
            padding: 0;
            margin: 0;
            position: relative;
        }

        .auth-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13.5px;
            font-weight: 500;
            padding: 7px 0;
        }

        .auth-features li i {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            background: rgba(255, 255, 255, .16);
            display: grid;
            place-items: center;
            font-size: 13px;
        }

        /* ===== Form panel ===== */
        .auth-form {
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-form h1 {
            font-weight: 800;
            font-size: 24px;
            margin: 0 0 4px;
        }

        .auth-form .sub {
            color: var(--muted);
            font-size: 14px;
            margin: 0 0 26px;
        }

        .field {
            margin-bottom: 18px;
        }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 7px;
            color: var(--ink);
        }

        .field .label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .field .label-row a {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--pri);
            text-decoration: none;
        }

        .field .label-row a:hover {
            text-decoration: underline;
        }

        .input-icon {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon > i.lead-ico {
            position: absolute;
            left: 16px;
            color: var(--muted);
            font-size: 15px;
            pointer-events: none;
        }

        .input-icon input {
            width: 100%;
            border: 1.6px solid #e6e8f2;
            background: #fafbff;
            border-radius: 12px;
            padding: 13px 16px 13px 44px;
            font-size: 14.5px;
            font-family: inherit;
            color: var(--ink);
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
            outline: none;
        }

        .input-icon input::placeholder {
            color: #b3b9d1;
        }

        .input-icon input:focus {
            border-color: var(--pri);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(103, 119, 239, .14);
        }

        .input-icon.has-error input {
            border-color: #fc544b;
        }

        .toggle-pass {
            position: absolute;
            right: 8px;
            border: 0;
            background: transparent;
            color: var(--muted);
            width: 36px;
            height: 36px;
            border-radius: 9px;
            cursor: pointer;
            transition: color .15s ease, background .15s ease;
        }

        .toggle-pass:hover {
            color: var(--pri);
            background: rgba(103, 119, 239, .1);
        }

        .field-error {
            display: block;
            color: #fc544b;
            font-size: 12.5px;
            font-weight: 600;
            margin-top: 6px;
        }

        .btn-submit {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            cursor: pointer;
            background: linear-gradient(135deg, var(--pri), var(--pri-dark));
            box-shadow: 0 10px 22px rgba(103, 119, 239, .4);
            transition: transform .18s ease, box-shadow .18s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            margin-top: 4px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(103, 119, 239, .5);
        }

        .back-link {
            text-align: center;
            margin-top: 22px;
            font-size: 13.5px;
        }

        .back-link a {
            color: var(--muted);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .back-link a:hover {
            color: var(--pri);
        }

        .auth-foot {
            text-align: center;
            color: #aab0cb;
            font-size: 12px;
            margin-top: 18px;
        }

        @media (max-width: 768px) {
            .auth-card {
                grid-template-columns: 1fr;
                max-width: 440px;
            }

            .auth-brand {
                display: none;
            }

            .auth-form {
                padding: 38px 28px;
            }
        }
    </style>
</head>

<body class="auth">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-logo"><i class="fas fa-gas-pump"></i></div>
            <h2>Monitoring SPBU</h2>
            <p>Sistem terpadu untuk memantau stok, penjualan, dan operasional SPBU secara real-time.</p>
            <ul class="auth-features">
                <li><i class="fas fa-chart-line"></i> Dashboard &amp; laporan informatif</li>
                <li><i class="fas fa-gas-pump"></i> Monitoring stok &amp; penjualan BBM</li>
                <li><i class="fas fa-shield-halved"></i> Akses aman sesuai jabatan</li>
            </ul>
        </div>

        <div class="auth-form">
            <h1>Selamat Datang 👋</h1>
            <p class="sub">Silakan masuk menggunakan akun Anda.</p>

            @if (session('msg'))
                {!! session('msg') !!}
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-icon @error('username') has-error @enderror">
                        <i class="fas fa-user lead-ico"></i>
                        <input id="username" type="text" name="username" value="{{ old('username') }}"
                            placeholder="Masukkan username" tabindex="1" autofocus>
                    </div>
                    @error('username')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <div class="label-row">
                        <label for="password">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Lupa password?</a>
                        @endif
                    </div>
                    <div class="input-icon @error('password') has-error @enderror">
                        <i class="fas fa-lock lead-ico"></i>
                        <input id="password" type="password" name="password" placeholder="Masukkan password"
                            tabindex="2" autocomplete="current-password">
                        <button class="toggle-pass" type="button" id="see_password" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-submit" tabindex="3">
                    <i class="fas fa-right-to-bracket"></i> Login
                </button>
            </form>

            <div class="back-link">
                <a href="{{ url('/') }}"><i class="fas fa-arrow-left"></i> Kembali ke beranda</a>
            </div>
            <div class="auth-foot">
                &copy; Monitoring SPBU {{ now()->year }}
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.slim.js"
        integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc=" crossorigin="anonymous"></script>
    <script>
        $('#see_password').on('click', function (e) {
            var $input = $('#password');
            var $icon = $(this).find('i');
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    </script>
</body>

</html>
