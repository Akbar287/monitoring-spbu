<?php

namespace App\Support;

use App\Models\Dombak;
use App\Models\DokumenStok;
use App\Models\JenisLosses;
use App\Models\Konversi;
use App\Models\StokInventory;
use App\Models\TypeDokumenStok;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Logika bersama modul Manajemen Persediaan.
 *
 * Seluruh pergerakan stok melewati tabel `dokumen_stok` (stok_awal ->
 * stok_akhir per dombak per shift) dan baru memengaruhi saldo saat
 * dikonfirmasi — lihat DATABASE.md §3.6.
 *
 * Semua aritmetika memakai App\Support\Uang (bcmath skala 6) karena kolom
 * stok bertipe decimal(16,6); nilai dikembalikan ke klien sebagai STRING
 * agar 6 angka di belakang koma tidak hilang saat melewati JSON.
 */
class Persediaan
{
    // jenis pergerakan yang dipakai modul ini
    public const GERAK_STOK_TAKING = 'Stok Taking';
    public const GERAK_TERIMA = 'Good Receive Penerimaan';
    public const GERAK_TERIMA_TAKING = 'Stok Taking Good Receive Penerimaan';
    public const GERAK_TERIMA_PENYESUAIAN = 'Stok Taking Good Receive Penyesuaian';
    public const GERAK_KIRIM = 'Good Transfer Send';
    public const GERAK_TERIMA_TRANSFER = 'Good Transfer Receive';
    public const GERAK_TRANSFER_TAKING = 'Stok Taking Good Transfer';
    public const GERAK_TRANSFER_PENYESUAIAN = 'Stok Taking Good Transfer Penyesuaian';

    public static function tipe(string $movement): TypeDokumenStok
    {
        $tipe = TypeDokumenStok::where('type_movement', $movement)->first();

        if (is_null($tipe)) {
            throw new RuntimeException("Jenis pergerakan stok \"{$movement}\" belum ada di tabel type_dokumen_stok.");
        }

        return $tipe;
    }

    public static function jenisLosses(string $nama): ?JenisLosses
    {
        return JenisLosses::where('jenis_loss', $nama)->first();
    }

    /**
     * Cari baris konversi tinggi -> volume milik sebuah dombak.
     * Tinggi dicocokkan pada presisi 6 desimal supaya "150" dan "150.000000"
     * dianggap sama.
     */
    public static function konversi(Dombak $dombak, mixed $tinggi): ?Konversi
    {
        $cari = Uang::of($tinggi);

        return $dombak->konversi()
            ->whereRaw('ROUND(tinggi::numeric, 6) = ?', [$cari])
            ->first();
    }

    /** Volume dari tinggi; melempar bila tinggi tidak ada di tabel konversi. */
    public static function volumeDariTinggi(Dombak $dombak, mixed $tinggi): string
    {
        $baris = self::konversi($dombak, $tinggi);

        if (is_null($baris)) {
            throw new RuntimeException(
                "Tinggi {$tinggi} tidak ada di tabel konversi dombak {$dombak->nama_dombak}. "
                . 'Unggah tabel konversi lebih dulu lewat menu Master → Dombak.'
            );
        }

        return Uang::of($baris->volume);
    }

    /** Saldo dombak saat ini (string 6 desimal). */
    public static function stokDombak(Dombak $dombak): string
    {
        $baris = $dombak->stok_inventory_dombak()->first();

        return Uang::of($baris->stok ?? 0);
    }

    public static function inventoryDariDombak(Dombak $dombak): ?StokInventory
    {
        return $dombak->stok_inventory_dombak()->first()?->stok_inventory()->first();
    }

    /**
     * Hitung ulang total `stok_inventory.stok` dari penjumlahan seluruh
     * `stok_inventory_dombak` miliknya.
     *
     * Alur web menghitung total ini secara ad-hoc di tiap titik konfirmasi
     * (dan di satu tempat memakai dokumen yang salah), sehingga totalnya bisa
     * menyimpang dari jumlah per dombak. Di sini totalnya selalu diturunkan
     * ulang supaya tidak mungkin melenceng.
     */
    public static function sinkronkanTotal(StokInventory $inventory): string
    {
        $total = Uang::jumlah($inventory->stok_inventory_dombak()->get(), 'stok');
        $inventory->update(['stok' => $total]);

        return $total;
    }

