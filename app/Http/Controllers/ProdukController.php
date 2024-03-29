<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdukRequest;
use App\Models\Produk;
use App\Models\Spbu;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Produk::select('produk.produk_id')->addSelect('produk.nama_produk')->addSelect('produk.is_aktif')->addSelect('detail_produk.nama_jenis')->addSelect('detail_produk.oktan')->join('detail_produk', 'produk.produk_id', 'detail_produk.produk_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif ? "Aktif" : "Tidak Aktif";
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('produk.show', $row->produk_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/produk/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::all();
        return view('master/produk/create', compact('spbu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProdukRequest $produkRequest)
    {
        $spbu = Spbu::find($produkRequest->spbu_id);
        $produk = $spbu->produk()->create($this->produkData());
        $detail = $produk->detail_produk()->create($this->detailProdukData());

        return redirect('/master/produk/' . $produk->produk_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Produk sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Produk $produk)
    {
        return view('master/produk/show', compact('produk'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produk $produk)
    {
        $spbu = Spbu::all();
        return view('master/produk/edit', compact('spbu', 'produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProdukRequest $produkRequest, Produk $produk)
    {
        $produk->update($this->produkData());
        $produk->detail_produk()->update($this->detailProdukData());

        return redirect('/master/produk/' . $produk->produk_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Produk sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produk $produk)
    {
        $produk->delete();

        return redirect('/master/produk')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Produk sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function produkData()
    {
        return [
            'nama_produk' => request('nama_produk'),
            'is_aktif' => request('is_aktif'),
        ];
    }

    public function detailProdukData()
    {
        return [
            'nama_jenis' => request('nama_jenis'),
            'oktan' => request('oktan'),
        ];
    }
}
