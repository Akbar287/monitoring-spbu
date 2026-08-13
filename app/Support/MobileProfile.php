<?php

namespace App\Support;

use App\Models\Member;
use App\Models\UserLogin;

/**
 * Serialisasi profil pengguna untuk API mobile.
 * Dipakai bersama oleh endpoint login, /auth/me, dan /profil supaya klien
 * hanya perlu mengenal satu bentuk objek user.
 */
class MobileProfile
{
    public static function for(UserLogin $user): array
    {
        $member = $user->member()->first();

        if (is_null($member)) {
            return [
                'user_login_id' => $user->user_login_id,
                'username' => $user->username,
                'member_id' => null,
                'nama' => $user->username,
                'level' => null,
                'jabatan' => null,
                'divisi' => null,
                'email' => null,
                'no_hp' => null,
                'avatar_url' => null,
                'spbu' => [],
            ];
        }

        $ktp = $member->ktp()->first();
        $jabatan = $member->jabatan()->first();
        $level = optional(optional($jabatan)->level()->first());
        $divisi = $level ? optional($level->divisi()->first()) : null;

        return [
            'user_login_id' => $user->user_login_id,
            'username' => $user->username,
            'member_id' => $member->member_id,
            'nama' => optional($ktp)->nama ?? $user->username,
            'nik' => optional($ktp)->nik,
            'level' => $level->nama_level ?? null,
            'jabatan' => optional($jabatan)->nama_jabatan,
            'divisi' => $divisi->nama_divisi ?? null,
            'email' => $member->email,
            'no_hp' => $member->no_hp,
            'no_wa' => $member->no_wa,
            'bergabung_sejak' => $member->bergabung_sejak,
            'avatar_url' => self::avatarUrl($member),
            'spbu' => $member->spbu()->get()->map(fn ($s) => [
                'spbu_id' => $s->spbu_id,
                'nama_spbu' => $s->nama_spbu,
                'nomor_spbu' => $s->nomor_spbu,
            ])->values()->all(),
        ];
    }

    public static function avatarUrl(?Member $member): ?string
    {
        if (is_null($member) || empty($member->avatar)) {
            return null;
        }

        return asset('storage/profile_img/' . $member->avatar);
    }

    /**
     * SPBU utama member; dipakai sebagai konteks default dashboard.
     */
    public static function spbuUtama(?Member $member): ?\App\Models\Spbu
    {
        return $member?->spbu()->first();
    }
}
