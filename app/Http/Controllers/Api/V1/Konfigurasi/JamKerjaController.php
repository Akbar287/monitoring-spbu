<?php

namespace App\Http\Controllers\Api\V1\Konfigurasi;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\JamKerja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Shift kerja. Sepuluh kolom waktunya menjadi acuan absensi (batas telat),
 * penjualan per shift, harga per shift, dan seluruh dokumen stok.
 */
class JamKerjaController extends CrudController
{
    /** Kolom bertipe `time` di tabel jam_kerja. */
    private const KOLOM_WAKTU = [
        'batas_awal_in',
        'batas_akhir_in',
        'jam_datang_in',
        'jam_pulang_out',
        'akhir_out',
        'jam_istirahat',
        'masuk_istirahat',
        'batas_telat',
    ];

    protected function model(): string { return JamKerja::class; }
    protected function label(): string { return 'Jam Kerja'; }
    protected function kolomCari(): array { return ['nama_jam_kerja']; }
    protected function urutan(): array { return ['urutan', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        $waktu = [];
        foreach (self::KOLOM_WAKTU as $kolom) {
            // terima HH:MM maupun HH:MM:SS
            $waktu[$kolom] = ['required', 'date_format:H:i,H:i:s'];
        }

        return array_merge($waktu, [
            'spbu_id' => ['required', 'exists:spbu,spbu_id'],
            'nama_jam_kerja' => ['required', 'string', 'max:32'],
            'urutan' => ['required', 'integer', 'min:0'],
            'hari_ids' => ['nullable', 'array'],
            'hari_ids.*' => ['exists:hari,hari_id'],
        ]);
    }

    protected function isi(Request $r): array
    {
        $data = [
            'spbu_id' => $r->input('spbu_id'),
            'nama_jam_kerja' => $r->input('nama_jam_kerja'),
            'urutan' => $r->integer('urutan'),
        ];

        foreach (self::KOLOM_WAKTU as $kolom) {
            $data[$kolom] = self::jam($r->input($kolom));
        }

        return $data;
    }

    /**
     * Pivot hari_jam_kerja hanya disentuh bila klien mengirim `hari_ids`,
     * supaya pembaruan sebagian tidak mengosongkannya.
     */
    protected function setelahSimpan(Request $r, Model $m, bool $baru): void
    {
        /** @var JamKerja $m */
        if ($r->has('hari_ids')) {
            $m->hari()->sync($r->input('hari_ids') ?? []);
        }
    }

    protected function ringkas(Model $m): array
    {
        /** @var JamKerja $m */
        return [
            'id' => $m->jam_kerja_id,
            'judul' => $m->nama_jam_kerja,
            'subjudul' => self::jam($m->jam_datang_in) . ' – ' . self::jam($m->jam_pulang_out) . ' · urutan ' . $m->urutan,
            'jam_kerja_id' => $m->jam_kerja_id,
            'spbu_id' => $m->spbu_id,
            'nama_jam_kerja' => $m->nama_jam_kerja,
            'urutan' => (int) $m->urutan,
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var JamKerja $m */
        $data = array_merge($this->ringkas($m), [
            'spbu' => optional($m->spbu()->first())->nama_spbu,
            'hari' => $m->hari()->get()->pluck('nama_hari')->values()->all(),
            'hari_ids' => $m->hari()->get()->pluck('hari_id')->values()->all(),
        ]);

        foreach (self::KOLOM_WAKTU as $kolom) {
            $data[$kolom] = self::jam($m->{$kolom});
        }

        return $data;
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var JamKerja $m */
        return [
            'transaksi penjualan' => $m->penjualan()->count(),
            'dokumen stok' => $m->dokumen_stok()->count(),
            'data totalisator' => $m->stand_meter()->count(),
            'riwayat harga' => $m->harga()->count(),
            'penjadwalan' => $m->penjadwalan()->count(),
        ];
    }

    /** Normalkan ke HH:MM agar tampilan klien seragam. */
    private static function jam(mixed $nilai): ?string
    {
        if (empty($nilai)) {
            return null;
        }

        return substr((string) $nilai, 0, 5);
    }
}
