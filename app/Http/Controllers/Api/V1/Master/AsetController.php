<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Aset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AsetController extends CrudController
{
    protected function model(): string { return Aset::class; }
    protected function label(): string { return 'Aset'; }
    protected function kolomCari(): array { return ['nama_aset', 'keterangan']; }
    protected function urutan(): array { return ['nama_aset', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'spbu_id' => ['required', 'exists:spbu,spbu_id'],
            'nama_aset' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:0'],
            'tanggal_digunakan' => ['required', 'date_format:Y-m-d'],
            'harga' => ['required', 'numeric', 'min:0'],
            'penyusutan_per_hari' => ['required', 'numeric', 'min:0'],
            'is_digunakan' => ['required', 'boolean'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'spbu_id' => $r->input('spbu_id'),
            'nama_aset' => $r->input('nama_aset'),
            'jumlah' => $r->integer('jumlah'),
            'tanggal_digunakan' => $r->input('tanggal_digunakan'),
            'harga' => self::angka($r->input('harga')),
            'penyusutan_per_hari' => self::angka($r->input('penyusutan_per_hari')),
            'is_digunakan' => self::bool($r, 'is_digunakan'),
            'keterangan' => $r->input('keterangan') ?? '',
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Aset $m */
        return [
            'id' => $m->aset_id,
            'judul' => $m->nama_aset,
            'subjudul' => 'Jumlah ' . $m->jumlah . ' · ' . (filter_var($m->is_digunakan, FILTER_VALIDATE_BOOLEAN) ? 'Digunakan' : 'Tidak digunakan'),
            'aset_id' => $m->aset_id,
            'spbu_id' => $m->spbu_id,
            'nama_aset' => $m->nama_aset,
            'jumlah' => (int) $m->jumlah,
            'is_digunakan' => filter_var($m->is_digunakan, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Aset $m */
        return array_merge($this->ringkas($m), [
            'spbu' => optional($m->spbu()->first())->nama_spbu,
            'tanggal_digunakan' => $m->tanggal_digunakan,
            'harga' => (float) $m->harga,
            'penyusutan_per_hari' => (float) $m->penyusutan_per_hari,
            'keterangan' => $m->keterangan,
            'jumlah_lampiran' => $m->file_aset()->count(),
        ]);
    }
}
