<?php

namespace App\Support;

use App\Models\Member;

/**
 * Aturan hak akses menu aplikasi mobile.
 *
 * Pembagiannya berbasis **nama jabatan**, bukan level — sebagian jabatan
 * berbagi level yang sama (mis. Admin, Manajer, dan Supervisor sama-sama
 * berlevel "Penanggung Jawab") tetapi hak aksesnya sengaja dibedakan.
 *
 * Kelas ini satu-satunya sumber kebenaran: dipakai middleware
 * EnsureAksesPenuh di server, dan dikirim ke klien sebagai flag
 * `akses_penuh` lewat MobileProfile supaya aplikasi tidak menyalin daftarnya.
 */
class Akses
{
    /**
     * Jabatan yang boleh membuka seluruh menu (Procurement, Master Data,
     * Konfigurasi). Perbandingan tidak peka huruf besar/kecil.
     */
    public const JABATAN_PENUH = [
        'direktur utama',
        'direktur',
        'komisaris',
        'admin',
        'manajer',
    ];

    /**
     * Menu untuk pegawai di luar daftar di atas: Beranda, Jadwal, dan Profil.
     */
    public const MENU_DASAR = ['Beranda', 'Jadwal', 'Profil'];

    /**
     * Satu pegawai bisa memegang lebih dari satu jabatan; cukup salah satunya
     * masuk daftar untuk mendapat akses penuh.
     */
    public static function penuh(?Member $member): bool
    {
        if (is_null($member)) {
            return false;
        }

        foreach ($member->jabatan()->get() as $jabatan) {
            if (self::jabatanPenuh($jabatan->nama_jabatan)) {
                return true;
            }
        }

        return false;
    }

    public static function jabatanPenuh(?string $namaJabatan): bool
    {
        if (is_null($namaJabatan)) {
            return false;
        }

        return in_array(mb_strtolower(trim($namaJabatan)), self::JABATAN_PENUH, true);
    }
}
