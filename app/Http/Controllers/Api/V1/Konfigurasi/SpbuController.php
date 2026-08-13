<?php

namespace App\Http\Controllers\Api\V1\Konfigurasi;

use App\Http\Controllers\Api\V1\CrudController;
use App\Models\Spbu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SpbuController extends CrudController
{
    protected function model(): string { return Spbu::class; }
    protected function label(): string { return 'SPBU'; }
    protected function kolomCari(): array { return ['nama_spbu', 'nomor_spbu', 'alamat']; }
    protected function urutan(): array { return ['nama_spbu', 'asc']; }

    protected function aturan(Request $r, ?Model $m): array
    {
        return [
            'nama_spbu' => ['required', 'string', 'max:255'],
            'nomor_spbu' => ['required', 'string', 'max:32'],
            'tanggal_pendirian' => ['required', 'date_format:Y-m-d'],
            'luas_lahan' => ['required', 'numeric', 'min:0'],
            'satuan_luas' => ['required', 'string', 'max:8'],
            'alamat' => ['required', 'string'],
        ];
    }

    protected function isi(Request $r): array
    {
        return [
            'nama_spbu' => $r->input('nama_spbu'),
            'nomor_spbu' => $r->input('nomor_spbu'),
            'tanggal_pendirian' => $r->input('tanggal_pendirian'),
            'luas_lahan' => self::angka($r->input('luas_lahan')),
            'satuan_luas' => $r->input('satuan_luas'),
            'alamat' => $r->input('alamat'),
        ];
    }

    protected function ringkas(Model $m): array
    {
        /** @var Spbu $m */
        return [
            'id' => $m->spbu_id,
            'judul' => $m->nama_spbu,
            'subjudul' => 'No. ' . $m->nomor_spbu,
            'spbu_id' => $m->spbu_id,
            'nama_spbu' => $m->nama_spbu,
            'nomor_spbu' => $m->nomor_spbu,
        ];
    }

    protected function lengkap(Model $m): array
    {
        /** @var Spbu $m */
        return array_merge($this->ringkas($m), [
            'tanggal_pendirian' => $m->tanggal_pendirian,
            'luas_lahan' => (float) $m->luas_lahan,
            'satuan_luas' => $m->satuan_luas,
            'alamat' => $m->alamat,
            'jumlah_divisi' => $m->divisi()->count(),
            'jumlah_produk' => $m->produk()->count(),
            'jumlah_dombak' => $m->dombak()->count(),
            'jumlah_jam_kerja' => $m->jam_kerja()->count(),
        ]);
    }

    /**
     * SPBU adalah akar hampir seluruh data operasional; menghapusnya akan
     * merontokkan produk, dombak, divisi, penjualan, sampai jurnal keuangan
     * lewat cascade. Karena itu dijaga sangat ketat.
     */
    protected function penghalangHapus(Model $m): array
    {
        /** @var Spbu $m */
        return [
            'divisi' => $m->divisi()->count(),
            'produk' => $m->produk()->count(),
            'dombak' => $m->dombak()->count(),
            'payung' => $m->payung()->count(),
            'jam kerja' => $m->jam_kerja()->count(),
            'aset' => $m->aset()->count(),
            'fasilitas' => $m->fasilitas()->count(),
            'transaksi penjualan' => $m->penjualan()->count(),
            'rencana pembelian' => $m->rencana_pembelian()->count(),
            'petty cash' => $m->petty_cash()->count(),
        ];
    }
}
