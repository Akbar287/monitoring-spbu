<?php

namespace App\Support;

use App\Models\AstmDensity;
use Illuminate\Validation\ValidationException;

class AstmLookup
{
    public static function cari(float $density, float $suhu, string $field = 'density'): AstmDensity
    {
        // Tidak membulatkan input ke sel tetangga dan tidak mengisi lubang tabel.
        if (abs($density * 1000 - round($density * 1000)) > 0.00001
            || abs($suhu * 2 - round($suhu * 2)) > 0.00001) {
            throw ValidationException::withMessages([$field => 'Tabel sumber menggunakan density kelipatan 0,001 g/cm³ dan suhu kelipatan 0,5 °C.']);
        }

        $cell = AstmDensity::where('density_observed', number_format($density, 4, '.', ''))
            ->where('suhu', number_format($suhu, 2, '.', ''))->first();

        if (! $cell) {
            throw ValidationException::withMessages([$field => 'Pasangan density dan suhu tidak tersedia atau tidak konsisten pada PDF ASTM. Hasil tidak dapat dihitung.']);
        }

        return $cell;
    }
}
