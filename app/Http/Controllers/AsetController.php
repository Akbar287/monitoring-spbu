<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsetRequest;
use App\Models\Aset;
use Yajra\DataTables\DataTables;
use App\Models\Spbu;
use Illuminate\Http\Request;

class AsetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Aset::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('aset.show', $row->aset_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/aset/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::all();
        return view('master/aset/create', compact('spbu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AsetRequest $asetRequest)
    {
        $spbu = Spbu::find($asetRequest->spbu_id);

        $aset = $spbu->aset()->where('nama_aset', request('nama_aset'))->where('jumlah', request('jumlah'))->where('tanggal_digunakan', request('tanggal_digunakan'))->where('harga', str_replace(',', '', request('harga')))->where('penyusutan_per_hari', str_replace(',', '', request('penyusutan_per_hari')))->first();
        if (is_null($aset)) {
            $aset = $spbu->aset()->create($this->asetData());
        }

        return redirect('/master/aset/' . $aset->aset_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Aset sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Aset $aset)
    {
        return view('master/aset/show', compact('aset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Aset $aset)
    {
        $spbu = Spbu::all();
        return view('master/aset/edit', compact('spbu', 'aset'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AsetRequest $asetRequest, Aset $aset)
    {
        $aset->update($this->asetData());

        return redirect('/master/aset/' . $aset->aset_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Aset sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Aset $aset)
    {
        $aset->delete();

        return redirect('/master/aset')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Aset sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function asetData()
    {
        return [
            'nama_aset' => request('nama_aset'),
            'jumlah' => request('jumlah'),
            'tanggal_digunakan' => request('tanggal_digunakan'),
            'harga' => str_replace(',', '', request('harga')),
            'penyusutan_per_hari' => str_replace(',', '', request('penyusutan_per_hari')),
            'is_digunakan' => request('is_digunakan') ? 1 : 0,
            'keterangan' => request('keterangan'),
        ];
    }
}
