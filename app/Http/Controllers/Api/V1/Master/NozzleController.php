<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Nozzle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class NozzleController extends CrudController
{
    protected function model(): string { return Nozzle::class; }
    protected function label(): string { return 'Nozzle'; }
    protected function kolomCari(): array { return ['nama_nozzle', 'keterangan']; }
    protected function urutan(): array { return ['nama_nozzle', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'dispenser_id' => ['required', 'exists:dispenser,dispenser_id'],
            'produk_id' => ['required', 'exists:produk,produk_id'],
            'nama_nozzle' => ['required', 'string', 'max:16'],
            'keterangan' => ['nullable', 'string'],
            'is_aktif' => ['required', 'boolean'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'dispenser_id' => $r->input('dispenser_id'),
            'produk_id' => $r->input('produk_id'),
            'nama_nozzle' => $r->input('nama_nozzle'),
            'keterangan' => $r->input('keterangan'),
            'is_aktif' => self::bool($r, 'is_aktif'),
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Nozzle $m */
        $produk = $m->produk()->first();

        return [
            'id' => $m->nozzle_id,
            'judul' => $m->nama_nozzle,
            'subjudul' => trim((optional($produk)->nama_produk ?? '-') . ' · ' . (filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif')),
            'nozzle_id' => $m->nozzle_id,
            'dispenser_id' => $m->dispenser_id,
            'produk_id' => $m->produk_id,
            'nama_nozzle' => $m->nama_nozzle,
            'keterangan' => $m->keterangan,
            'is_aktif' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Nozzle $m */
        $dispenser = $m->dispenser()->first();

        return array_merge($this->ringkas($m), [
            'produk' => optional($m->produk()->first())->nama_produk,
            'dispenser' => optional($dispenser)->nama_dispenser,
            'payung' => optional(optional($dispenser)->payung()->first())->nama_payung,
        ]);
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var Nozzle $m */
        return [
            'data totalisator' => $m->stand_meter()->count(),
            'penjadwalan' => $m->penjadwalan()->count(),
        ];
    }
}
