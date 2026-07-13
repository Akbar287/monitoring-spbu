<?php

namespace App\Http\Controllers;

use App\Exports\PajakPembelianExport;
use App\Models\PajakPembelian;
use App\Models\Spbu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;
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

        if ($request->ajax() && ! $request->inertia()) {
            $data = is_null($request->spbu_id) ?
                PajakPembelian::select('pajak_pembelian.pajak_pembelian_id', 'pajak_pembelian.ppn', 'pajak_pembelian.ppbkb', 'pajak_pembelian.pph', 'rencana_pembelian.tanggal_pembelian', 'rencana_pembelian.kode_pembelian')
                ->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'pajak_pembelian.rencana_pembelian_id')
                ->whereBetween('rencana_pembelian.tanggal_pembelian', [$waktuMulai, $waktuSelesai])
                ->get() :
                PajakPembelian::select('pajak_pembelian.pajak_pembelian_id', 'pajak_pembelian.ppn', 'pajak_pembelian.ppbkb', 'pajak_pembelian.pph', 'rencana_pembelian.tanggal_pembelian', 'rencana_pembelian.kode_pembelian')
                ->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'pajak_pembelian.rencana_pembelian_id')
                ->where('rencana_pembelian.spbu_id', $request->spbu_id)
                ->whereBetween('rencana_pembelian.tanggal_pembelian', [$waktuMulai, $waktuSelesai])
                ->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    $actionBtn = Carbon::createFromFormat('Y-m-d', $row->tanggal_pembelian)->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('kode_pembelian', function ($row) {
                    $actionBtn = $row->kode_pembelian;
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
        return Inertia::render('Table/Index', [
            'title' => 'Rekap Pajak',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'headerActions' => [['label' => 'Export', 'url' => route('rekap-pajak.export'), 'color' => 'success', 'icon' => 'file-excel']],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'selectFilters' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'value' => $spbuSelected, 'nullable' => true, 'options' => array_merge([['value' => 'all', 'label' => 'Semua']], $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu])->all())],
            ],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Kode Pembelian', 'data' => 'kode_pembelian'],
                    ['label' => 'PPN', 'data' => 'ppn'],
                    ['label' => 'PPH', 'data' => 'pph'],
                    ['label' => 'PPBKB', 'data' => 'ppbkb'],
                    ['label' => 'Total Pajak', 'data' => 'total_pajak'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function show(PajakPembelian $pajakPembelian)
    {
        $rencana = $pajakPembelian->rencana_pembelian()->first();
        return Inertia::render('Show/Index', [
            'title' => 'Detail Pajak Pembelian',
            'breadcrumbs' => [
                ['label' => 'Financial', 'url' => route('menu.financial')],
                ['label' => 'Rekap Pajak', 'url' => route('rekap-pajak.index')],
            ],
            'backUrl' => route('rekap-pajak.index'),
            'fields' => [
                ['label' => 'Id Rencana Pembelian', 'value' => $rencana?->rencana_pembelian_id],
                ['label' => 'SPBU', 'value' => optional($rencana?->spbu()->first())->nama_spbu],
                ['label' => 'Keterangan', 'value' => $rencana?->keterangan],
                ['label' => 'Grand Total (Sblm Pajak)', 'value' => 'Rp. ' . number_format($rencana?->grand_total ?? 0, 6, '.', ',')],
                ['label' => 'Grand Total (Stlh Pajak)', 'value' => 'Rp. ' . number_format($pajakPembelian->total_amount ?? $rencana?->grand_total ?? 0, 6, '.', ',')],
            ],
            'tables' => [[
                'title' => 'Pajak Pembelian',
                'columns' => ['Komponen', 'Nominal'],
                'rows' => [
                    ['Net Price', 'Rp. ' . number_format($pajakPembelian->net_price ?? $rencana?->grand_total ?? 0, 6, '.', ',')],
                    ['PPN', 'Rp. ' . number_format($pajakPembelian->ppn ?? 0, 6, '.', ',')],
                    ['PPBKB', 'Rp. ' . number_format($pajakPembelian->ppbkb ?? 0, 6, '.', ',')],
                    ['PPh', 'Rp. ' . number_format($pajakPembelian->pph ?? 0, 6, '.', ',')],
                    ['Total Amount (Gross Price)', 'Rp. ' . number_format($pajakPembelian->total_amount ?? $rencana?->grand_total ?? 0, 6, '.', ',')],
                ],
            ]],
        ]);
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
            ->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'pajak_pembelian.rencana_pembelian_id')
            ->where('rencana_pembelian.spbu_id', $request->spbu_id)
            ->whereBetween('rencana_pembelian.tanggal_pembelian', [$waktuMulai, $waktuSelesai])
            ->orderBy('rencana_pembelian.tanggal_pembelian')
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
