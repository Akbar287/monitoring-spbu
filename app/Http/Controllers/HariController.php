<?php

namespace App\Http\Controllers;

use App\Http\Requests\HariRequest;
use App\Models\Hari;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class HariController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Hari::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('hari_kerja', function ($row) {
                    $actionBtn = $row->hari_kerja == 1 ? 'Hari Kerja' : 'Hari Libur';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('hari.show', $row->hari_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Hari',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')]],
            'createUrl' => route('hari.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Hari', 'data' => 'nama_hari'],
                    ['label' => 'Hari Kerja', 'data' => 'hari_kerja'],
                    ['label' => 'Keterangan', 'data' => 'keterangan'],
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
            'title' => 'Tambah Hari',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Hari', 'url' => route('hari.index')]],
            'backUrl' => route('hari.index'),
            'method' => 'post',
            'action' => route('hari.store'),
            'fields' => [
                ['name' => 'nama_hari', 'label' => 'Nama Hari', 'type' => 'text', 'value' => null],
                ['name' => 'hari_kerja', 'label' => 'Jenis Hari', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Hari Kerja'], ['value' => 'false', 'label' => 'Hari Libur']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HariRequest $hariRequest)
    {
        $hari = Hari::where('nama_hari', request('nama_hari'))->where('hari_kerja', request('hari_kerja') == 'true' ? 1 : 0)->where('keterangan', request('keterangan'))->first();
        if (is_null($hari)) {
            $hari = Hari::create($this->hariData());
        }

        return redirect('/konfigurasi/hari')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Hari sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hari $hari)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Hari',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Hari', 'url' => route('hari.index')]],
            'backUrl' => route('hari.index'),
            'fields' => [
                ['label' => 'Nama Hari', 'value' => $hari->nama_hari],
                ['label' => 'Jenis Hari', 'value' => filter_var($hari->hari_kerja, FILTER_VALIDATE_BOOLEAN) ? 'Hari Kerja' : 'Hari Libur'],
                ['label' => 'Keterangan', 'value' => $hari->keterangan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('hari.edit', $hari), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('hari.destroy', $hari),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hari $hari)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Hari',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Hari', 'url' => route('hari.index')]],
            'backUrl' => route('hari.index'),
            'method' => 'put',
            'action' => route('hari.update', $hari),
            'fields' => [
                ['name' => 'nama_hari', 'label' => 'Nama Hari', 'type' => 'text', 'value' => $hari->nama_hari],
                ['name' => 'hari_kerja', 'label' => 'Jenis Hari', 'type' => 'select', 'value' => filter_var($hari->hari_kerja, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Hari Kerja'], ['value' => 'false', 'label' => 'Hari Libur']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $hari->keterangan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HariRequest $hariRequest, Hari $hari)
    {
        $hari->update($this->hariData());

        return redirect('/konfigurasi/hari')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Hari sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hari $hari)
    {
        $hari->delete();

        return redirect('/konfigurasi/hari/')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Hari sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function hariData()
    {
        return [
            'nama_hari' => request('nama_hari'),
            'hari_kerja' => request('hari_kerja') == 'true' ? 1 : 0,
            'keterangan' => request('keterangan'),
        ];
    }
}
