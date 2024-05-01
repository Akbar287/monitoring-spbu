<?php

namespace App\Http\Controllers;

use App\Http\Requests\DombakRequest;
use App\Models\Dombak;
use App\Models\Spbu;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class DombakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Dombak::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == true ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('dombak.show', $row->dombak_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/dombak/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::all();
        return view('master/dombak/create', compact('spbu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DombakRequest $dombakRequest)
    {
        $spbu = Spbu::find($dombakRequest->spbu_id);

        $dombak = $spbu->dombak()->where('nama_dombak', request('nama_dombak'))->where('keterangan', request('keterangan'))->where('is_aktif', request('is_aktif'))->first();
        if (is_null($dombak)) {
            $dombak = $spbu->dombak()->create($this->dombakData());
        }

        return redirect('/master/dombak/' . $dombak->dombak_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dombak sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Dombak $dombak)
    {
        return view('master/dombak/show', compact('dombak'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dombak $dombak)
    {
        $spbu = Spbu::all();
        return view('master/dombak/edit', compact('spbu', 'dombak'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DombakRequest $dombakRequest, Dombak $dombak)
    {
        $dombak->update($this->dombakData());

        return redirect('/master/dombak/' . $dombak->dombak_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dombak sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dombak $dombak)
    {
        $dombak->delete();

        return redirect('/master/dombak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dombak sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function dombakData()
    {
        return [
            'nama_dombak' => request('nama_dombak'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif')
        ];
    }

    public function api(Request $request)
    {
        $request->validate([
            'dombak_id' => ['required']
        ]);

        $dombak = Dombak::where('dombak_id', $request->dombak_id)->first();
        $data = $dombak->stok_inventory_dombak()->first();

        if ($dombak->konversi()->count() > 0) {
            $min = $dombak->konversi()->select('tinggi')->orderBy('tinggi', 'asc')->limit(1)->first();
            $max = $dombak->konversi()->select('tinggi')->orderBy('tinggi', 'desc')->limit(1)->first();

            if (is_null($dombak)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'dombak not found',
                    'data' => []
                ], 404);
            }

            $tinggi = [
                'min' => $min->tinggi,
                'max' => $max->tinggi
            ];
        } else {
            $tinggi = [
                'min' => 0,
                'max' => 0
            ];
        }

        $dombakLain = null;
        if (!is_null($request->type)) {
            if (request('type') == 'transfer') {
                $dombakLain = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_dombak()->select('dombak.dombak_id')->addSelect('dombak.nama_dombak')->addSelect('stok_inventory_dombak.stok')->addSelect('produk.nama_produk')->join('stok_inventory', 'stok_inventory.stok_inventory_id', 'stok_inventory_dombak.stok_inventory_id')->join('produk', 'produk.produk_id', 'stok_inventory.produk_id')->join('dombak', 'dombak.dombak_id', 'stok_inventory_dombak.dombak_id')->where('stok_inventory_dombak.dombak_id', '!=', $dombak->dombak_id)->get();
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'stok konversi not found',
            'data' => [
                'stok' => number_format($data->stok, 6, ".", ",")
            ],
            'tinggi' => $tinggi,
            'dombak' => $dombakLain
        ], 200);
    }
}
