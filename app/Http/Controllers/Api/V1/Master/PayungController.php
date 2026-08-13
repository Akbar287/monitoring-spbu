<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Payung;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PayungController extends CrudController
{
    protected function model(): string { return Payung::class; }
    protected function label(): string { return 'Payung'; }
    protected function kolomCari(): array { return ['nama_payung', 'keterangan']; }
    protected function urutan(): array { return ['nama_payung', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'spbu_id' => ['required', 'exists:spbu,spbu_id'],
            'nama_payung' => ['required', 'string', 'max:16'],
            'keterangan' => ['nullable', 'string'],
            'is_aktif' => ['required', 'boolean'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'spbu_id' => $r->input('spbu_id'),
            'nama_payung' => $r->input('nama_payung'),
            'keterangan' => $r->input('keterangan'),
            'is_aktif' => self::bool($r, 'is_aktif'),
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Payung $m */
        return [
            'id' => $m->payung_id,
            'judul' => $m->nama_payung,
            'subjudul' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif',
            'payung_id' => $m->payung_id,
            'spbu_id' => $m->spbu_id,
            'nama_payung' => $m->nama_payung,
            'keterangan' => $m->keterangan,
            'is_aktif' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Payung $m */
        return array_merge($this->ringkas($m), [
            'spbu' => optional($m->spbu()->first())->nama_spbu,
            'dispenser' => $m->dispenser()->get()->pluck('nama_dispenser')->values()->all(),
            'dombak' => $m->dombak()->get()->pluck('nama_dombak')->values()->all(),
        ]);
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var Payung $m */
        return ['dispenser' => $m->dispenser()->count()];
    }
}
