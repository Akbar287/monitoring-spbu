<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Spbu;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MarginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $spbu = Spbu::get();
        $produk = Produk::where('is_aktif', true)->get();
        $typeReport = [['type_report_id' => 1, 'nama_type_report' => 'Totalisator'], ['type_report_id' => 2, 'nama_type_report' => 'Total Penjualan BBM'], ['type_report_id' => 3, 'nama_type_report' => 'Setoran, Penerimaan BBM'], ['type_report_id' => 4, 'nama_type_report' => 'Bongkaran BBM Aktual'], ['type_report_id' => 5, 'nama_type_report' => 'Stok BBM di Dombak']];

        $spbuSelected = $request->spbu_id;
        $produkSelected = $request->produk_id ?? 'all';
        $monthSelected = $request->month ?? date('Y-m');
        $typeReportSelected = $request->type_report_id ?? '1';

        if (!is_null($request->type_report_id)) {
            $this->generatePdf();
        }
        $waktuMulai = Carbon::createFromFormat('Y-m', $monthSelected)->firstOfMonth();
        $waktuSelesai = Carbon::createFromFormat('Y-m', $monthSelected)->endOfMonth();
        return view('financial/margin/index', compact('spbu', 'spbuSelected', 'monthSelected', 'produk', 'produkSelected', 'typeReport', 'typeReportSelected'));
    }

    public function totalisator()
    {
    }
    public function total_penjualan()
    {
    }
    public function setoran_penerimaan()
    {
    }
    public function bongkaran()
    {
    }
    public function stok_dombak()
    {
    }
    public function generatePdf()
    {
        dd("GENERATE");
    }
}
