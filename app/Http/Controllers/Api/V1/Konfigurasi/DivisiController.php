<?php

namespace App\Http\Controllers\Api\V1\Konfigurasi;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Divisi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DivisiController extends CrudController
{
    protected function model(): string { return Divisi::class; }
    protected function label(): string { return 'Divisi'; }
    protected function kolomCari(): array { return ['nama_divisi', 'keterangan']; }
    protected function urutan(): array { return ['nama_divisi', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'spbu_id' => ['required', 'exists:spbu,spbu_id'],
            'nama_divisi' => ['required', 'string', 'max:64'],
            'keterangan' => ['nullable', 'string'],
            'is_aktif' => ['required', 'boolean'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'spbu_id' => $r->input('spbu_id'),
            'nama_divisi' => $r->input('nama_divisi'),
            'keterangan' => $r->input('keterangan'),
            'is_aktif' => self::bool($r, 'is_aktif'),
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Divisi $m */
        return [
            'id' => $m->divisi_id,
            'judul' => $m->nama_divisi,
            'subjudul' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif',
            'divisi_id' => $m->divisi_id,
            'spbu_id' => $m->spbu_id,
            'nama_divisi' => $m->nama_divisi,
            'keterangan' => $m->keterangan,
            'is_aktif' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Divisi $m */
        return array_merge($this->ringkas($m), [
            'spbu' => optional($m->spbu()->first())->nama_spbu,
            'level' => $m->level()->get()->pluck('nama_level')->values()->all(),
        ]);
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var Divisi $m */
        return ['level' => $m->level()->count()];
    }
}
