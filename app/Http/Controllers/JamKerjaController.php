<?php

namespace App\Http\Controllers;

use App\Http\Requests\JamKerjaRequest;
use App\Models\JamKerja;
use App\Models\Spbu;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class JamKerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = JamKerja::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('jam_kerja.show', $row->jam_kerja_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('konfigurasi/jam_kerja/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::all();
        return view('konfigurasi/jam_kerja/create', compact('spbu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JamKerjaRequest $jamKerjaRequest)
    {
        $spbu = Spbu::find($jamKerjaRequest->spbu_id);

        $jamKerja = $spbu->jam_kerja()->where("spbu_id", request('spbu_id'))->where("nama_jam_kerja", request('nama_jam_kerja'))->where("batas_awal_in", request('batas_awal_in'))->where("batas_akhir_in", request('batas_akhir_in'))->where("jam_datang_in", request('jam_datang_in'))->where("jam_pulang_out", request('jam_pulang_out'))->where("akhir_out", request('akhir_out'))->where("jam_istirahat", request('jam_istirahat'))->where("masuk_istirahat", request('masuk_istirahat'))->where("batas_telat", request('batas_telat'))->where("urutan", request('urutan'))->first();
        if (is_null($jamKerja)) {
            $jamKerja = $spbu->jam_kerja()->create($this->jamKerjaData());
        }

        return redirect('/konfigurasi/jam_kerja/' . $jamKerja->jam_kerja_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jam Kerja sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(JamKerja $jamKerja)
    {
        return view('konfigurasi/jam_kerja/show', compact('jamKerja'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JamKerja $jamKerja)
    {
        $spbu = Spbu::all();
        return view('konfigurasi/jam_kerja/edit', compact('jamKerja', 'spbu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JamKerjaRequest $jamKerjaRequest, JamKerja $jamKerja)
    {
        $jamKerja->update($this->jamKerjaData());

        return redirect('/konfigurasi/jam_kerja/' . $jamKerja->jam_kerja_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jam Kerja sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JamKerja $jamKerja)
    {
        $jamKerja->delete();

        return redirect('/konfigurasi/jam_kerja')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jam Kerja sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function jamKerjaData()
    {
        return [
            "spbu_id" => request('spbu_id'),
            "nama_jam_kerja" => request('nama_jam_kerja'),
            "batas_awal_in" => request('batas_awal_in'),
            "batas_akhir_in" => request('batas_akhir_in'),
            "jam_datang_in" => request('jam_datang_in'),
            "jam_pulang_out" => request('jam_pulang_out'),
            "akhir_out" => request('akhir_out'),
            "jam_istirahat" => request('jam_istirahat'),
            "masuk_istirahat" => request('masuk_istirahat'),
            "batas_telat" => request('batas_telat'),
            "urutan" => request('urutan')
        ];
    }
}
