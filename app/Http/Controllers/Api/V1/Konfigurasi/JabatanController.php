<?php

namespace App\Http\Controllers\Api\V1\Konfigurasi;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Jabatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class JabatanController extends CrudController
{
    protected function model(): string { return Jabatan::class; }
    protected function label(): string { return 'Jabatan'; }
    protected function kolomCari(): array { return ['nama_jabatan', 'keterangan']; }
    protected function urutan(): array { return ['nama_jabatan', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'level_id' => ['required', 'exists:level,level_id'],
            'nama_jabatan' => ['required', 'string', 'max:64'],
            'keterangan' => ['nullable', 'string'],
            'is_aktif' => ['required', 'boolean'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'level_id' => $r->input('level_id'),
            'nama_jabatan' => $r->input('nama_jabatan'),
            'keterangan' => $r->input('keterangan'),
            'is_aktif' => self::bool($r, 'is_aktif'),
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Jabatan $m */
        $level = $m->level()->first();

        return [
            'id' => $m->jabatan_id,
            'judul' => $m->nama_jabatan,
            'subjudul' => trim((optional($level)->nama_level ?? '-') . ' · ' . (filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Nonaktif')),
            'jabatan_id' => $m->jabatan_id,
            'level_id' => $m->level_id,
            'nama_jabatan' => $m->nama_jabatan,
            'keterangan' => $m->keterangan,
            'is_aktif' => filter_var($m->is_aktif, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Jabatan $m */
        $level = $m->level()->first();

        return array_merge($this->ringkas($m), [
            'level' => optional($level)->nama_level,
            'divisi' => optional(optional($level)->divisi()->first())->nama_divisi,
            'jumlah_pegawai' => $m->member()->count(),
        ]);
    }

    /**
     * Jabatan menentukan level akses pegawai; menghapusnya akan mencabut
     * jabatan seluruh pegawai terkait lewat cascade pivot jabatan_member.
     */
    protected function penghalangHapus(Model $m): array
    {
        /** @var Jabatan $m */
        return [
            'pegawai' => $m->member()->count(),
            'penjadwalan' => $m->penjadwalan()->count(),
            'aturan gaji' => $m->gaji()->count(),
        ];
    }
}
