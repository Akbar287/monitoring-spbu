<?php

namespace App\Http\Controllers\Api\V1\Konfigurasi;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Hari;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HariController extends CrudController
{
    protected function model(): string { return Hari::class; }
    protected function label(): string { return 'Hari'; }
    protected function kolomCari(): array { return ['nama_hari', 'keterangan']; }
    // tabel hari tanpa timestamps; diurutkan berdasarkan nama
    protected function urutan(): array { return ['nama_hari', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'nama_hari' => ['required', 'string', 'max:32'],
            'hari_kerja' => ['required', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'nama_hari' => $r->input('nama_hari'),
            'hari_kerja' => self::bool($r, 'hari_kerja'),
            'keterangan' => $r->input('keterangan'),
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Hari $m */
        return [
            'id' => $m->hari_id,
            'judul' => $m->nama_hari,
            'subjudul' => filter_var($m->hari_kerja, FILTER_VALIDATE_BOOLEAN) ? 'Hari kerja' : 'Libur',
            'hari_id' => $m->hari_id,
            'nama_hari' => $m->nama_hari,
            'hari_kerja' => filter_var($m->hari_kerja, FILTER_VALIDATE_BOOLEAN),
            'keterangan' => $m->keterangan,
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Hari $m */
        return array_merge($this->ringkas($m), [
            'jam_kerja' => $m->jam_kerja()->get()->pluck('nama_jam_kerja')->values()->all(),
        ]);
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var Hari $m */
        return ['kaitan jam kerja' => $m->jam_kerja()->count()];
    }
}
