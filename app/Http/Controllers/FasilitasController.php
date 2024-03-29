<?php

namespace App\Http\Controllers;

use App\Http\Requests\FasilitasRequest;
use App\Models\Fasilitas;
use App\Models\Spbu;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class FasilitasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Fasilitas::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('fasilitas.show', $row->fasilitas_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/fasilitas/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::all();
        return view('master/fasilitas/create', compact('spbu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FasilitasRequest $fasilitasRequest)
    {
        $spbu = Spbu::find($fasilitasRequest->spbu_id);
        $fasilitas = $spbu->fasilitas()->create($this->fasilitasData());

        return redirect('/master/fasilitas/' . $fasilitas->fasilitas_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Fasilitas sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fasilitas $fasilitas)
    {
        return view('master/fasilitas/show', compact('fasilitas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fasilitas $fasilitas)
    {
        $spbu = Spbu::all();
        return view('master/fasilitas/edit', compact('spbu', 'fasilitas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FasilitasRequest $fasilitasRequest, Fasilitas $fasilitas)
    {
        $fasilitas->update($this->fasilitasData());

        return redirect('/master/fasilitas/' . $fasilitas->fasilitas_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Fasilitas sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fasilitas $fasilitas)
    {
        $fasilitas->delete();

        return redirect('/master/fasilitas')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Fasilitas sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function fasilitasData()
    {
        return [
            'nama_fasilitas' => request('nama_fasilitas'),
            'jumlah' => request('jumlah'),
            'tanggal_digunakan' => request('tanggal_digunakan'),
            'keterangan' => request('keterangan'),
        ];
    }
}
