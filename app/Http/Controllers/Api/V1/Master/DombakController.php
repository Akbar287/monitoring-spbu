<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Dombak;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DombakController extends CrudController
{
    protected function model(): string { return Dombak::class; }
    protected function label(): string { return 'Dombak'; }
    protected function kolomCari(): array { return ['nama_dombak', 'keterangan']; }
    protected function urutan(): array { return ['nama_dombak', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'spbu_id' => ['required', 'exists:spbu,spbu_id'],
            'nama_dombak' => ['required', 'string', 'max:16'],
            'keterangan' => ['nullable', 'string'],
            'is_aktif' => ['required', 'boolean'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'spbu_id' => $r->input('spbu_id'),
            'nama_dombak' => $r->input('nama_dombak'),
            'keterangan' => $r->input('keterangan'),
            'is_aktif' => self::bool($r, 'is_aktif'),
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Dombak $m */
        return [
            'id' => $m->dombak_id,
            'judul' => $m->nama_dombak,
            'subjudul' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif',
            'dombak_id' => $m->dombak_id,
            'spbu_id' => $m->spbu_id,
            'nama_dombak' => $m->nama_dombak,
            'keterangan' => $m->keterangan,
            'is_aktif' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Dombak $m */
        $stok = $m->stok_inventory_dombak()->get()->sum(fn ($s) => (float) $s->stok);

        return array_merge($this->ringkas($m), [
            'spbu' => optional($m->spbu()->first())->nama_spbu,
            'stok' => $stok,
            'jumlah_konversi' => $m->konversi()->count(),
            'payung' => $m->payung()->get()->pluck('nama_payung')->values()->all(),
        ]);
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var Dombak $m */
        return [
            'dokumen stok' => $m->dokumen_stok()->count(),
            'data totalisator' => $m->stand_meter()->count(),
        ];
    }
}
