<?php

namespace App\Http\Middleware;

use App\Support\Akses;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi route ke jabatan yang berhak membuka seluruh menu
 * (lihat App\Support\Akses::JABATAN_PENUH).
 *
 * Dipakai API mobile untuk Procurement, Master Data, dan Konfigurasi. Di web,
 * pembatasan yang setara hanya berupa penyembunyian menu
 * (App\Support\Navigation), bukan penegakan di server — lihat ARCHITECTURE.md §5.
 */
class EnsureAksesPenuh
{
    public function handle(Request $request, Closure $next): Response
    {
        $member = $request->user()?->member()?->first();

        if (! Akses::penuh($member)) {
            $jabatan = optional($member?->jabatan()->first())->nama_jabatan;

            return ApiResponse::fail(
                'Menu ini hanya untuk jabatan Direktur Utama, Direktur, Komisaris, Admin, dan Manajer.',
                403,
                ['jabatan_anda' => $jabatan]
            );
        }

        return $next($request);
    }
}
