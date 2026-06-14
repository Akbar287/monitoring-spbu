<?php

namespace App\Support;

class Navigation
{
    /**
     * Anak pertama tiap modul: link "Ringkasan" menuju halaman menu modul
     * yang sudah Inertia (flag inertia => navigasi SPA via <Link> + NProgress).
     */
    private static function ringkasan(string $url, array $children): array
    {
        return array_merge(
            [['label' => 'Ringkasan', 'url' => $url, 'inertia' => true, 'exact' => true]],
            $children
        );
    }

    /**
     * Struktur menu sidebar berdasarkan level jabatan.
     * Active-state dihitung di sisi React lewat prefix-match URL,
     * jadi tidak perlu daftar nama route seperti di Blade.
     *
     * @return array<int, array{label:string,icon:string,url?:string,inertia?:bool,children?:array}>
     */
    public static function for(?string $level): array
    {
        $pj = $level === 'Penanggung Jawab';
        $direksi = in_array($level, ['Komisaris', 'Direktur Utama', 'Direktur']);
        $operator = $level === 'Operator';
        $keamanan = $level === 'Keamanan';

        $menu = [];

        $menu[] = ['label' => 'Dashboard', 'icon' => 'home', 'url' => url('/home')];

        if ($pj) {
            $menu[] = ['label' => 'Master Data', 'icon' => 'th', 'url' => url('/master'), 'children' => self::ringkasan(url('/master'), [
                ['label' => 'Aset', 'url' => url('/master/aset')],
                ['label' => 'Fasilitas', 'url' => url('/master/fasilitas')],
                ['label' => 'Produk', 'url' => url('/master/produk')],
                ['label' => 'Pegawai', 'url' => url('/master/member')],
                ['label' => 'Harga', 'url' => url('/master/harga')],
                ['label' => 'Dombak', 'url' => url('/master/dombak')],
                ['label' => 'Payung', 'url' => url('/master/payung')],
                ['label' => 'Dispenser', 'url' => url('/master/dispenser')],
                ['label' => 'Nozzle', 'url' => url('/master/nozzle')],
            ])];
        }

        if ($pj || $direksi || $operator) {
            $menu[] = ['label' => 'Procurement', 'icon' => 'filter', 'url' => url('/procurement'), 'children' => self::ringkasan(url('/procurement'), [
                ['label' => 'Perencanaan', 'url' => url('/procurement/perencanaan')],
                ['label' => 'Pembelian', 'url' => url('/procurement/pembelian')],
                ['label' => 'Pembayaran', 'url' => url('/procurement/pembayaran')],
                ['label' => 'MS2', 'url' => url('/procurement/ms2')],
                ['label' => 'Delivery', 'url' => url('/procurement/delivery')],
            ])];

            $stok = [];
            if (!$operator) {
                $stok[] = ['label' => 'Monitoring Stok', 'url' => url('/stok/monitoring')];
            }
            $stok[] = ['label' => 'Penerimaan BBM', 'url' => url('/stok/penerimaan')];
            $stok[] = ['label' => 'Stok Taking', 'url' => url('/stok/stok-taking')];
            $stok[] = ['label' => 'Stok Transfer', 'url' => url('/stok/stok-transfer')];
            $stok[] = ['label' => 'Stok Summary', 'url' => url('/stok/stok-summary')];
            $menu[] = ['label' => 'Manajemen Persediaan', 'icon' => 'tint', 'url' => url('/stok'), 'children' => self::ringkasan(url('/stok'), $stok)];
        }

        if ($pj) {
            $menu[] = ['label' => 'Tera', 'icon' => 'upload', 'url' => url('/tera'), 'children' => self::ringkasan(url('/tera'), [
                ['label' => 'Stok Peminjaman Tera', 'url' => url('/tera/stok-peminjaman')],
                ['label' => 'Stok Pengembalian Tera', 'url' => url('/tera/stok-pengembalian')],
            ])];
        }

        if ($pj || $direksi || $operator) {
            $menu[] = ['label' => 'Totalisator', 'icon' => 'thermometer-full', 'url' => url('/stok/totalisator')];

            $menu[] = ['label' => 'Setoran', 'icon' => 'percent', 'url' => url('/setoran'), 'children' => self::ringkasan(url('/setoran'), [
                ['label' => 'Open Penjualan', 'url' => url('/setoran/open-penjualan')],
                ['label' => 'Penitipan Setoran', 'url' => url('/setoran/penitipan-setoran')],
                ['label' => 'Closing Penjualan', 'url' => url('/setoran/closing-penjualan')],
                ['label' => 'Setoran Bank', 'url' => url('/setoran/setoran-bank')],
            ])];
        }

        if ($pj) {
            $menu[] = ['label' => 'Artikel', 'icon' => 'newspaper', 'url' => url('/artikel'), 'children' => self::ringkasan(url('/artikel'), [
                ['label' => 'Kategori', 'url' => url('/artikel/kategori')],
                ['label' => 'Tag', 'url' => url('/artikel/tag')],
                ['label' => 'Post', 'url' => url('/artikel/post')],
            ])];
        }

        if ($pj || $direksi) {
            $menu[] = ['label' => 'Financial', 'icon' => 'file', 'url' => url('/financial'), 'children' => self::ringkasan(url('/financial'), [
                ['label' => 'Petty Cash', 'url' => url('/financial/petty-cash')],
                ['label' => 'Rekap Pajak', 'url' => url('/financial/rekap-pajak')],
                ['label' => 'Margin', 'url' => url('/financial/margin')],
                ['label' => 'Laba Rugi', 'url' => url('/financial/laba-rugi')],
                ['label' => 'Neraca', 'url' => url('/financial/neraca')],
                ['label' => 'Buku Besar', 'url' => url('/financial/buku-besar')],
                ['label' => 'Arus Kas', 'url' => url('/financial/arus-kas')],
                ['label' => 'Perubahan Modal', 'url' => url('/financial/perubahan-modal')],
            ])];
        }

        if ($pj) {
            $menu[] = ['label' => 'Jadwal & Absensi', 'icon' => 'calendar', 'url' => url('/jadwal_absensi'), 'children' => self::ringkasan(url('/jadwal_absensi'), [
                ['label' => 'Penjadwalan Pegawai', 'url' => route('jadwal-kerja.index')],
                ['label' => 'Absensi', 'url' => route('absensi.index')],
                ['label' => 'Laporan Rekap', 'url' => route('laporan-absensi.index')],
            ])];

            $menu[] = ['label' => 'Konfigurasi', 'icon' => 'wrench', 'url' => url('/konfigurasi'), 'children' => self::ringkasan(url('/konfigurasi'), [
                ['label' => 'SPBU', 'url' => url('/konfigurasi/spbu')],
                ['label' => 'Divisi', 'url' => url('/konfigurasi/divisi')],
                ['label' => 'Level', 'url' => url('/konfigurasi/level')],
                ['label' => 'Jabatan', 'url' => url('/konfigurasi/jabatan')],
                ['label' => 'Pajak', 'url' => url('/konfigurasi/pajak')],
                ['label' => 'Hari', 'url' => url('/konfigurasi/hari')],
                ['label' => 'Jam Kerja', 'url' => url('/konfigurasi/jam_kerja')],
                ['label' => 'Import Excel', 'url' => url('/konfigurasi/import')],
            ])];
        }

        if ($operator || $keamanan || $pj) {
            $menu[] = ['label' => 'Kehadiran', 'icon' => 'calendar-check', 'url' => route('menu.kehadiran')];
        }

        return $menu;
    }
}
