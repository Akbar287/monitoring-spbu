<?php

namespace App\Http\Middleware;

use App\Support\Setup;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Selama set up awal belum dituntaskan, admin diarahkan ke /setup.
 *
 * Yang sengaja TIDAK dihalangi:
 *  - rute /setup itu sendiri dan logout;
 *  - seluruh halaman yang justru harus dibuka untuk mengisi set up
 *    (master, konfigurasi, financial, procurement, stok);
 *  - permintaan non-GET, agar form yang sedang diisi tidak putus di tengah;
 *  - API mobile — punya alurnya sendiri dan tidak boleh ikut dialihkan.
 *
 * Pegawai non-admin tidak diarahkan ke mana-mana: mereka memang tidak
 * berwenang mengisi data pondasi.
 */
class PastikanSetupSelesai
{
    /** Awalan URL yang tetap boleh diakses karena dibutuhkan untuk mengisi set up. */
    private const DIIZINKAN = [
        'setup', 'logout', 'profil', 'notifikasi',
        'master', 'konfigurasi', 'financial', 'procurement', 'stok',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || $request->is('api/*') || $request->ajax()) {
            return $next($request);
        }

        if (Setup::selesai()) {
            return $next($request);
        }

        $member = optional($request->user())->member()->first();

        if (is_null($member) || ! $this->admin($member)) {
            return $next($request);
        }

        foreach (self::DIIZINKAN as $awalan) {
            if ($request->is($awalan) || $request->is($awalan . '/*')) {
                return $next($request);
            }
        }

        return redirect()->route('setup.index');
    }

    /**
     * Seorang pegawai bisa memegang lebih dari satu jabatan, jadi seluruhnya
     * ditelusuri — sama seperti App\Support\Akses.
     */
    private function admin(mixed $member): bool
    {
        foreach ($member->jabatan()->get() as $jabatan) {
            $level = optional($jabatan->level()->first())->nama_level;

            if (in_array($level, Setup::LEVEL_ADMIN, true)) {
                return true;
            }
        }

        return false;
    }
}
