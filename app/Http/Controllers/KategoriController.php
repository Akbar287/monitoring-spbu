<?php

namespace App\Http\Controllers;

use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Kategori::get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('kategori.show', $row->kategori_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Kategori',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')]],
            'createUrl' => route('kategori.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Kategori', 'data' => 'nama_kategori'],
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
            'title' => 'Tambah Kategori',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Kategori', 'url' => route('kategori.index')]],
            'backUrl' => route('kategori.index'),
            'method' => 'post',
            'action' => route('kategori.store'),
            'fields' => [
                ['name' => 'nama_kategori', 'label' => 'Nama Kategori', 'type' => 'text', 'value' => null],
                ['name' => 'detail', 'label' => 'Detail', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KategoriRequest $kategoriRequest)
    {
        $kategori = Kategori::where('nama_kategori', request('nama_kategori'))->where('detail', request('detail'))->first();
        if (is_null($kategori)) {
            $kategori = Kategori::create($this->kategoriData());
        }

        return redirect('/artikel/kategori')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Kategori sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kategori $kategori)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Kategori',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Kategori', 'url' => route('kategori.index')]],
            'backUrl' => route('kategori.index'),
            'fields' => [
                ['label' => 'Nama Kategori', 'value' => $kategori->nama_kategori],
                ['label' => 'Detail', 'value' => $kategori->detail],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('kategori.edit', $kategori), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('kategori.delete', $kategori),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kategori $kategori)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Kategori',
            'breadcrumbs' => [['label' => 'Artikel', 'url' => route('menu.artikel')], ['label' => 'Kategori', 'url' => route('kategori.index')]],
            'backUrl' => route('kategori.index'),
            'method' => 'put',
            'action' => route('kategori.update', $kategori),
            'fields' => [
                ['name' => 'nama_kategori', 'label' => 'Nama Kategori', 'type' => 'text', 'value' => $kategori->nama_kategori],
                ['name' => 'detail', 'label' => 'Detail', 'type' => 'textarea', 'value' => $kategori->detail],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KategoriRequest $kategoriRequest, Kategori $kategori)
    {
        $kategori->update($this->kategoriData());

        return redirect('/artikel/kategori')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Kategori sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kategori $kategori)
    {
        $kategori->delete();

        return redirect('/artikel/kategori')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Kategori sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function kategoriData()
    {
        return [
            'nama_kategori' => request('nama_kategori'),
            'detail' => request('detail')
        ];
    }
}
