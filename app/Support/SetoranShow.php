<?php

namespace App\Support;

use App\Models\Penjualan;

/**
 * Serialisasi props bersama utk halaman Setoran/Show (penitipan/closing/bank).
 * Bentuk nilai mengikuti blade lama (number_format 6 desimal, label nozzle
 * "nama (produk)", PJ digrup per jabatan dengan daftar nama).
 */
class SetoranShow
{
    public static function info(Penjualan $penjualan): array
    {
        $spbu = $penjualan->spbu()->first();

        return [
            ['label' => 'SPBU', 'value' => $spbu ? $spbu->nama_spbu . ' (' . $spbu->nomor_spbu . ')' : '-'],
            ['label' => 'Tanggal', 'value' => $penjualan->tanggal, 'type' => 'date'],
            ['label' => 'Jam Kerja', 'value' => optional($penjualan->jam_kerja()->first())->nama_jam_kerja],
            ['label' => 'Total Uang Kotak', 'value' => number_format($penjualan->total_uang_kotak, 6, '.', ','), 'prefix' => 'Rp'],
            ['label' => 'Total Penjualan', 'value' => number_format($penjualan->total_penjualan, 6, '.', ','), 'prefix' => 'Rp'],
        ];
    }

    public static function pj(Penjualan $penjualan): array
    {
        return $penjualan->pj_operasional_setoran()
            ->distinct('pj_operasional_setoran.jabatan_id')
            ->join('jabatan', 'pj_operasional_setoran.jabatan_id', 'jabatan.jabatan_id')
            ->orderBy('nama_jabatan', 'asc')
            ->get()
            ->map(function ($pjRow) use ($penjualan) {
                $namas = $penjualan->pj_operasional_setoran()
                    ->where('pj_operasional_setoran.jabatan_id', $pjRow->jabatan_id)
                    ->get()
                    ->map(fn ($m) => optional(optional($m->member()->first())->ktp()->first())->nama)
                    ->filter()->values()->all();

                return [
                    'jabatan' => optional($pjRow->jabatan()->first())->nama_jabatan,
                    'namas' => $namas,
                ];
            })->values()->all();
    }

    /**
     * @return array<int, array{detail_penjualan_id:mixed,is_confirmed:bool,nozzle:string,awal:string,akhir:string,selisih:string,harga:?string,total:?string}>
     */
    public static function standMeters(Penjualan $penjualan): array
    {
        return $penjualan->detail_penjualan()->get()->map(function ($dp) {
            $sm = $dp->stand_meter()->first();
            $nozzle = $sm ? $sm->nozzle()->first() : null;
            $produk = $nozzle ? $nozzle->produk()->first() : null;
            $harga = $produk ? $produk->harga()->where('is_default', true)->first() : null;
            $selisih = $sm ? $sm->stand_meter_akhir - $sm->stand_meter_awal : 0;

            return [
                'detail_penjualan_id' => $dp->detail_penjualan_id,
                'is_confirmed' => (bool) $dp->is_confirmed,
                'nozzle' => $nozzle ? $nozzle->nama_nozzle . ' (' . optional($produk)->nama_produk . ')' : '-',
                'awal' => $sm ? number_format($sm->stand_meter_awal, 6, '.', ',') : '-',
                'akhir' => $sm ? number_format($sm->stand_meter_akhir, 6, '.', ',') : '-',
                'selisih' => number_format($selisih, 6, '.', ','),
                'harga' => $harga ? number_format($harga->harga_jual, 6, '.', ',') : null,
                'total' => $harga ? number_format($selisih * $harga->harga_jual, 6, '.', ',') : null,
            ];
        })->values()->all();
    }

    public static function levelInfo(): array
    {
        $jabatan = optional(auth()->user()->member()->first())->jabatan()->first();
        $level = $jabatan ? $jabatan->level()->first() : null;

        return [
            'level' => optional($level)->nama_level,
            'divisi' => $level ? optional($level->divisi()->first())->nama_divisi : null,
            'jabatan' => optional($jabatan)->nama_jabatan,
        ];
    }
}
