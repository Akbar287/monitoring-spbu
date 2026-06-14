<?php

namespace App\Support;

class Upload
{
    /**
     * Batas unggah efektif (KB) = min(upload_max_filesize, post_max_size).
     * Dipakai rule validasi server DAN dibagikan ke FileDropzone via Inertia,
     * sehingga menaikkan limit di php.ini otomatis menaikkan keduanya.
     */
    public static function maxKb(): int
    {
        return (int) floor(min(
            self::toBytes(ini_get('upload_max_filesize')),
            self::toBytes(ini_get('post_max_size'))
        ) / 1024);
    }

    private static function toBytes(string $val): float
    {
        $val = trim($val);
        $unit = strtolower(substr($val, -1));
        $num = (float) $val;

        return match ($unit) {
            'g' => $num * 1024 ** 3,
            'm' => $num * 1024 ** 2,
            'k' => $num * 1024,
            default => $num,
        };
    }
}
