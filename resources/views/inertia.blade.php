<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title inertia>{{ config('app.name', 'Monitoring SPBU') }}</title>

    <script>
        (function () {
            var key = 'spbu-theme';

            function preferredTheme() {
                try {
                    var saved = localStorage.getItem(key);
                    if (saved === 'dark' || saved === 'light') return saved;
                    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                } catch (e) {
                    return 'light';
                }
            }

            function applyTheme(theme, persist) {
                document.documentElement.dataset.theme = theme;
                document.documentElement.style.colorScheme = theme;
                if (persist) {
                    try {
                        localStorage.setItem(key, theme);
                    } catch (e) {}
                }
            }

            window.spbuTheme = {
                get: preferredTheme,
                set: function (theme) { applyTheme(theme, true); },
                toggle: function () {
                    var next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
                    applyTheme(next, true);
                    return next;
                }
            };

            applyTheme(preferredTheme(), false);
        })();
    </script>

    <!-- Stisla + FontAwesome (hasil build Mix) & tema bersama -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/theme.css') }}?v={{ filemtime(public_path('css/theme.css')) }}" rel="stylesheet">

    <!-- Icon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap"
        rel="stylesheet" />

    @viteReactRefresh
    @vite('resources/js/inertia/app.jsx')
    @inertiaHead
</head>

<body>
    @inertia
</body>

</html>
