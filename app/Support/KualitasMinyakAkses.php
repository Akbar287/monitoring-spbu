<?php

namespace App\Support;

use App\Models\UserLogin;

class KualitasMinyakAkses
{
    public static function menyetujui(?UserLogin $user): bool
    {
        return self::jabatan($user, ['admin', 'manajer']);
    }

    public static function mencatat(?UserLogin $user): bool
    {
        return self::jabatan($user, ['admin', 'manajer', 'operator']);
    }

    private static function jabatan(?UserLogin $user, array $nama): bool
    {
        return $user?->member?->jabatan->contains(
            fn ($jabatan) => in_array(mb_strtolower(trim($jabatan->nama_jabatan)), $nama, true)
        ) ?? false;
    }
}
