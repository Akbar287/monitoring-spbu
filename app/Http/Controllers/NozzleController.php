<?php

namespace App\Http\Controllers;

use App\Http\Requests\NozzleRequest;
use App\Models\Dispenser;
use App\Models\Nozzle;
use App\Models\Produk;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class NozzleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Nozzle::select('*')->addSelect('dispenser.nama_dispenser')->addSelect('nozzle.keterangan')->join('dispenser', 'nozzle.dispenser_id', 'dispenser.dispenser_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn =  $row->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('nama_payung', function ($row) {
                    $actionBtn =  $row->dispenser()->first()->payung()->first()->nama_payung;
                    return $actionBtn;
                })
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn =  $row->is_aktif == true ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('nozzle.show', $row->nozzle_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/nozzle/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dispenser = Dispenser::all();
        $produk = Produk::all();
        return view('master/nozzle/create', compact('dispenser', 'produk'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NozzleRequest $nozzleRequest)
    {
        $dispenser = Dispenser::find($nozzleRequest->dispenser_id);
        $nozzle = $dispenser->nozzle()->create($this->nozzleData());

        return redirect('/master/nozzle/' . $nozzle->nozzle_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Nozzle sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Nozzle $nozzle)
    {
        return view('master/nozzle/show', compact('nozzle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nozzle $nozzle)
    {
        $dispenser = Dispenser::all();
        $produk = Produk::all();
        return view('master/nozzle/edit', compact('dispenser', 'nozzle', 'produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NozzleRequest $nozzleRequest, Nozzle $nozzle)
    {
        $nozzle->update($this->nozzleData());

        return redirect('/master/nozzle/' . $nozzle->nozzle_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Nozzle sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nozzle $nozzle)
    {
        $nozzle->delete();

        return redirect('/master/nozzle')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Nozzle sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function nozzleData()
    {
        return [
            'produk_id' => request('produk_id'),
            'nama_nozzle' => request('nama_nozzle'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif'),
        ];
    }
}
