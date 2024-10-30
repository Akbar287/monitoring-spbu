<?php

namespace App\Http\Controllers;

use App\Models\DokumenStok;
use App\Models\Produk;
use Illuminate\Http\Request;

class StokSummaryController extends Controller
{
    public function index()
    {
        return view('stok.stok_summary.index');
    }

    public function store(Request $request)
    {
        //
    }

    public function show($date)
    {
        $produk = Produk::where('is_aktif', 1)->get()->toArray();

        $temp = [];
        foreach ($produk as $p) {
            $temp[] = DokumenStok::with(['type_dokumen_stok', 'dombak', 'losses.jenis_losses'])
                ->select('dokumen_stok.tanggal')->distinct()
                ->addSelect('dokumen_stok.dokumen_stok_id')
                ->addSelect('tds.type_movement')
                ->addSelect('dokumen_stok.stok_awal')
                ->addSelect('dokumen_stok.stok_akhir')
                ->addSelect('dokumen_stok.is_confirmation')
                ->addSelect('dokumen_stok.waktu')
                ->addSelect('l.simbol')
                ->addSelect('l.jumlah')
                ->addSelect('jl.jenis_loss')
                ->addSelect('d.nama_dombak')
                ->addSelect('p.nama_produk')
                ->addSelect('jk.nama_jam_kerja')
                ->addSelect('jk.urutan')
                ->addSelect('sm.stand_meter_awal')
                ->addSelect('sm.stand_meter_akhir')
                ->join('monitoring_spbu.type_dokumen_stok as tds', 'dokumen_stok.type_dokumen_stok_id', '=', 'tds.type_dokumen_stok_id')
                ->join('monitoring_spbu.dombak as d', 'dokumen_stok.dombak_id', '=', 'd.dombak_id')
                ->join('stok_inventory as si', 'si.stok_inventory_id', '=', 'dokumen_stok.stok_inventory_id')
                ->join('produk as p', 'si.produk_id', '=', 'p.produk_id')
                ->join('jam_kerja as jk', 'jk.jam_kerja_id', '=', 'dokumen_stok.jam_kerja_id')
                ->leftJoin('monitoring_spbu.losses as l', 'dokumen_stok.dokumen_stok_id', '=', 'l.dokumen_stok_id')
                ->leftJoin('monitoring_spbu.jenis_losses as jl', 'l.jenis_losses_id', '=', 'jl.jenis_losses_id')
                ->leftJoin('monitoring_spbu.stand_meter_dokumen_stok as smds', 'smds.dokumen_stok_id', '=', 'dokumen_stok.dokumen_stok_id')
                ->leftJoin('monitoring_spbu.stand_meter as sm', 'sm.stand_meter_id', '=', 'smds.stand_meter_id')
                ->where('dokumen_stok.tanggal', $date)
                ->where('si.produk_id', $p['produk_id'])
                ->orderBy('dokumen_stok.waktu', 'asc')
                ->orderBy('d.nama_dombak', 'asc')
                ->orderBy('jk.urutan', 'asc')
                ->orderByRaw("CASE
                    WHEN tds.type_movement = 'Stok Taking' THEN 1
                    WHEN tds.type_movement = 'Stok Taking Good Receive Penerimaan' THEN 2
                    WHEN tds.type_movement = 'Good Receive Penerimaan' THEN 3
                    WHEN tds.type_movement = 'Good Issue Sales' THEN 4
                    WHEN tds.type_movement = 'Stok Taking Good Transfer' THEN 5
                    WHEN tds.type_movement = 'Good Transfer Send' THEN 6
                    WHEN tds.type_movement = 'Good Transfer Receive' THEN 7
                    WHEN tds.type_movement = 'Good Issue Tera' THEN 8
                    WHEN tds.type_movement = 'Good Return Tera' THEN 9
                    WHEN tds.type_movement = 'Loss' THEN 10
                    WHEN tds.type_movement = 'Commit Change From User' THEN 11
                END")->get();
        }

        return view('stok.stok_summary.show', compact('date', 'produk', 'temp'));
    }

    public function update(Request $request, $date)
    {
        //
    }

    public function api_call(Request $request) {}
}
