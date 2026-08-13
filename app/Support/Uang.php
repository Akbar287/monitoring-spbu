<?php

namespace App\Support;

/**
 * Aritmetika desimal presisi tetap untuk modul Procurement.
 *
 * Seluruh kolom uang & kuantitas di database bertipe `decimal(16,6)` /
 * `decimal(18,6)`, jadi perhitungan dilakukan dengan bcmath pada skala 6 —
 * bukan float — supaya tidak ada galat pembulatan biner. Nilai dikembalikan
 * ke klien sebagai STRING (mis. "1234567.891234") agar 6 angka di belakang
 * koma tetap utuh saat melewati JSON dan JavaScript.
 *
 * Perhitungan antara memakai skala lebih tinggi (SKALA_KERJA) lalu
 * dibulatkan half-up ke 6 desimal, sehingga hasil seperti PPN 11% tidak
 * kehilangan digit terakhir.
 */
class Uang
{
    public const SKALA = 6;
    private const SKALA_KERJA = 12;

    /** Normalkan nilai apa pun menjadi string desimal 6 angka. */
    public static function of(mixed $nilai): string
    {
        return self::bulat(self::bersih($nilai));
    }

    /**
     * Buang pemisah ribuan dan spasi; terima "12,500.75" maupun 12500.75.
     *
     * Float dikonversi lewat sprintf, BUKAN cast (string). Cast memakai
     * ini `precision` (bawaan 14 digit signifikan) sehingga nilai seperti
     * 156837600.987654 yang masuk sebagai float JSON akan terpotong menjadi
     * 156837600.98765. Klien tetap disarankan mengirim angka sebagai string.
     */
    public static function bersih(mixed $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return '0';
        }

        if (is_float($nilai)) {
            return rtrim(rtrim(sprintf('%.' . self::SKALA_KERJA . 'F', $nilai), '0'), '.') ?: '0';
        }

        $teks = trim((string) $nilai);
        $teks = str_replace([',', ' ', "\u{00A0}"], '', $teks);

        return is_numeric($teks) ? $teks : '0';
    }

    public static function tambah(mixed ...$nilai): string
    {
        $hasil = '0';
        foreach ($nilai as $n) {
            $hasil = bcadd($hasil, self::bersih($n), self::SKALA_KERJA);
        }

        return self::bulat($hasil);
    }

    public static function kurang(mixed $a, mixed $b): string
    {
        return self::bulat(bcsub(self::bersih($a), self::bersih($b), self::SKALA_KERJA));
    }

    public static function kali(mixed $a, mixed $b): string
    {
        return self::bulat(bcmul(self::bersih($a), self::bersih($b), self::SKALA_KERJA));
    }

    public static function bagi(mixed $a, mixed $b): string
    {
        $pembagi = self::bersih($b);
        if (bccomp($pembagi, '0', self::SKALA_KERJA) === 0) {
            return self::bulat('0');
        }

        return self::bulat(bcdiv(self::bersih($a), $pembagi, self::SKALA_KERJA));
    }

    /** Persentase: nilai × (persen / 100), tetap pada presisi penuh. */
    public static function persen(mixed $nilai, mixed $persen): string
    {
        $rasio = bcdiv(self::bersih($persen), '100', self::SKALA_KERJA);

        return self::bulat(bcmul(self::bersih($nilai), $rasio, self::SKALA_KERJA));
    }

    public static function bandingkan(mixed $a, mixed $b): int
    {
        return bccomp(self::bersih($a), self::bersih($b), self::SKALA);
    }

    public static function nol(mixed $nilai): bool
    {
        return self::bandingkan($nilai, '0') === 0;
    }

    /** Pembulatan half-up ke 6 desimal — bcmath sendiri memotong, bukan membulatkan. */
    public static function bulat(mixed $nilai, int $skala = self::SKALA): string
    {
        $teks = self::bersih($nilai);
        $negatif = str_starts_with($teks, '-');
        if ($negatif) {
            $teks = substr($teks, 1);
        }

        // tambah 0.5 pada digit terakhir yang dipertahankan, lalu potong
        $setengah = '0.' . str_repeat('0', $skala) . '5';
        $hasil = bcadd($teks, $setengah, $skala + 1);
        $hasil = bcadd($hasil, '0', $skala);

        return ($negatif && bccomp($hasil, '0', $skala) !== 0 ? '-' : '') . $hasil;
    }

    /**
     * Jumlahkan sebuah kolom dari koleksi/array pada presisi penuh.
     *
     * @param  iterable<mixed>  $baris
     */
    public static function jumlah(iterable $baris, string $kolom): string
    {
        $total = '0';
        foreach ($baris as $b) {
            $nilai = is_array($b) ? ($b[$kolom] ?? 0) : ($b->{$kolom} ?? 0);
            $total = bcadd($total, self::bersih($nilai), self::SKALA_KERJA);
        }

        return self::bulat($total);
    }
}
