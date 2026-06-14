<?php

namespace App\Http\Controllers;

use App\Http\Requests\PajakPembelianLibRequest;
use App\Models\PajakPembelianLib;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class PajakPembelianLibController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = PajakPembelianLib::orderBy('created_at', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('d F Y');
                })
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pajak_pembelian_lib.show', $row->pajak_pembelian_lib_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Pajak',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')]],
            'createUrl' => route('pajak_pembelian_lib.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'PPN', 'data' => 'ppn'],
                    ['label' => 'PPH', 'data' => 'pph'],
                    ['label' => 'PPBKB', 'data' => 'ppbkb'],
                    ['label' => 'Aktif', 'data' => 'is_aktif'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Pajak',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Pajak', 'url' => route('pajak_pembelian_lib.index')]],
            'backUrl' => route('pajak_pembelian_lib.index'),
            'method' => 'post',
            'action' => route('pajak_pembelian_lib.store'),
            'fields' => [
                ['name' => 'ppn', 'label' => 'PPN', 'type' => 'text', 'value' => null, 'col' => 'col-md-4'],
                ['name' => 'pph', 'label' => 'PPH', 'type' => 'text', 'value' => null, 'col' => 'col-md-4'],
                ['name' => 'ppbkb', 'label' => 'PPBKB', 'type' => 'text', 'value' => null, 'col' => 'col-md-4'],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PajakPembelianLibRequest $pajakPembelianLibRequest)
    {
        $pajakPembelianLib = PajakPembelianLib::where('ppn', request('ppn'))->where('ppbkb', request('ppbkb'))->where('pph', request('pph'))->where('is_aktif', request('is_aktif') ? 1 : 0)->first();
        if (is_null($pajakPembelianLib)) {
            $pajakPembelianLib = PajakPembelianLib::create($this->pajakPembelianLibData());
        }

        return redirect('/konfigurasi/pajak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pajak Pembelian Library sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(PajakPembelianLib $pajak)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Pajak',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Pajak', 'url' => route('pajak_pembelian_lib.index')]],
            'backUrl' => route('pajak_pembelian_lib.index'),
            'fields' => [
                ['label' => 'Tanggal', 'value' => $pajak->tanggal],
                ['label' => 'PPN', 'value' => $pajak->ppn],
                ['label' => 'PPH', 'value' => $pajak->pph],
                ['label' => 'PPBKB', 'value' => $pajak->ppbkb],
                ['label' => 'Status', 'value' => filter_var($pajak->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif'],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('pajak_pembelian_lib.edit', $pajak), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('pajak_pembelian_lib.destroy', $pajak),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PajakPembelianLib $pajak)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Pajak',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Pajak', 'url' => route('pajak_pembelian_lib.index')]],
            'backUrl' => route('pajak_pembelian_lib.index'),
            'method' => 'put',
            'action' => route('pajak_pembelian_lib.update', $pajak),
            'fields' => [
                ['name' => 'ppn', 'label' => 'PPN', 'type' => 'text', 'value' => $pajak->ppn, 'col' => 'col-md-4'],
                ['name' => 'pph', 'label' => 'PPH', 'type' => 'text', 'value' => $pajak->pph, 'col' => 'col-md-4'],
                ['name' => 'ppbkb', 'label' => 'PPBKB', 'type' => 'text', 'value' => $pajak->ppbkb, 'col' => 'col-md-4'],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => filter_var($pajak->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PajakPembelianLibRequest $pajakPembelianLibRequest, PajakPembelianLib $pajak)
    {
        $pajak->update($this->pajakPembelianLibData());

        return redirect('/konfigurasi/pajak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pajak Pembelian Library sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PajakPembelianLib $pajak)
    {
        $pajak->delete();

        return redirect('/konfigurasi/pajak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pajak Pembelian Library sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function pajakPembelianLibData()
    {
        return [
            'ppn' => request('ppn'),
            'ppbkb' => request('ppbkb'),
            'pph' => request('pph'),
            'is_aktif' => request('is_aktif') ? 1 : 0,
        ];
    }
}
