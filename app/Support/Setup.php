<?php

namespace App\Support;

use App\Models\Aset;
use App\Models\Coa;
use App\Models\Dispenser;
use App\Models\Divisi;
use App\Models\Dombak;
use App\Models\Fasilitas;
use App\Models\FileLo;
use App\Models\Harga;
use App\Models\Hari;
use App\Models\Jabatan;
use App\Models\JamKerja;
use App\Models\Jurnal;
use App\Models\Konversi;
use App\Models\Level;
use App\Models\Member;
use App\Models\Ms2;
use App\Models\Nozzle;
use App\Models\PajakPembelianLib;
use App\Models\Payung;
use App\Models\PengaturanSistem;
use App\Models\Produk;
use App\Models\RencanaPembelian;
use App\Models\Spbu;
use App\Models\StokInventory;

/**
 * Sumber kebenaran tunggal untuk halaman Set Up (/setup).
 *
 * Halaman ini diisi admin saat pertama kali memakai aplikasi. Urutan langkah
 * di sini BUKAN sekadar tampilan: tiap langkah menyebut prasyaratnya lewat
 * `butuh`, dan langkah yang prasyaratnya belum beres akan terkunci. Alasannya
 * data di sistem ini saling berantai — nozzle butuh dispenser, dispenser butuh
 * dombak, dombak butuh produk, dan seterusnya.
 *
 * Khusus tahap pengadaan, rantainya wajib dituruti dari perencanaan → MS2 →
 * LO/pengiriman, karena penerimaan BBM hanya bisa diproses dari LO yang lahir
 * dari rantai tersebut. Itu sebabnya kondisi awal pengadaan tidak dibuatkan
 * jalur pintas: admin mengisi lewat form pengadaan yang asli agar seluruh efek
 * ikutannya (jurnal, stok, pajak) terbentuk persis seperti operasi normal.
 */
class Setup
{
    public const KUNCI_SELESAI = 'setup_selesai_at';
    public const KUNCI_MULAI_OPERASI = 'tanggal_mulai_operasi';

    /** Level yang dianggap admin dan diarahkan mengisi set up. */
    public const LEVEL_ADMIN = ['Penanggung Jawab', 'Direktur Utama', 'Direktur', 'Komisaris'];

    public static function selesai(): bool
    {
        return ! is_null(PengaturanSistem::ambil(self::KUNCI_SELESAI));
    }

    public static function tanggalMulaiOperasi(): ?string
    {
        return PengaturanSistem::ambil(self::KUNCI_MULAI_OPERASI);
    }

    public static function tandaiSelesai(): void
    {
        PengaturanSistem::simpan(self::KUNCI_SELESAI, now()->toDateTimeString());
    }

    public static function bukaKembali(): void
    {
        PengaturanSistem::hapus(self::KUNCI_SELESAI);
    }

