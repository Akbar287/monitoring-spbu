<?php

/**
 * Generator aset ikon aplikasi dari logo SPBU.
 *
 * Sumber: public/images/logo.jpg — berkas JPEG yang latarnya berupa pola
 * papan catur (#EFEFEF/#FFFFFF), yaitu indikator transparansi yang ikut
 * ter-render saat logo diekspor. Pola itu dibuang di sini, bukan dipakai
 * apa adanya, supaya ikon tidak tampil kotak-kotak abu-abu.
 *
 * Jalankan dari akar repositori:
 *   php mobile/scripts/buat-ikon.php
 */

$sumber = __DIR__ . '/../../public/images/logo.jpg';
$tujuan = __DIR__ . '/../assets';

if (! file_exists($sumber)) {
    fwrite(STDERR, "Logo sumber tidak ditemukan: $sumber\n");
    exit(1);
}

$asli = imagecreatefromjpeg($sumber);

// ---------------------------------------------------------------- transparan
// Logo terdiri dari bidang warna jenuh (merah/biru/hijau); latar papan catur
// nyaris abu-abu. Alpha diturunkan dari saturasi agar tepi anti-alias tetap halus.
$w = imagesx($asli);
$h = imagesy($asli);
$bersih = imagecreatetruecolor($w, $h);
imagealphablending($bersih, false);
imagesavealpha($bersih, true);
imagefill($bersih, 0, 0, imagecolorallocatealpha($bersih, 0, 0, 0, 127));

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgb = imagecolorat($asli, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        $maks = max($r, $g, $b);
        $min = min($r, $g, $b);
        $saturasi = $maks - $min;

        // 0 = sepenuhnya latar, 255 = sepenuhnya logo
        $tutupan = (int) min(255, round($saturasi * 255 / 60));
        if ($tutupan <= 4) {
            continue; // biarkan transparan
        }

        $alpha = 127 - (int) round($tutupan * 127 / 255);
        imagesetpixel($bersih, $x, $y, imagecolorallocatealpha($bersih, $r, $g, $b, $alpha));
    }
}

// ------------------------------------------------------------------- pangkas
[$x1, $y1, $x2, $y2] = kotakIsi($bersih, $w, $h);
$lebar = $x2 - $x1 + 1;
$tinggi = $y2 - $y1 + 1;

$logo = imagecreatetruecolor($lebar, $tinggi);
imagealphablending($logo, false);
imagesavealpha($logo, true);
imagecopy($logo, $bersih, 0, 0, $x1, $y1, $lebar, $tinggi);

echo "Logo dipangkas ke {$lebar}x{$tinggi} (dari {$w}x{$h})\n";

// -------------------------------------------------------------------- keluaran
// icon.png — iOS & Android legacy: WAJIB buram, jadi logo ditempel di atas putih
simpan(kanvas(1024, $logo, 0.72, [255, 255, 255]), "$tujuan/icon.png");

// adaptive icon Android: foreground transparan, isi di dalam zona aman (~66%)
simpan(kanvas(1024, $logo, 0.55, null), "$tujuan/android-icon-foreground.png");
simpan(kanvas(1024, null, 0, [255, 255, 255]), "$tujuan/android-icon-background.png");
simpan(monokrom(kanvas(1024, $logo, 0.55, null)), "$tujuan/android-icon-monochrome.png");

// splash & favicon
simpan(kanvas(1024, $logo, 0.62, null), "$tujuan/splash-icon.png");
simpan(kanvas(48, $logo, 0.86, [255, 255, 255]), "$tujuan/favicon.png");

echo "Selesai. Aset ikon diperbarui di mobile/assets/.\n";

// ------------------------------------------------------------------- pembantu

/** Kotak pembatas piksel yang tidak sepenuhnya transparan. */
function kotakIsi($im, int $w, int $h): array
{
    $x1 = $w;
    $y1 = $h;
    $x2 = 0;
    $y2 = 0;

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $alpha = (imagecolorat($im, $x, $y) >> 24) & 0x7F;
            if ($alpha < 120) {
                $x1 = min($x1, $x);
                $y1 = min($y1, $y);
                $x2 = max($x2, $x);
                $y2 = max($y2, $y);
            }
        }
    }

    return $x1 <= $x2 ? [$x1, $y1, $x2, $y2] : [0, 0, $w - 1, $h - 1];
}

/**
 * Kanvas persegi berisi logo yang diskalakan proporsional.
 *
 * @param  float  $isi   proporsi sisi kanvas yang ditempati logo
 * @param  array|null  $latar  warna latar RGB, atau null untuk transparan
 */
function kanvas(int $sisi, $logo, float $isi, ?array $latar)
{
    $out = imagecreatetruecolor($sisi, $sisi);
    imagealphablending($out, false);
    imagesavealpha($out, true);

    $warnaLatar = $latar
        ? imagecolorallocatealpha($out, $latar[0], $latar[1], $latar[2], 0)
        : imagecolorallocatealpha($out, 0, 0, 0, 127);
    imagefilledrectangle($out, 0, 0, $sisi, $sisi, $warnaLatar);

    if ($logo === null) {
        return $out;
    }

    $lw = imagesx($logo);
    $lh = imagesy($logo);
    $skala = ($sisi * $isi) / max($lw, $lh);
    $tw = max(1, (int) round($lw * $skala));
    $th = max(1, (int) round($lh * $skala));

    imagealphablending($out, true);
    imagecopyresampled($out, $logo, (int) (($sisi - $tw) / 2), (int) (($sisi - $th) / 2), 0, 0, $tw, $th, $lw, $lh);
    imagealphablending($out, false);

    return $out;
}

/** Siluet hitam dari kanal alpha — untuk themed icon Android 13+. */
function monokrom($im)
{
    $sisi = imagesx($im);
    $out = imagecreatetruecolor($sisi, $sisi);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagefilledrectangle($out, 0, 0, $sisi, $sisi, imagecolorallocatealpha($out, 0, 0, 0, 127));

    for ($y = 0; $y < $sisi; $y++) {
        for ($x = 0; $x < $sisi; $x++) {
            $alpha = (imagecolorat($im, $x, $y) >> 24) & 0x7F;
            if ($alpha < 127) {
                imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, 0, 0, 0, $alpha));
            }
        }
    }

    return $out;
}

function simpan($im, string $path): void
{
    imagepng($im, $path, 9);
    echo '  ' . basename($path) . ' → ' . imagesx($im) . 'x' . imagesy($im) . "\n";
}
