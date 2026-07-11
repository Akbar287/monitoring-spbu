<?php

namespace App\Http\Controllers;

use App\Models\Jurnal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class JurnalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Jurnal::select('jurnal.*', 'spbu.nama_spbu', DB::raw('(select sum(debit) from jurnal_detail where jurnal_detail.jurnal_id = jurnal.jurnal_id) as total'))
                ->join('spbu', 'spbu.spbu_id', 'jurnal.spbu_id')
                ->orderBy('jurnal.tanggal', 'desc')
                ->orderBy('jurnal.nomor_jurnal', 'desc')
                ->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_label', function ($row) {
                    return Carbon::parse($row->tanggal)->format('d F Y');
                })
                ->addColumn('sumber', function ($row) {
                    return ucwords(str_replace('_', ' ', $row->tipe_sumber));
                })
                ->addColumn('total_label', function ($row) {
                    return 'Rp. ' . number_format(floatval($row->total), 2, ',', '.');
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('jurnal.show', $row->jurnal_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return Inertia::render('Table/Index', [
            'title' => 'Jurnal Umum',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')]],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nomor', 'data' => 'nomor_jurnal'],
                    ['label' => 'Tanggal', 'data' => 'tanggal_label'],
                    ['label' => 'SPBU', 'data' => 'nama_spbu'],
                    ['label' => 'Sumber', 'data' => 'sumber'],
                    ['label' => 'Keterangan', 'data' => 'keterangan'],
                    ['label' => 'Total', 'data' => 'total_label'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Jurnal $jurnal)
    {
        $jurnal->load(['jurnal_detail.coa', 'spbu']);

        $rows = $jurnal->jurnal_detail->map(fn ($d) => [
            $d->coa->kode_akun,
            $d->coa->nama_akun,
            floatval($d->debit) > 0 ? 'Rp. ' . number_format(floatval($d->debit), 2, ',', '.') : '',
            floatval($d->kredit) > 0 ? 'Rp. ' . number_format(floatval($d->kredit), 2, ',', '.') : '',
            $d->keterangan ?? '',
        ])->values()->all();

        $totalDebit = $jurnal->jurnal_detail->sum(fn ($d) => floatval($d->debit));
        $totalKredit = $jurnal->jurnal_detail->sum(fn ($d) => floatval($d->kredit));
        $rows[] = ['', 'TOTAL', 'Rp. ' . number_format($totalDebit, 2, ',', '.'), 'Rp. ' . number_format($totalKredit, 2, ',', '.'), ''];

        return Inertia::render('Show/Index', [
            'title' => 'Detail Jurnal ' . $jurnal->nomor_jurnal,
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Jurnal Umum', 'url' => route('jurnal.index')]],
            'backUrl' => route('jurnal.index'),
            'fields' => [
                ['label' => 'Nomor Jurnal', 'value' => $jurnal->nomor_jurnal],
                ['label' => 'Tanggal', 'value' => Carbon::parse($jurnal->tanggal)->format('d F Y')],
                ['label' => 'SPBU', 'value' => $jurnal->spbu->nama_spbu ?? '-'],
                ['label' => 'Sumber', 'value' => ucwords(str_replace('_', ' ', $jurnal->tipe_sumber))],
                ['label' => 'Keterangan', 'value' => $jurnal->keterangan ?? '-'],
            ],
            'tables' => [[
                'title' => 'Baris Jurnal',
                'columns' => ['Kode Akun', 'Nama Akun', 'Debit', 'Kredit', 'Keterangan'],
                'rows' => $rows,
            ]],
        ]);
    }
}
