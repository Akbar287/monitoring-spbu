<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Dispenser;
use App\Models\Divisi;
use App\Models\Dombak;
use App\Models\Hari;
use App\Models\Jabatan;
use App\Models\JamKerja;
use App\Models\Level;
use App\Models\Payung;
use App\Models\Produk;
use App\Models\Spbu;
use App\Models\StatusMember;
use App\Support\ApiResponse;

/**
 * Semua daftar pilihan (dropdown) untuk form Master Data dan Konfigurasi
 * dalam satu permintaan, supaya klien mobile cukup memuatnya sekali lalu
 * meng-cache-nya selama sesi.
 */
class OpsiController extends Controller
{
    public function __invoke()
    {
        return ApiResponse::ok([
            'spbu' => Spbu::orderBy('nama_spbu')->get()
                ->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu . ' (' . $s->nomor_spbu . ')'])
                ->values()->all(),

            'produk' => Produk::orderBy('nama_produk')->get()
                ->map(fn ($p) => ['value' => $p->produk_id, 'label' => $p->nama_produk])
                ->values()->all(),

            'dombak' => Dombak::orderBy('nama_dombak')->get()
                ->map(fn ($d) => ['value' => $d->dombak_id, 'label' => $d->nama_dombak])
                ->values()->all(),

            'payung' => Payung::orderBy('nama_payung')->get()
                ->map(fn ($p) => ['value' => $p->payung_id, 'label' => $p->nama_payung])
                ->values()->all(),

            'dispenser' => Dispenser::orderBy('nama_dispenser')->get()
                ->map(fn ($d) => [
                    'value' => $d->dispenser_id,
                    'label' => $d->nama_dispenser . (optional($d->payung()->first())->nama_payung ? ' — ' . $d->payung()->first()->nama_payung : ''),
                ])->values()->all(),

            'jam_kerja' => JamKerja::orderBy('urutan')->get()
                ->map(fn ($j) => ['value' => $j->jam_kerja_id, 'label' => $j->nama_jam_kerja])
                ->values()->all(),

            // --- entitas modul Konfigurasi -------------------------------
            'divisi' => Divisi::orderBy('nama_divisi')->get()
                ->map(fn ($d) => [
                    'value' => $d->divisi_id,
                    'label' => $d->nama_divisi . (optional($d->spbu()->first())->nama_spbu ? ' — ' . $d->spbu()->first()->nama_spbu : ''),
                ])->values()->all(),

            'level' => Level::orderBy('nama_level')->get()
                ->map(fn ($l) => [
                    'value' => $l->level_id,
                    'label' => $l->nama_level . (optional($l->divisi()->first())->nama_divisi ? ' — ' . $l->divisi()->first()->nama_divisi : ''),
                ])->values()->all(),

            'jabatan' => Jabatan::orderBy('nama_jabatan')->get()
                ->map(fn ($j) => [
                    'value' => $j->jabatan_id,
                    'label' => $j->nama_jabatan . (optional($j->level()->first())->nama_level ? ' — ' . $j->level()->first()->nama_level : ''),
                ])->values()->all(),

            'hari' => Hari::get()
                ->map(fn ($h) => ['value' => $h->hari_id, 'label' => $h->nama_hari])
                ->values()->all(),

            'status_member' => StatusMember::orderBy('nama_status')->get()
                ->map(fn ($s) => ['value' => $s->status_member_id, 'label' => $s->nama_status])
                ->values()->all(),

            // --- pilihan statis ------------------------------------------
            'jenis_kelamin' => [
                ['value' => 'pria', 'label' => 'Pria'],
                ['value' => 'wanita', 'label' => 'Wanita'],
            ],
            'satuan_luas' => [
                ['value' => 'm2', 'label' => 'm² (meter persegi)'],
                ['value' => 'ha', 'label' => 'ha (hektar)'],
            ],
        ]);
    }
}
