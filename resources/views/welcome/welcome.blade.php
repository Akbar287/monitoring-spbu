<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="route" content="{{ Route::currentRouteName() }}">

        <title>Monitoring SPBU</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <title>{{ config('app.name', 'Monitoring SPBU') }}</title>

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    </head>
    <body class="antialiased">
        <div class="jumbotron jumbotron-fluid">
            <div class="container">
              <h1 class="display-4">Monitoring SPBU</h1>
              <p class="lead">Disini untuk Portal Pemberitahuan / Pengumuman.</p>
            </div>
        </div>
        <div class="container">
            @yield('content')
        </div>
    </body>
</html>
