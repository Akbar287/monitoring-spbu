<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProdukController extends CrudController
{
    protected function model(): string { return Produk::class; }
    protected function label(): string { return 'Produk'; }
    protected function kolomCari(): array { return ['nama_produk', 'singkatan']; }
    protected function urutan(): array { return ['nama_produk', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'spbu_id' => ['required', 'exists:spbu,spbu_id'],
            'nama_produk' => ['required', 'string', 'max:64'],
            'singkatan' => ['nullable', 'string', 'max:4'],
            'is_aktif' => ['required', 'boolean'],
            // detail_produk selalu satu baris per produk (jenis + oktan)
            'nama_jenis' => ['required', 'string', 'max:255'],
            'oktan' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'spbu_id' => $r->input('spbu_id'),
            'nama_produk' => $r->input('nama_produk'),
            'singkatan' => $r->input('singkatan'),
            'is_aktif' => self::bool($r, 'is_aktif'),
        ];
    }

    /** Produk selalu punya tepat satu detail_produk — dibuat/diperbarui menyusul. */
    protected function setelahSimpan(Request $r, Model $m, bool $baru): void
    {
        /** @var Produk $m */
        $detail = [
            'nama_jenis' => $r->input('nama_jenis'),
            'oktan' => $r->integer('oktan'),
        ];

        $ada = $m->detail_produk()->first();
        $ada ? $ada->update($detail) : $m->detail_produk()->create($detail);
    }

    protected function ringkas(Model $m): array
    {
        /** @var Produk $m */
        return [
            'id' => $m->produk_id,
            'judul' => $m->nama_produk,
            'subjudul' => trim(($m->singkatan ? $m->singkatan . ' · ' : '') . (filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif')),
            'produk_id' => $m->produk_id,
            'spbu_id' => $m->spbu_id,
            'nama_produk' => $m->nama_produk,
            'singkatan' => $m->singkatan,
            'is_aktif' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Produk $m */
        $detail = $m->detail_produk()->first();
        $harga = $m->harga()->where('is_default', true)->first();
        $stok = $m->stok_inventory()->first();

        return array_merge($this->ringkas($m), [
            'spbu' => optional($m->spbu()->first())->nama_spbu,
            'nama_jenis' => optional($detail)->nama_jenis,
            'oktan' => optional($detail)->oktan,
            'harga_jual' => $harga ? (float) $harga->harga_jual : null,
            'harga_beli' => $harga ? (float) $harga->harga_beli : null,
            'stok' => $stok ? (float) $stok->stok : 0,
        ]);
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var Produk $m */
        return [
            'nozzle' => $m->nozzle()->count(),
            'riwayat harga' => $m->harga()->count(),
            'rencana pembelian' => $m->detail_rencana_pembelian()->count(),
            'dokumen LO' => $m->file_lo()->count(),
        ];
    }
}
