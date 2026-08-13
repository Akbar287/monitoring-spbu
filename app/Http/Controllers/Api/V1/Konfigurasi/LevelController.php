<?php

namespace App\Http\Controllers\Api\V1\Konfigurasi;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Level;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class LevelController extends CrudController
{
    protected function model(): string { return Level::class; }
    protected function label(): string { return 'Level'; }
    protected function kolomCari(): array { return ['nama_level', 'keterangan']; }
    protected function urutan(): array { return ['nama_level', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'divisi_id' => ['required', 'exists:divisi,divisi_id'],
            'nama_level' => ['required', 'string', 'max:64'],
            'keterangan' => ['nullable', 'string'],
            'is_aktif' => ['required', 'boolean'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'divisi_id' => $r->input('divisi_id'),
            'nama_level' => $r->input('nama_level'),
            'keterangan' => $r->input('keterangan'),
            'is_aktif' => self::bool($r, 'is_aktif'),
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Level $m */
        return [
            'id' => $m->level_id,
            'judul' => $m->nama_level,
            'subjudul' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif',
            'level_id' => $m->level_id,
            'divisi_id' => $m->divisi_id,
            'nama_level' => $m->nama_level,
            'keterangan' => $m->keterangan,
            'is_aktif' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Level $m */
        return array_merge($this->ringkas($m), [
            'divisi' => optional($m->divisi()->first())->nama_divisi,
            'jabatan' => $m->jabatan()->get()->pluck('nama_jabatan')->values()->all(),
        ]);
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var Level $m */
        return ['jabatan' => $m->jabatan()->count()];
    }
}
