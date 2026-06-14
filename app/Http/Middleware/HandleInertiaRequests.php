<?php

namespace App\Http\Middleware;

use App\Support\Navigation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
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
            'uploadMaxKb' => \App\Support\Upload::maxKb(),
        ]);
    }
}
