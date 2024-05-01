<?php

namespace App\Http\Controllers;

use App\Exports\PajakPembelianExport;
use App\Models\PajakPembelian;
use App\Models\Spbu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class RekapPajakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $spbu = Spbu::select('nama_spbu')->addSelect('spbu_id')->get();
        $spbuSelected = $request->spbu_id;
        $waktuMulai = $request->has('waktu_mulai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_mulai) : Carbon::now()->firstOfMonth();
        $waktuSelesai = $request->has('waktu_selesai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_selesai) : Carbon::now()->endOfMonth();
        if ($waktuMulai->gt($waktuSelesai)) {
            $temp = $waktuMulai;
            $waktuMulai = $waktuSelesai;
            $waktuSelesai = $temp;
        }
        $waktuMulai = $waktuMulai->format('Y-m-d');
        $waktuSelesai = $waktuSelesai->format('Y-m-d');

        if ($request->ajax()) {
            $data = is_null($request->spbu_id) ?
                PajakPembelian::select()
                ->whereBetween('created_at', [$waktuMulai, $waktuSelesai])
                ->get() :
                PajakPembelian::join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'pajak_pembelian.rencana_pembelian_id')
                ->where('rencana_pembelian.spbu_id', $request->spbu_id)
                ->whereBetween('pajak_pembelian.created_at', [$waktuMulai, $waktuSelesai])
                ->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    $actionBtn = Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('kode_pembelian', function ($row) {
                    $actionBtn = $row->rencana_pembelian()->first()->kode_pembelian;
                    return $actionBtn;
                })
                ->addColumn('ppn', function ($row) {
                    $actionBtn = "Rp. " . number_format($row->ppn, 6, '.', ',');
                    return $actionBtn;
                })
                ->addColumn('ppbkb', function ($row) {
                    $actionBtn = "Rp. " . number_format($row->ppbkb, 6, '.', ',');
                    return $actionBtn;
                })
                ->addColumn('pph', function ($row) {
                    $actionBtn = "Rp. " . number_format($row->pph, 6, '.', ',');
                    return $actionBtn;
                })
                ->addColumn('total_pajak', function ($row) {
                    $actionBtn = "Rp. " . number_format($row->ppn + $row->ppbkb + $row->pph, 6, '.', ',');
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('rekap-pajak.show', $row->pajak_pembelian_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('financial/rekap_pajak/index', compact('waktuMulai', 'waktuSelesai', 'spbu', 'spbuSelected'));
    }

    public function show(PajakPembelian $pajakPembelian)
    {
        $rencana = $pajakPembelian->rencana_pembelian()->first();
        return view('financial/rekap_pajak/show', compact('rencana'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'spbu_id' => ['required'],
            'waktu_mulai' => ['required'],
            'waktu_selesai' => ['required'],
        ]);

        $waktuMulai = $request->has('waktu_mulai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_mulai) : Carbon::now()->firstOfMonth();
        $waktuSelesai = $request->has('waktu_selesai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_selesai) : Carbon::now()->endOfMonth();
        if ($waktuMulai->gt($waktuSelesai)) {
            $temp = $waktuMulai;
            $waktuMulai = $waktuSelesai;
            $waktuSelesai = $temp;
        }

        $pajak = PajakPembelian::select('tanggal_pembelian', 'kode_pembelian', 'ppn', 'pph', 'ppbkb')
            // ->selectRaw('SUM(ppn + pph + ppbkb) as total_pajak')
            ->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'pajak_pembelian.rencana_pembelian_id')
            ->where('rencana_pembelian.spbu_id', $request->spbu_id)
            ->whereBetween('pajak_pembelian.created_at', [$waktuMulai, $waktuSelesai])
            ->get()
            ->toArray();

        for ($i = 0; $i < count($pajak); $i++) {
            $pajak[$i]['total_pajak']  = $pajak[$i]['ppn'] + $pajak[$i]['pph'] + $pajak[$i]['ppbkb'];
        }

        $header = ['Tanggal', 'Kode Pembelian', 'PPN', "PPH", 'PPBKB', 'Total Pajak'];

        array_unshift($pajak, $header);
        $final_to_download = Excel::download(new PajakPembelianExport($pajak), 'Rekap_Pajak_Excel.xls', \Maatwebsite\Excel\Excel::XLS);
        // Storage::delete('temp/' . $name);
        return $final_to_download;
    }
}
