<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // API mobile: stateless, token Sanctum. Didaftarkan lebih dulu agar
            // prefix api/v1 tidak tertangkap grup api berbasis sesi di bawahnya.
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/mobile.php'));

            // Endpoint AJAX internal untuk halaman web — memakai middleware
            // 'web' (sesi + CSRF), bukan 'api'. Lihat ARCHITECTURE.md §4.
            Route::middleware('web')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
