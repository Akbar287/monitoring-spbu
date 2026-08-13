<?php

namespace App\Http\Controllers\Api\V1\Konfigurasi;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\PajakPembelianLib;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Library tarif pajak pembelian BBM (PPN, PPBKB, PPh 22).
 *
 * Alur pembelian mengambil tarif dengan
 * `PajakPembelianLib::where('is_aktif', 1)->first()` di beberapa tempat
 * (DetailPembelianController, PembelianController, PembayaranController),
 * jadi hanya boleh ada SATU baris aktif — dijaga di setelahSimpan().
 */
class PajakController extends CrudController
{
    protected function model(): string { return PajakPembelianLib::class; }
    protected function label(): string { return 'Tarif Pajak'; }
    protected function urutan(): array { return ['created_at', 'desc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'ppn' => ['required', 'numeric', 'min:0'],
            'ppbkb' => ['required', 'numeric', 'min:0'],
            'pph' => ['required', 'numeric', 'min:0'],
            'is_aktif' => ['required', 'boolean'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'ppn' => self::angka($r->input('ppn')),
            'ppbkb' => self::angka($r->input('ppbkb')),
            'pph' => self::angka($r->input('pph')),
            'is_aktif' => self::bool($r, 'is_aktif'),
        ];
    }

    /** Tarif yang baru diaktifkan menonaktifkan tarif aktif lainnya. */
    protected function setelahSimpan(Request $r, Model $m, bool $baru): void
    {
        /** @var PajakPembelianLib $m */
        if (! filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        PajakPembelianLib::where('pajak_pembelian_lib_id', '!=', $m->pajak_pembelian_lib_id)
            ->where('is_aktif', true)
            ->update(['is_aktif' => false]);
    }

    protected function ringkas(Model $m): array
    {
        /** @var PajakPembelianLib $m */
        $aktif = filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN);

        return [
            'id' => $m->pajak_pembelian_lib_id,
            'judul' => 'PPN ' . self::persen($m->ppn) . ' · PPBKB ' . self::persen($m->ppbkb) . ' · PPh ' . self::persen($m->pph),
            'subjudul' => $aktif ? 'Tarif berlaku' : 'Tidak aktif',
            'pajak_pembelian_lib_id' => $m->pajak_pembelian_lib_id,
            'ppn' => (float) $m->ppn,
            'ppbkb' => (float) $m->ppbkb,
            'pph' => (float) $m->pph,
            'is_aktif' => $aktif,
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var PajakPembelianLib $m */
        return array_merge($this->ringkas($m), [
            'dipakai_pembelian' => $m->pajak_pembelian()->count(),
        ]);
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var PajakPembelianLib $m */
        return ['faktur pembelian' => $m->pajak_pembelian()->count()];
    }

    private static function persen(mixed $nilai): string
    {
        return rtrim(rtrim(number_format((float) $nilai, 4, '.', ''), '0'), '.');
    }
}
