<?php

namespace App\Http\Controllers\Api\V1\Persediaan;

use App\Http\Controllers\Controller;
use App\Models\DokumenStok;
use App\Models\Losses;
use App\Models\Produk;
use App\Models\StokInventory;
use App\Support\ApiResponse;
use App\Support\MobileProfile;
use App\Support\Persediaan;
use App\Support\Uang;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Ringkasan modul Manajemen Persediaan: saldo stok, antrean dokumen yang
 * belum dikonfirmasi, dan losses hari ini.
 */
class PersediaanController extends Controller
{
    public function ringkasan(Request $request)
    {
        $spbu = MobileProfile::spbuUtama($request->user()->member()->first());
        $hariIni = Carbon::now()->format('Y-m-d');

        $produk = Produk::where('is_aktif', true)
            ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id))
            ->orderBy('nama_produk')
            ->get();

        $stok = $produk->map(function (Produk $p) {
            $inv = $p->stok_inventory()->first();

            return [
                'produk_id' => $p->produk_id,
                'produk' => $p->nama_produk,
                'stok_inventory_id' => optional($inv)->stok_inventory_id,
                'stok' => Uang::of(optional($inv)->stok ?? 0),
                'dombak' => $inv
                    ? $inv->stok_inventory_dombak()->get()->map(fn ($d) => [
                        'dombak' => optional($d->dombak()->first())->nama_dombak,
                        'stok' => Uang::of($d->stok),
                    ])->values()->all()
                    : [],
            ];
        })->values()->all();

        // antrean dokumen menunggu konfirmasi, dikelompokkan per jenis gerakan
        $antrean = DokumenStok::where('is_confirmation', 0)
            ->get()
            ->groupBy(fn (DokumenStok $d) => optional($d->type_dokumen_stok()->first())->type_movement ?? '-')
            ->map->count();

        $lossHariIni = Losses::where('tanggal', $hariIni)->get();

        return ApiResponse::ok([
            'tanggal' => $hariIni,
            'spbu' => $spbu ? $spbu->nama_spbu : 'Semua SPBU',
            'total_stok' => Uang::jumlah($stok, 'stok'),
            'stok' => $stok,
            'menunggu_konfirmasi' => [
                'stok_taking' => (int) ($antrean[Persediaan::GERAK_STOK_TAKING] ?? 0),
                'penerimaan' => (int) ($antrean[Persediaan::GERAK_TERIMA] ?? 0),
                'transfer' => (int) ($antrean[Persediaan::GERAK_KIRIM] ?? 0),
                'total' => (int) $antrean->sum(),
            ],
            'losses_hari_ini' => [
                'kurang' => Uang::jumlah($lossHariIni->where('simbol', 'kurang'), 'jumlah'),
                'lebih' => Uang::jumlah($lossHariIni->where('simbol', 'lebih'), 'jumlah'),
                'jumlah_kejadian' => $lossHariIni->count(),
            ],
        ]);
    }

    /**
     * Daftar dombak beserta saldo & rentang tinggi konversinya — dipakai
     * seluruh form modul ini (stok taking, penerimaan, transfer).
     */
    public function opsiDombak(Request $request)
    {
        $spbu = MobileProfile::spbuUtama($request->user()->member()->first());

        $items = \App\Models\Dombak::where('is_aktif', true)
            ->when($spbu, fn ($q) => $q->where('spbu_id', $spbu->spbu_id))
            ->orderBy('nama_dombak')
            ->get()
            ->map(function ($d) {
                $inv = Persediaan::inventoryDariDombak($d);

                return [
                    'value' => $d->dombak_id,
                    'label' => $d->nama_dombak,
                    'produk' => optional(optional($inv)->produk()->first())->nama_produk,
                    'stok_inventory_id' => optional($inv)->stok_inventory_id,
                    'stok' => Persediaan::stokDombak($d),
                    'jumlah_konversi' => $d->konversi()->count(),
                    'siap' => ! is_null($inv) && $d->konversi()->count() > 0,
                ];
            })->values()->all();

        return ApiResponse::ok(['items' => $items]);
    }

    /** Cari tinggi pada tabel konversi sebuah dombak (untuk input tinggi). */
    public function cariKonversi(Request $request, string $dombakId)
    {
        $request->validate(['cari' => ['nullable', 'string', 'max:32']]);

        $dombak = \App\Models\Dombak::find($dombakId);
        if (is_null($dombak)) {
            return ApiResponse::fail('Dombak tidak ditemukan.', 404);
        }

        $cari = trim((string) $request->input('cari'));

        $items = $dombak->konversi()
            ->when($cari !== '', fn ($q) => $q->whereRaw('CAST(tinggi AS TEXT) LIKE ?', [$cari . '%']))
            ->orderBy('tinggi')
            ->limit(60)
            ->get()
            ->map(fn ($k) => [
                'value' => Uang::of($k->tinggi),
                'label' => Uang::of($k->tinggi) . ' → ' . Uang::of($k->volume) . ' L',
                'tinggi' => Uang::of($k->tinggi),
                'volume' => Uang::of($k->volume),
            ])->values()->all();

        return ApiResponse::ok([
            'dombak' => $dombak->nama_dombak,
            'stok_sekarang' => Persediaan::stokDombak($dombak),
            'items' => $items,
        ]);
    }
}
