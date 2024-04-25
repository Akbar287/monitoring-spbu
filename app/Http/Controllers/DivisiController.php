<?php

namespace App\Http\Controllers;


use App\Models\Divisi;
use App\Http\Requests\DivisiRequest;
use App\Models\Spbu;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class DivisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Divisi::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == true ? 'Aktif' : "Tidak Aktif";
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('divisi.show', $row->divisi_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('konfigurasi/divisi/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::all();
        return view('konfigurasi/divisi/create', compact('spbu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DivisiRequest $divisiRequest)
    {
        $spbu = Spbu::find($divisiRequest->spbu_id);

        $divisi = $spbu->divisi()->where('nama_divisi', request('nama_divisi'))->where('keterangan', request('keterangan'))->where('is_aktif', request('is_aktif') == 'true' ? true : false)->first();
        if (is_null($divisi)) {
            $divisi = $spbu->divisi()->create($this->divisiData());
        }

        return redirect('/konfigurasi/divisi/' . $divisi->divisi_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Divisi sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Divisi $divisi)
    {
        return view('konfigurasi/divisi/show', compact('divisi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Divisi $divisi)
    {
        $spbu = Spbu::all();
        return view('konfigurasi/divisi/edit', compact('divisi', 'spbu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DivisiRequest $divisiRequest, Divisi $divisi)
    {
        $divisi->update($this->divisiData());

        return redirect('/konfigurasi/divisi/' . $divisi->divisi_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Divisi sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Divisi $divisi)
    {
        $divisi->delete();

        return redirect('/konfigurasi/divisi/')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Divisi sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function divisiData()
    {
        return [
            'nama_divisi' => request('nama_divisi'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif') == 'true' ? true : false
        ];
    }
}
