<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi route ke level jabatan tertentu, mis. `level:Penanggung Jawab`.
 *
 * Dipakai API mobile untuk modul Master Data — di web, pembatasan yang setara
 * hanya berupa penyembunyian menu (App\Support\Navigation), bukan penegakan di
 * server. Lihat ARCHITECTURE.md §5.
 */
class EnsureLevel
{
    public function handle(Request $request, Closure $next, string ...$level): Response
    {
        $member = $request->user()?->member()?->first();
        $namaLevel = optional(optional(optional($member)->jabatan()->first())->level()->first())->nama_level;

        if (is_null($namaLevel) || ! in_array($namaLevel, $level, true)) {
            return ApiResponse::fail(
                'Fitur ini hanya untuk ' . implode(' / ', $level) . '.',
                403,
                ['level_anda' => $namaLevel]
            );
        }

        return $next($request);
    }
}