    /**
     * Terapkan sebuah dokumen stok ke saldo: saldo dombak menjadi stok_akhir,
     * lalu total produk disinkronkan. Menandai dokumen sebagai terkonfirmasi.
     */
    public static function terapkan(DokumenStok $dokumen): void
    {
        $inventory = $dokumen->stok_inventory()->first();
        $dombak = $dokumen->dombak()->first();

        if (is_null($inventory) || is_null($dombak)) {
            throw new RuntimeException('Dokumen stok tidak lengkap: inventory atau dombak tidak ditemukan.');
        }

        $baris = $inventory->stok_inventory_dombak()->where('dombak_id', $dombak->dombak_id)->first();

        if (is_null($baris)) {
            throw new RuntimeException(
                "Dombak {$dombak->nama_dombak} belum terhubung ke produk ini. "
                . 'Hubungkan lewat Monitoring Stok → Dombak.'
            );
        }

        $baris->update(['stok' => Uang::of($dokumen->stok_akhir)]);
        self::sinkronkanTotal($inventory);

        $dokumen->update(['is_confirmation' => 1]);
    }

    /**
     * Buat dokumen stok bila kombinasi yang sama persis belum ada
     * (idempoten — mengikuti pola alur web supaya kiriman ganda tidak
     * menghasilkan dua dokumen).
     */
    public static function dokumen(
        TypeDokumenStok $tipe,
        StokInventory $inventory,
        string $memberId,
        string $dombakId,
        string $jamKerjaId,
        string $tanggal,
        mixed $awal,
        mixed $akhir,
        ?string $waktu = null
    ): DokumenStok {
        $awal = Uang::of($awal);
        $akhir = Uang::of($akhir);

        $ada = $tipe->dokumen_stok()
            ->where('stok_inventory_id', $inventory->stok_inventory_id)
            ->where('member_id', $memberId)
            ->where('dombak_id', $dombakId)
            ->where('jam_kerja_id', $jamKerjaId)
            ->where('tanggal', $tanggal)
            ->where('stok_awal', $awal)
            ->where('stok_akhir', $akhir)
            ->where('is_confirmation', 0)
            ->first();

        if ($ada) {
            return $ada;
        }

        return $tipe->dokumen_stok()->create([
            'stok_inventory_id' => $inventory->stok_inventory_id,
            'member_id' => $memberId,
            'dombak_id' => $dombakId,
            'jam_kerja_id' => $jamKerjaId,
            'tanggal' => $tanggal,
            'stok_awal' => $awal,
            'stok_akhir' => $akhir,
            'is_confirmation' => 0,
            'waktu' => $waktu ?: now()->format('H:i:s'),
        ]);
    }

    /** Kaitkan dokumen ke baris konversi yang dipakai (untuk jejak audit tinggi). */
    public static function kaitkanKonversi(DokumenStok $dokumen, ?Konversi $konversi): void
    {
        if (is_null($konversi)) {
            return;
        }

        $ada = DB::table('konversi_dokumen_stok')
            ->where('konversi_id', $konversi->konversi_id)
            ->where('dokumen_stok_id', $dokumen->dokumen_stok_id)
            ->exists();

        if (! $ada) {
            DB::table('konversi_dokumen_stok')->insert([
                'konversi_id' => $konversi->konversi_id,
                'dokumen_stok_id' => $dokumen->dokumen_stok_id,
            ]);
        }
    }

