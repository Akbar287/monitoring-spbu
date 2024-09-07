<?php

namespace App\Http\Controllers;

use App\Http\Requests\HariRequest;
use App\Models\Hari;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class HariController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Hari::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('hari_kerja', function ($row) {
                    $actionBtn = $row->hari_kerja == 1 ? 'Hari Kerja' : 'Hari Libur';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('hari.show', $row->hari_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('konfigurasi/hari/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('konfigurasi/hari/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HariRequest $hariRequest)
    {
        $hari = Hari::where('nama_hari', request('nama_hari'))->where('hari_kerja', request('hari_kerja') == 'true' ? 1 : 0)->where('keterangan', request('keterangan'))->first();
        if (is_null($hari)) {
            $hari = Hari::create($this->hariData());
        }

        return redirect('/konfigurasi/hari/' . $hari->hari_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Hari sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hari $hari)
    {
        return view('konfigurasi/hari/show', compact('hari'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hari $hari)
    {
        return view('konfigurasi/hari/edit', compact('hari'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HariRequest $hariRequest, Hari $hari)
    {
        $hari->update($this->hariData());

        return redirect('/konfigurasi/hari/' . $hari->hari_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Hari sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hari $hari)
    {
        $hari->delete();

        return redirect('/konfigurasi/hari/')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Hari sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function hariData()
    {
        return [
            'nama_hari' => request('nama_hari'),
            'hari_kerja' => request('hari_kerja') == 'true' ? 1 : 0,
            'keterangan' => request('keterangan'),
        ];
    }
}