    /**
     * Definisi seluruh langkah, sudah terurut. `hitung` sengaja memakai
     * count() ringan — halaman set up dipanggil tiap kali admin membukanya.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function definisi(): array
    {
        return [
            [
                'kunci' => 'identitas',
                'judul' => 'Identitas SPBU & tanggal mulai operasi',
                'deskripsi' => 'Data pokok SPBU dan tanggal aplikasi mulai dipakai. Tanggal ini menjadi batas antara data lama dan transaksi yang dicatat aplikasi.',
                'ikon' => 'fa-gas-pump',
                'wajib' => true,
                'butuh' => [],
                'url' => route('setup.identitas'),
                'kelola' => route('spbu.index'),
                'hitung' => fn () => Spbu::count(),
                'selesai' => fn () => Spbu::count() > 0 && ! is_null(self::tanggalMulaiOperasi()),
                'satuan' => 'SPBU',
            ],
            [
                'kunci' => 'organisasi',
                'judul' => 'Struktur organisasi',
                'deskripsi' => 'Divisi → Level → Jabatan. Hak akses menu, penempatan pegawai, dan aturan jadwal semuanya menggantung pada rantai ini.',
                'ikon' => 'fa-sitemap',
                'wajib' => true,
                'butuh' => ['identitas'],
                'url' => route('divisi.index'),
                'rincian' => [
                    ['label' => 'Divisi', 'url' => route('divisi.index'), 'hitung' => fn () => Divisi::count()],
                    ['label' => 'Level', 'url' => route('level.index'), 'hitung' => fn () => Level::count()],
                    ['label' => 'Jabatan', 'url' => route('jabatan.index'), 'hitung' => fn () => Jabatan::count()],
                ],
                'hitung' => fn () => Jabatan::count(),
                'selesai' => fn () => Divisi::count() > 0 && Level::count() > 0 && Jabatan::count() > 0,
                'satuan' => 'jabatan',
            ],
            [
                'kunci' => 'waktu_kerja',
                'judul' => 'Hari & jam kerja',
                'deskripsi' => 'Dasar penjadwalan dan absensi. Shift yang dipakai operator diambil dari jam kerja yang didaftarkan di sini.',
                'ikon' => 'fa-clock',
                'wajib' => true,
                'butuh' => ['organisasi'],
                'url' => route('jam_kerja.index'),
                'rincian' => [
                    ['label' => 'Hari', 'url' => route('hari.index'), 'hitung' => fn () => Hari::count()],
                    ['label' => 'Jam kerja', 'url' => route('jam_kerja.index'), 'hitung' => fn () => JamKerja::count()],
                ],
                'hitung' => fn () => JamKerja::count(),
                'selesai' => fn () => Hari::count() > 0 && JamKerja::count() > 0,
                'satuan' => 'jam kerja',
            ],
            [
                'kunci' => 'pajak',
                'judul' => 'Pajak pembelian',
                'deskripsi' => 'Jenis dan tarif pajak yang melekat pada pembelian BBM. Dipakai saat menghitung nilai pembelian dan saat memposting jurnalnya.',
                'ikon' => 'fa-percent',
                'wajib' => true,
                'butuh' => ['identitas'],
                'url' => route('pajak_pembelian_lib.index'),
                'hitung' => fn () => PajakPembelianLib::count(),
                'selesai' => fn () => PajakPembelianLib::count() > 0,
                'satuan' => 'jenis pajak',
            ],
            [
                'kunci' => 'pegawai',
                'judul' => 'Pegawai & akun login',
                'deskripsi' => 'Daftar pegawai beserta penempatan jabatannya. Tanpa jabatan, seorang pegawai tidak mendapat hak menu apa pun.',
                'ikon' => 'fa-users',
                'wajib' => true,
                'butuh' => ['organisasi'],
                'url' => route('member.index'),
                'hitung' => fn () => Member::count(),
                'selesai' => fn () => Member::count() > 0,
                'satuan' => 'pegawai',
            ],
            [
                'kunci' => 'produk',
                'judul' => 'Produk & harga jual',
                'deskripsi' => 'Jenis BBM yang dijual beserta harga berlakunya. Harga menjadi dasar nilai penjualan dan setoran.',
                'ikon' => 'fa-fill-drip',
                'wajib' => true,
                'butuh' => ['identitas'],
                'url' => route('produk.index'),
                'rincian' => [
                    ['label' => 'Produk', 'url' => route('produk.index'), 'hitung' => fn () => Produk::count()],
                    ['label' => 'Harga', 'url' => route('harga.index'), 'hitung' => fn () => Harga::count()],
                ],
                'hitung' => fn () => Produk::count(),
                'selesai' => fn () => Produk::count() > 0 && Harga::count() > 0,
                'satuan' => 'produk',
            ],
            [
                'kunci' => 'sarana',
                'judul' => 'Sarana penyaluran',
                'deskripsi' => 'Dombak (tangki pendam) beserta tabel konversi tinggi→volume, lalu dispenser dan nozzle yang menempel padanya.',
                'ikon' => 'fa-oil-can',
                'wajib' => true,
                'butuh' => ['produk'],
                'url' => route('dombak.index'),
                'rincian' => [
                    ['label' => 'Dombak', 'url' => route('dombak.index'), 'hitung' => fn () => Dombak::count()],
                    ['label' => 'Tabel konversi', 'url' => route('dombak.index'), 'hitung' => fn () => Konversi::count()],
                    ['label' => 'Dispenser', 'url' => route('dispenser.index'), 'hitung' => fn () => Dispenser::count()],
                    ['label' => 'Nozzle', 'url' => route('nozzle.index'), 'hitung' => fn () => Nozzle::count()],
                ],
                'hitung' => fn () => Dombak::count(),
                'selesai' => fn () => Dombak::count() > 0 && Dispenser::count() > 0 && Nozzle::count() > 0,
                'satuan' => 'dombak',
            ],
            [
                'kunci' => 'sarana_pendukung',
                'judul' => 'Aset, fasilitas & payung',
                'deskripsi' => 'Pelengkap. Boleh dilewati sekarang dan diisi belakangan tanpa mengganggu operasi.',
                'ikon' => 'fa-box',
                'wajib' => false,
                'butuh' => ['identitas'],
                'url' => route('aset.index'),
                'rincian' => [
                    ['label' => 'Aset', 'url' => route('aset.index'), 'hitung' => fn () => Aset::count()],
                    ['label' => 'Fasilitas', 'url' => route('fasilitas.index'), 'hitung' => fn () => Fasilitas::count()],
                    ['label' => 'Payung', 'url' => route('payung.index'), 'hitung' => fn () => Payung::count()],
                ],
                'hitung' => fn () => Aset::count() + Fasilitas::count() + Payung::count(),
                'selesai' => fn () => (Aset::count() + Fasilitas::count() + Payung::count()) > 0,
                'satuan' => 'entri',
            ],
            [
                'kunci' => 'coa',
                'judul' => 'Bagan akun (COA)',
                'deskripsi' => 'Daftar kode akun. Seluruh jurnal — pembelian, setoran, losses — mengacu ke kode akun di sini, jadi harus lengkap sebelum saldo awal diisi.',
                'ikon' => 'fa-list-ol',
                'wajib' => true,
                'butuh' => ['identitas'],
                'url' => route('coa.index'),
                'hitung' => fn () => Coa::count(),
                'selesai' => fn () => Coa::count() > 0,
                'satuan' => 'akun',
            ],
            [
                'kunci' => 'saldo_awal',
                'judul' => 'Saldo awal akun',
                'deskripsi' => 'Posisi kas, bank, persediaan, utang, dan modal pada tanggal mulai operasi. Tanpa ini neraca hari pertama tidak seimbang.',
                'ikon' => 'fa-scale-balanced',
                'wajib' => true,
                'butuh' => ['coa'],
                'url' => route('saldo-awal.index'),
                'hitung' => fn () => Jurnal::where('tipe_sumber', 'saldo_awal')->count(),
                'selesai' => fn () => Jurnal::where('tipe_sumber', 'saldo_awal')->count() > 0,
                'satuan' => 'jurnal saldo awal',
            ],
            [
                'kunci' => 'stok_awal',
                'judul' => 'Stok awal per dombak',
                'deskripsi' => 'Isi fisik tiap dombak pada tanggal mulai operasi, diukur lewat monitoring stok. Menjadi titik nol perhitungan losses dan penjualan.',
                'ikon' => 'fa-gauge-high',
                'wajib' => true,
                'butuh' => ['sarana'],
                'url' => route('monitoring.index'),
                'hitung' => fn () => StokInventory::count(),
                'selesai' => fn () => StokInventory::count() > 0,
                'satuan' => 'kartu stok',
            ],
            [
                'kunci' => 'pengadaan_awal',
                'judul' => 'Pengadaan yang sudah berjalan',
                'deskripsi' => 'Pembelian atau pengiriman yang terjadi sebelum aplikasi diaktifkan. Isi berurutan: perencanaan dahulu, lalu MS2, lalu LO — karena penerimaan BBM hanya bisa diproses dari LO hasil rantai ini.',
                'ikon' => 'fa-truck-fast',
                'wajib' => false,
                'butuh' => ['produk', 'pajak'],
                'url' => route('perencanaan.index'),
                'berantai' => true,
                'rincian' => [
                    ['label' => '1. Perencanaan pembelian', 'url' => route('perencanaan.index'), 'hitung' => fn () => RencanaPembelian::count()],
                    ['label' => '2. MS2', 'url' => route('ms2.produk.index'), 'hitung' => fn () => Ms2::count()],
                    ['label' => '3. LO / pengiriman', 'url' => route('pengiriman.index'), 'hitung' => fn () => FileLo::count()],
                ],
                'hitung' => fn () => FileLo::count(),
                'selesai' => fn () => RencanaPembelian::count() > 0,
                'satuan' => 'LO',
            ],
        ];
    }

    /**
     * Definisi + status terhitung, siap dikirim ke Inertia.
     * Closure sengaja tidak ikut dikirim ke frontend.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function langkah(): array
    {
        $selesaiPer = [];
        $hasil = [];

        foreach (self::definisi() as $langkah) {
            $terkunci = false;
            $penghalang = [];

            foreach ($langkah['butuh'] as $prasyarat) {
                if (($selesaiPer[$prasyarat] ?? false) !== true) {
                    $terkunci = true;
                    $penghalang[] = $prasyarat;
                }
            }

            $sudah = ($langkah['selesai'])();
            $selesaiPer[$langkah['kunci']] = $sudah;

            // Langkah yang datanya sudah terisi tidak pernah dikunci — kunci
            // hanya berguna untuk mencegah pengisian yang menggantung.
            if ($sudah) {
                $terkunci = false;
                $penghalang = [];
            }

            $rincian = [];
            foreach ($langkah['rincian'] ?? [] as $r) {
                $rincian[] = [
                    'label' => $r['label'],
                    'url' => $r['url'],
                    'jumlah' => ($r['hitung'])(),
                ];
            }

            $hasil[] = [
                'kunci' => $langkah['kunci'],
                'judul' => $langkah['judul'],
                'deskripsi' => $langkah['deskripsi'],
                'ikon' => $langkah['ikon'],
                'wajib' => $langkah['wajib'],
                'berantai' => $langkah['berantai'] ?? false,
                'url' => $langkah['url'],
                'kelola' => $langkah['kelola'] ?? null,
                'jumlah' => ($langkah['hitung'])(),
                'satuan' => $langkah['satuan'],
                'selesai' => $sudah,
                'terkunci' => $terkunci,
                'penghalang' => $penghalang,
                'rincian' => $rincian,
            ];
        }

        return $hasil;
    }

    /**
     * Ringkasan kemajuan. `bisa_diselesaikan` hanya benar bila seluruh
     * langkah wajib sudah beres.
     *
     * @return array<string, mixed>
     */
    public static function kemajuan(?array $langkah = null): array
    {
        $langkah ??= self::langkah();

        $wajib = array_filter($langkah, fn ($l) => $l['wajib']);
        $wajibSelesai = array_filter($wajib, fn ($l) => $l['selesai']);

        return [
            'total' => count($langkah),
            'selesai' => count(array_filter($langkah, fn ($l) => $l['selesai'])),
            'wajib' => count($wajib),
            'wajib_selesai' => count($wajibSelesai),
            'persen' => count($wajib) === 0 ? 100 : (int) floor(count($wajibSelesai) / count($wajib) * 100),
            'bisa_diselesaikan' => count($wajib) === count($wajibSelesai),
        ];
    }

    /**
     * Langkah wajib berikutnya yang belum beres dan tidak terkunci —
     * dipakai tombol "lanjutkan" agar admin tidak perlu memilih sendiri.
     */
    public static function berikutnya(?array $langkah = null): ?array
    {
        foreach ($langkah ?? self::langkah() as $l) {
            if ($l['wajib'] && ! $l['selesai'] && ! $l['terkunci']) {
                return $l;
            }
        }

        return null;
    }
}
