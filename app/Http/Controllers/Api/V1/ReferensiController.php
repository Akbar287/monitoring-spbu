<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use Illuminate\Http\Request;

/**
 * Data referensi read-only untuk mobile: stok berjalan dan harga aktif.
 * Cakupannya dibatasi SPBU tempat pegawai ditempatkan.
 */
class ReferensiController extends Controller
{
    public function stok(Request $request)
    {
        $produk = $this->produkTerkait($request);

        $items = $produk->map(function (Produk $p) {
            $inventory = $p->stok_inventory()->first();

            return [
                'produk_id' => $p->produk_id,
                'nama_produk' => $p->nama_produk,
                'singkatan' => $p->singkatan,
                'stok' => (float) ($inventory->stok ?? 0),
                'per_dombak' => $inventory
                    ? $inventory->stok_inventory_dombak()->get()->map(fn ($d) => [
                        'dombak' => optional($d->dombak()->first())->nama_dombak,
                        'stok' => (float) $d->stok,
                    ])->values()->all()
                    : [],
            ];
        })->values()->all();

        return ApiResponse::ok(['items' => $items]);
    }

    public function harga(Request $request)
    {
        $items = $this->produkTerkait($request)->map(function (Produk $p) {
            $harga = $p->harga()->where('is_default', true)->orderBy('tanggal', 'desc')->first()
                ?? $p->harga()->orderBy('tanggal', 'desc')->first();

            return [
                'produk_id' => $p->produk_id,
                'nama_produk' => $p->nama_produk,
                'harga_jual' => $harga ? (float) $harga->harga_jual : null,
                'harga_beli' => $harga ? (float) $harga->harga_beli : null,
                'tanggal' => optional($harga)->tanggal,
            ];
        })->values()->all();

        return ApiResponse::ok(['items' => $items]);
    }

    private function produkTerkait(Request $request): \Illuminate\Support\Collection
    {
        $spbu = MobileProfile::spbuUtama($request->user()->member()->first());

        return Produk::where('is_aktif', true)
            ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id))
            ->orderBy('nama_produk')
            ->get();
    }
}
