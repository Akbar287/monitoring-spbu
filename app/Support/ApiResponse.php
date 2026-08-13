<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Amplop respons seragam untuk API mobile (routes/mobile.php).
 * Bentuknya mengikuti konvensi JSON yang sudah dipakai alur kehadiran lama
 * di KehadiranOperatorController: { status, message, data }.
 */
class ApiResponse
{
    public static function ok(mixed $data = [], string $message = 'OK', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function fail(string $message, int $code = 400, mixed $data = []): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Bungkus hasil paginator agar klien punya metadata halaman yang konsisten.
     */
    public static function paginated(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator, callable $map, string $message = 'OK'): JsonResponse
    {
        return self::ok([
            'items' => collect($paginator->items())->map($map)->values()->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'has_more' => $paginator->hasMorePages(),
            ],
        ], $message);
    }
}
