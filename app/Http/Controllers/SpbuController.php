<?php

namespace App\Http\Controllers;

use App\Models\Spbu;
use Illuminate\Http\Request;
use App\Http\Requests\SpbuRequest;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class SpbuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Spbu::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('spbu.show', $row->spbu_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'SPBU',
            'lead' => 'Kelola SPBU yang ingin anda monitoring.',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')]],
            'createUrl' => route('spbu.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama SPBU', 'data' => 'nama_spbu'],
                    ['label' => 'Nomor SPBU', 'data' => 'nomor_spbu'],
                    ['label' => 'Tanggal Berdiri', 'data' => 'tanggal_pendirian'],
                    ['label' => 'Alamat', 'data' => 'alamat'],
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
            'title' => 'Tambah SPBU',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'SPBU', 'url' => route('spbu.index')]],
            'backUrl' => route('spbu.index'),
            'method' => 'post',
            'action' => route('spbu.store'),
            'fields' => [
                ['name' => 'nama_spbu', 'label' => 'Nama SPBU', 'type' => 'text', 'value' => null],
                ['name' => 'nomor_spbu', 'label' => 'Nomor SPBU', 'type' => 'text', 'value' => null],
                ['name' => 'tanggal_pendirian', 'label' => 'Tanggal Pendirian', 'type' => 'date', 'value' => null],
                ['name' => 'luas_lahan', 'label' => 'Luas Lahan', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'satuan_luas', 'label' => 'Satuan Luas', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SpbuRequest $spbuRequest)
    {
        $spbu = Spbu::where('nama_spbu', request('nama_spbu'))->where('nomor_spbu', request('nomor_spbu'))->where('tanggal_pendirian', request('tanggal_pendirian'))->where('luas_lahan', request('luas_lahan'))->where('satuan_luas', request('satuan_luas'))->where('alamat', request('alamat'))->first();
        if (is_null($spbu)) {
            $spbu = Spbu::create($this->spbuData());
        }

        return redirect('/konfigurasi/spbu')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Spbu sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Spbu $spbu)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail SPBU',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'SPBU', 'url' => route('spbu.index')]],
            'backUrl' => route('spbu.index'),
            'fields' => [
                ['label' => 'Nama SPBU', 'value' => $spbu->nama_spbu],
                ['label' => 'Nomor SPBU', 'value' => $spbu->nomor_spbu],
                ['label' => 'Tanggal Berdiri', 'value' => $spbu->tanggal_pendirian],
                ['label' => 'Luas Lahan', 'value' => trim($spbu->luas_lahan . ' ' . $spbu->satuan_luas)],
                ['label' => 'Alamat', 'value' => $spbu->alamat],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('spbu.edit', $spbu), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('spbu.destroy', $spbu),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Spbu $spbu)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah SPBU',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'SPBU', 'url' => route('spbu.index')]],
            'backUrl' => route('spbu.index'),
            'method' => 'put',
            'action' => route('spbu.update', $spbu),
            'fields' => [
                ['name' => 'nama_spbu', 'label' => 'Nama SPBU', 'type' => 'text', 'value' => $spbu->nama_spbu],
                ['name' => 'nomor_spbu', 'label' => 'Nomor SPBU', 'type' => 'text', 'value' => $spbu->nomor_spbu],
                ['name' => 'tanggal_pendirian', 'label' => 'Tanggal Pendirian', 'type' => 'date', 'value' => $spbu->tanggal_pendirian],
                ['name' => 'luas_lahan', 'label' => 'Luas Lahan', 'type' => 'text', 'value' => $spbu->luas_lahan, 'col' => 'col-md-6'],
                ['name' => 'satuan_luas', 'label' => 'Satuan Luas', 'type' => 'text', 'value' => $spbu->satuan_luas, 'col' => 'col-md-6'],
                ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea', 'value' => $spbu->alamat],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SpbuRequest $spbuRequest, Spbu $spbu)
    {
        $spbu->update($this->spbuData());

        return redirect('/konfigurasi/spbu')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Spbu sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Spbu $spbu)
    {
        $spbu->delete();

        return redirect('/konfigurasi/spbu')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Spbu sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function spbuData()
    {
        return [
            'nama_spbu' => request('nama_spbu'),
            'nomor_spbu' => request('nomor_spbu'),
            'tanggal_pendirian' => request('tanggal_pendirian'),
            'luas_lahan' => request('luas_lahan'),
            'satuan_luas' => request('satuan_luas'),
            'alamat' => request('alamat'),
        ];
    }
}
