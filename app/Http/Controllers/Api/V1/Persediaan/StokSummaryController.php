<?php

namespace App\Http\Controllers\Api\V1\Persediaan;

use App\Http\Controllers\Controller;
use App\Models\DokumenStok;
use App\Models\Produk;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use App\Support\Uang;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Stok Summary: seluruh pergerakan stok pada satu tanggal, dikelompokkan per
 * produk dan diurutkan sesuai urutan kejadian di lapangan.
 *
 * Bentuk laporannya mengikuti StokSummaryController web, termasuk urutan
 * jenis pergerakan dan baris turunan (losses, stand meter untuk penjualan).
 */
class StokSummaryController extends Controller
{
    /** Urutan tampil bila beberapa gerakan terjadi pada shift & dombak sama. */
    private const URUTAN = [
        'Stok Taking' => 1,
        'Stok Taking Good Receive Penerimaan' => 2,
        'Good Receive Penerimaan' => 3,
        'Stok Taking Good Receive Penyesuaian' => 4,
        'Good Issue Sales' => 5,
        'Stok Taking Good Transfer' => 6,
        'Good Transfer Send' => 7,
        'Good Transfer Receive' => 8,
        'Stok Taking Good Transfer Penyesuaian' => 9,
        'Good Issue Tera' => 10,
        'Good Return Tera' => 11,
        'Loss' => 12,
        'Commit Change From User' => 13,
    ];

    public function show(Request $request, string $tanggal)
    {
        if (! $this->tanggalValid($tanggal)) {
            return ApiResponse::fail('Format tanggal harus Y-m-d.', 422);
        }

        $spbu = MobileProfile::spbuUtama($request->user()->member()->first());

        $produk = Produk::where('is_aktif', true)
            ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id))
            ->orderBy('nama_produk')
            ->get();

        $items = $produk->map(function (Produk $p) use ($tanggal) {
            $inv = $p->stok_inventory()->first();

            $dokumen = $inv
                ? $inv->dokumen_stok()->where('tanggal', $tanggal)->get()
                : collect();

            $baris = $dokumen
                ->sortBy([
                    fn ($a, $b) => strcmp((string) $a->waktu, (string) $b->waktu),
                    fn ($a, $b) => strcmp(
                        (string) optional($a->dombak()->first())->nama_dombak,
                        (string) optional($b->dombak()->first())->nama_dombak
                    ),
                    fn ($a, $b) => $this->urutan($a) <=> $this->urutan($b),
                ])
                ->map(fn (DokumenStok $d) => $this->serial($d))
                ->values()->all();

            return [
                'produk_id' => $p->produk_id,
                'produk' => $p->nama_produk,
                'stok_sekarang' => Uang::of(optional($inv)->stok ?? 0),
                'jumlah_gerakan' => count($baris),
                'items' => $baris,
            ];
        })->values()->all();

        return ApiResponse::ok([
            'tanggal' => $tanggal,
            'label_tanggal' => Carbon::createFromFormat('Y-m-d', $tanggal)->translatedFormat('d F Y'),
            'produk' => $items,
            'total_gerakan' => collect($items)->sum('jumlah_gerakan'),
        ]);
    }

    private function urutan(DokumenStok $d): int
    {
        $gerak = optional($d->type_dokumen_stok()->first())->type_movement;

        return self::URUTAN[$gerak] ?? 99;
    }

    /**
     * Satu kartu pergerakan: stok awal → baris turunan sesuai jenis → stok
     * akhir. Semua angka string 6 desimal.
     */
    private function serial(DokumenStok $d): array
    {
        $gerak = optional($d->type_dokumen_stok()->first())->type_movement;
        $selisih = Uang::kurang($d->stok_akhir, $d->stok_awal);
        $loss = $d->losses()->first();

        $rows = [['label' => 'Stok Awal', 'value' => Uang::of($d->stok_awal)]];

        if ($gerak === 'Stok Taking' && $loss) {
            $rows[] = [
                'label' => 'Losses / Profit',
                'value' => ($loss->simbol === 'kurang' ? '-' : '+') . ' ' . Uang::of($loss->jumlah),
            ];
        }

        $label = match ($gerak) {
            'Good Transfer Send' => 'Stok Dipindah',
            'Good Transfer Receive' => 'Stok Terpindah',
            'Good Receive Penerimaan' => 'Stok Terisi',
            'Stok Taking Good Receive Penerimaan',
            'Stok Taking Good Transfer' => 'Penjualan / Losses',
            'Good Issue Tera' => 'Dikeluarkan untuk Tera',
            'Good Return Tera' => 'Kembali dari Tera',
            'Commit Change From User' => 'Koreksi Manual',
            default => null,
        };

        if ($label) {
            $rows[] = ['label' => $label, 'value' => $selisih];
        }

        $rows[] = ['label' => 'Stok Akhir', 'value' => Uang::of($d->stok_akhir)];

        if ($gerak === 'Good Issue Sales') {
            $sm = $d->stand_meter()->first();
            $awal = Uang::of(optional($sm)->stand_meter_awal ?? 0);
            $akhir = Uang::of(optional($sm)->stand_meter_akhir ?? 0);
            $rows[] = ['label' => 'Stand Meter Awal', 'value' => $awal . ' L'];
            $rows[] = ['label' => 'Penjualan', 'value' => Uang::kurang($akhir, $awal) . ' L'];
            $rows[] = ['label' => 'Stand Meter Akhir', 'value' => $akhir . ' L'];
        }

        return [
            'id' => $d->dokumen_stok_id,
            'jenis' => $gerak,
            'waktu' => $d->waktu,
            'dombak' => optional($d->dombak()->first())->nama_dombak,
            'jam_kerja' => optional($d->jam_kerja()->first())->nama_jam_kerja,
            'is_confirmation' => filter_var($d->is_confirmation, FILTER_VALIDATE_BOOLEAN),
            'rows' => $rows,
        ];
    }

    private function tanggalValid(string $t): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $t)
            && Carbon::hasFormat($t, 'Y-m-d');
    }
}
