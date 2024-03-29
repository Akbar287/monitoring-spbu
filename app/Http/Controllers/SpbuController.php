<?php

namespace App\Http\Controllers;

use App\Models\Spbu;
use Illuminate\Http\Request;
use App\Http\Requests\SpbuRequest;
use Yajra\DataTables\DataTables;

class SpbuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $spbus = Spbu::all();
        if ($request->ajax()) {
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
        return view('konfigurasi/spbu/index', compact('spbus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('konfigurasi/spbu/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SpbuRequest $spbuRequest)
    {
        $spbu = Spbu::create($this->spbuData());

        return redirect('/konfigurasi/spbu/' . $spbu->spbu_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Spbu sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Spbu $spbu)
    {
        return view('konfigurasi/spbu/show', compact('spbu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Spbu $spbu)
    {
        return view('konfigurasi/spbu/edit', compact('spbu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SpbuRequest $spbuRequest, Spbu $spbu)
    {
        $spbu->update($this->spbuData());

        return redirect('/konfigurasi/spbu/' . $spbu->spbu_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Spbu sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
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
