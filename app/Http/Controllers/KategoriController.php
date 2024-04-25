<?php

namespace App\Http\Controllers;

use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
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
        return view('article/kategori/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('article/kategori/create');
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

        return redirect('/artikel/kategori/' . $kategori->kategori_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Kategori sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kategori $kategori)
    {
        return view('article/kategori/show', compact('kategori'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kategori $kategori)
    {
        return view('article/kategori/edit', compact('kategori'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KategoriRequest $kategoriRequest, Kategori $kategori)
    {
        $kategori->update($this->kategoriData());

        return redirect('/artikel/kategori/' . $kategori->kategori_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Kategori sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
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
