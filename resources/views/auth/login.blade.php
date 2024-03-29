<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="route" content="login">

    <title>{{ config('app.name', 'Login') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Icon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
</head>

<body>
    <div id="app">
        <section class="section">
            <div class="container mt-5">
                <div class="row">
                    <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
                        <div class="login-brand">
                            <img src="../images/logo.jpg" alt="logo" width="100" class="shadow-light">
                        </div>

                        <div class="card card-primary">
                            <div class="card-header">
                                <h4>{{ __('Login') }}</h4>
                            </div>

                            <div class="card-body">
                                @if(session('msg')) {!! session('msg') !!} @endif
                                <form method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label for="username">{{ __('username') }}</label>
                                        <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" name="username" tabindex="1" autofocus>
                                        @error('username')
                                        <div class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <div class="d-block">
                                            <label for="password" class="control-label">{{ __('Password') }}</label>
                                            @if (Route::has('password.request'))
                                            <div class="float-right">
                                                <a href="{{ route('password.request') }}" class="text-small">
                                                {{ __('Forgot Your Password?') }}
                                                </a>
                                            </div>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                <input type="password" id="password" class="form-control" class="form-control @error('password') is-invalid @enderror" name="password" tabindex="2" autocomplete="current-password" />
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" type="button" id="see_password"><i class="fas fa-eye"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                                            {{ __('Login') }}
                                        </button>
                                    </div>
                                </form>
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <a type="button" class="" href="{{ url('/') }}" tabindex="4">
                                            {{ __('Kembali') }}
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="simple-footer">
                            Copyright &copy; Monitoring SPBU {{ now()->year }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.js" integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc=" crossorigin="anonymous"></script>
    <script>
        $('#see_password').on('click', function(e) {
            if($('#password').attr('type') == 'password') {
                $('#password').attr('type', 'text');
            } else {
                $('#password').attr('type', 'password');
            }
        })
    </script>
</body>

</html>
