<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Fasilitas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FasilitasController extends CrudController
{
    protected function model(): string { return Fasilitas::class; }
    protected function label(): string { return 'Fasilitas'; }
    protected function kolomCari(): array { return ['nama_fasilitas', 'keterangan']; }
    protected function urutan(): array { return ['nama_fasilitas', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'spbu_id' => ['required', 'exists:spbu,spbu_id'],
            'nama_fasilitas' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:0'],
            'tanggal_digunakan' => ['required', 'date_format:Y-m-d'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'spbu_id' => $r->input('spbu_id'),
            'nama_fasilitas' => $r->input('nama_fasilitas'),
            'jumlah' => $r->integer('jumlah'),
            'tanggal_digunakan' => $r->input('tanggal_digunakan'),
            'keterangan' => $r->input('keterangan') ?? '',
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Fasilitas $m */
        return [
            'id' => $m->fasilitas_id,
            'judul' => $m->nama_fasilitas,
            'subjudul' => 'Jumlah ' . $m->jumlah,
            'fasilitas_id' => $m->fasilitas_id,
            'spbu_id' => $m->spbu_id,
            'nama_fasilitas' => $m->nama_fasilitas,
            'jumlah' => (int) $m->jumlah,
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Fasilitas $m */
        return array_merge($this->ringkas($m), [
            'spbu' => optional($m->spbu()->first())->nama_spbu,
            'tanggal_digunakan' => $m->tanggal_digunakan,
            'keterangan' => $m->keterangan,
            'jumlah_lampiran' => $m->file_fasilitas()->count(),
        ]);
    }
}
