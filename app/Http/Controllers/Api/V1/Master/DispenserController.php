<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Dispenser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class DispenserController extends CrudController
{
    protected function model(): string { return Dispenser::class; }
    protected function label(): string { return 'Dispenser'; }
    protected function kolomCari(): array { return ['nama_dispenser', 'keterangan']; }
    protected function urutan(): array { return ['nama_dispenser', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'payung_id' => ['required', 'exists:payung,payung_id'],
            'nama_dispenser' => ['required', 'string', 'max:16'],
            'keterangan' => ['nullable', 'string'],
            'is_aktif' => ['required', 'boolean'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'payung_id' => $r->input('payung_id'),
            'nama_dispenser' => $r->input('nama_dispenser'),
            'keterangan' => $r->input('keterangan'),
            'is_aktif' => self::bool($r, 'is_aktif'),
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Dispenser $m */
        $payung = $m->payung()->first();

        return [
            'id' => $m->dispenser_id,
            'judul' => $m->nama_dispenser,
            'subjudul' => trim((optional($payung)->nama_payung ? 'Payung ' . $payung->nama_payung . ' · ' : '') . (filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif')),
            'dispenser_id' => $m->dispenser_id,
            'payung_id' => $m->payung_id,
            'nama_dispenser' => $m->nama_dispenser,
            'keterangan' => $m->keterangan,
            'is_aktif' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Dispenser $m */
        return array_merge($this->ringkas($m), [
            'payung' => optional($m->payung()->first())->nama_payung,
            'nozzle' => $m->nozzle()->get()->pluck('nama_nozzle')->values()->all(),
        ]);
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var Dispenser $m */
        return ['nozzle' => $m->nozzle()->count()];
    }
}
