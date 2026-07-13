<?php

namespace App\Http\Middleware;

use App\Support\Navigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;

class HandleInertiaRequests extends Middleware
{
    private static ?array $inertiaRouteUris = null;

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'inertia';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $auth = null;
        $navigation = [];

        if ($user = $request->user()) {
            $member = $user->member()->first();
            $level = optional(optional(optional($member->jabatan()->first())->level())->first())->nama_level;

            $auth = [
                'nama' => optional($member->ktp()->first())->nama,
                'level' => $level,
                'avatar' => asset('/storage/profile_img/' . $member->avatar),
            ];
            $navigation = Navigation::for($level);
        }

        return array_merge(parent::share($request), [
            'auth' => $auth,
            'navigation' => $navigation,
            'urls' => [
                'home' => url('/home'),
                'profil' => url('/profil'),
                'notifikasi' => url('/notifikasi'),
                'logout' => route('logout'),
            ],
            'flash' => [
                'msg' => fn () => $request->session()->get('msg'),
            ],
            'spa' => [
                'routes' => self::inertiaRouteUris(),
            ],
            'uploadMaxKb' => \App\Support\Upload::maxKb(),
        ]);
    }

    private static function inertiaRouteUris(): array
    {
        if (self::$inertiaRouteUris !== null) {
            return self::$inertiaRouteUris;
        }

        $routes = [];
        $seen = [];

        foreach (Route::getRoutes() as $route) {
            if (!in_array('GET', $route->methods(), true)) {
                continue;
            }

            $uri = '/' . ltrim($route->uri(), '/');
            if (isset($seen[$uri])) {
                continue;
            }
            $seen[$uri] = true;

            $routes[] = [
                'uri' => $uri,
                'inertia' => self::routeUsesInertia($route->getAction('uses')),
            ];
        }

        self::$inertiaRouteUris = $routes;

        return self::$inertiaRouteUris;
    }

    private static function routeUsesInertia(mixed $uses): bool
    {
        try {
            if ($uses instanceof \Closure) {
                return self::sourceUsesInertia(new ReflectionFunction($uses));
            }

            if (is_string($uses)) {
                [$class, $method] = str_contains($uses, '@')
                    ? explode('@', $uses, 2)
                    : [$uses, '__invoke'];

                return class_exists($class)
                    && method_exists($class, $method)
                    && self::sourceUsesInertia(new ReflectionMethod($class, $method));
            }

            if (is_array($uses) && isset($uses[0], $uses[1])) {
                return self::sourceUsesInertia(new ReflectionMethod($uses[0], $uses[1]));
            }
        } catch (Throwable) {
            return false;
        }

        return false;
    }

    private static function sourceUsesInertia(ReflectionFunction|ReflectionMethod $reflection): bool
    {
        $filename = $reflection->getFileName();
        if (!$filename) {
            return false;
        }

        $lines = file($filename);
        if ($lines === false) {
            return false;
        }

        $source = implode('', array_slice(
            $lines,
            $reflection->getStartLine() - 1,
            $reflection->getEndLine() - $reflection->getStartLine() + 1
        ));

        return str_contains($source, 'Inertia::render')
            || str_contains($source, '\\Inertia\\Inertia::render');
    }
}
