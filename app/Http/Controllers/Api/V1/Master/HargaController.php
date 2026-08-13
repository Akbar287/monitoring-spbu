<?php

namespace App\Http\Controllers\Api\V1\Master;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Harga;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HargaController extends CrudController
{
    protected function model(): string { return Harga::class; }
    protected function label(): string { return 'Harga'; }
    protected function urutan(): array { return ['tanggal', 'desc']; }

    /** Default: hanya harga yang sedang berlaku; `?semua=1` untuk riwayat. */
    protected function query(): Builder
    {
        $query = Harga::query();

        if (! request()->boolean('semua')) {
            $query->where('is_default', true);
        }

        if ($produk = request('produk_id')) {
            $query->where('produk_id', $produk);
        }

        return $query;
    }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'produk_id' => ['required', 'exists:produk,produk_id'],
            'jam_kerja_id' => ['required', 'exists:jam_kerja,jam_kerja_id'],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'harga_jual' => ['required', 'numeric', 'min:0'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'produk_id' => $r->input('produk_id'),
            'jam_kerja_id' => $r->input('jam_kerja_id'),
            'tanggal' => $r->input('tanggal'),
            'harga_jual' => self::angka($r->input('harga_jual')),
            'harga_beli' => self::angka($r->input('harga_beli')),
            'is_default' => 1,
        ];
    }

    /**
     * Harga baru selalu menjadi yang berlaku; harga default lama untuk produk
     * yang sama diturunkan menjadi riwayat. Mengikuti HargaController web.
     *
     * Penting: harga lama TIDAK dihapus karena `detail_penjualan.harga_id`
     * merujuk padanya dan menjadi dasar HPP perpetual (lihat docs/keuangan.md).
     */
    protected function setelahSimpan(Request $r, Model $m, bool $baru): void
    {
        /** @var Harga $m */
        Harga::where('produk_id', $m->produk_id)
            ->where('harga_id', '!=', $m->harga_id)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }

    protected function ringkas(Model $m): array
    {
        /** @var Harga $m */
        $produk = $m->produk()->first();

        return [
            'id' => $m->harga_id,
            'judul' => optional($produk)->nama_produk ?? '-',
            'subjudul' => 'Jual ' . number_format((float) $m->harga_jual, 0, ',', '.') . ' · ' . $m->tanggal,
            'harga_id' => $m->harga_id,
            'produk_id' => $m->produk_id,
            'jam_kerja_id' => $m->jam_kerja_id,
            'tanggal' => $m->tanggal,
            'harga_jual' => (float) $m->harga_jual,
            'harga_beli' => (float) $m->harga_beli,
            'is_default' => filter_var($m->is_default, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Harga $m */
        return array_merge($this->ringkas($m), [
            'produk' => optional($m->produk()->first())->nama_produk,
            'jam_kerja' => optional($m->jam_kerja()->first())->nama_jam_kerja,
            'margin' => (float) $m->harga_jual - (float) $m->harga_beli,
        ]);
    }

    protected function penghalangHapus(Model $m): array
    {
        /** @var Harga $m */
        return ['transaksi penjualan' => $m->detail_penjualan()->count()];
    }
}