    /**
     * Catat selisih sebagai losses (kurang) atau profit (lebih).
     * Selisih nol tidak dicatat. Baris lama untuk dokumen ini diganti supaya
     * tidak menumpuk saat dokumen dikirim ulang.
     */
    public static function catatSelisih(DokumenStok $dokumen, string $namaJenis, string $tanggal, mixed $selisih): void
    {
        $jenis = self::jenisLosses($namaJenis);
        if (is_null($jenis) || Uang::nol($selisih)) {
            return;
        }

        $kurang = Uang::bandingkan($selisih, '0') < 0;
        $jumlah = $kurang ? Uang::kali($selisih, '-1') : Uang::of($selisih);

        // WAJIB per-model: mass delete lewat query builder TIDAK memicu event
        // `deleted` pada Losses, sehingga jurnal susutnya tertinggal yatim dan
        // pembuatan berikutnya memposting jurnal kedua.
        self::hapusLosses($dokumen);

        $dokumen->losses()->create([
            'jenis_losses_id' => $jenis->jenis_losses_id,
            'tanggal' => $tanggal,
            'simbol' => $kurang ? 'kurang' : 'lebih',
            'jumlah' => $jumlah,
        ]);
    }

    /**
     * Nama dombak sebuah dokumen, aman terhadap dokumen null.
     *
     * `optional($x)->relasi()` mengembalikan null, sehingga `->first()`
     * setelahnya akan fatal — pola itu tidak boleh dipakai untuk rantai relasi.
     */
    public static function namaDombak(?DokumenStok $d): ?string
    {
        return $d ? optional($d->dombak()->first())->nama_dombak : null;
    }

    /**
     * Hapus seluruh losses sebuah dokumen satu per satu agar event `deleted`
     * pada model Losses berjalan dan jurnal susutnya ikut dibatalkan
     * (App\Support\PostingKeuangan::batalLosses).
     */
    public static function hapusLosses(DokumenStok $dokumen): void
    {
        foreach ($dokumen->losses()->get() as $loss) {
            $loss->delete();
        }
    }

    /**
     * Hapus dokumen stok dengan aman: losses dibatalkan lewat model dulu,
     * baru dokumennya. Menghapus dokumen secara langsung akan menghapus baris
     * losses lewat cascade database — tanpa event, sehingga jurnal tertinggal.
     */
    public static function hapusDokumen(DokumenStok $dokumen): void
    {
        self::hapusLosses($dokumen);
        $dokumen->delete();
    }

    /** Bentuk seragam sebuah dokumen stok untuk klien. */
    public static function serialDokumen(DokumenStok $d, bool $lengkap = false): array
    {
        $dombak = $d->dombak()->first();
        $inventory = $d->stok_inventory()->first();
        $produk = $inventory?->produk()->first();
        $loss = $d->losses()->first();

        $data = [
            'id' => $d->dokumen_stok_id,
            'dokumen_stok_id' => $d->dokumen_stok_id,
            'tanggal' => $d->tanggal,
            'waktu' => $d->waktu,
            'jenis' => optional($d->type_dokumen_stok()->first())->type_movement,
            'produk' => optional($produk)->nama_produk,
            'dombak' => optional($dombak)->nama_dombak,
            'jam_kerja' => optional($d->jam_kerja()->first())->nama_jam_kerja,
            'stok_awal' => Uang::of($d->stok_awal),
            'stok_akhir' => Uang::of($d->stok_akhir),
            'selisih' => Uang::kurang($d->stok_akhir, $d->stok_awal),
            'is_confirmation' => filter_var($d->is_confirmation, FILTER_VALIDATE_BOOLEAN),
            'losses' => $loss ? [
                'jenis' => optional($loss->jenis_losses()->first())->jenis_loss,
                'simbol' => $loss->simbol,
                'jumlah' => Uang::of($loss->jumlah),
            ] : null,
        ];

        if ($lengkap) {
            $data['petugas'] = optional(optional($d->member()->first())->ktp()->first())->nama;
            $data['produk_id'] = optional($produk)->produk_id;
            $data['dombak_id'] = $d->dombak_id;
            $data['jumlah_lampiran'] = $d->file_dokumen_stok()->count();
            $data['tinggi'] = $d->konversi()->get()
                ->map(fn ($k) => ['tinggi' => Uang::of($k->tinggi), 'volume' => Uang::of($k->volume)])
                ->values()->all();
        }

        return $data;
    }
}
