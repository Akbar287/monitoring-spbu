<?php

namespace App\Http\Controllers;

use App\Http\Requests\JabatanRequest;
use App\Models\Jabatan;
use App\Models\Level;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class JabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Jabatan::select('jabatan.*')->addSelect('level.nama_level')->join('level', 'level.level_id', 'jabatan.level_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == true ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('jabatan.show', $row->jabatan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('konfigurasi/jabatan/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $level = Level::all();
        return view('konfigurasi/jabatan/create', compact('level'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JabatanRequest $jabatanRequest)
    {
        $level = Level::find($jabatanRequest->level_id);
        $jabatan = $level->jabatan()->create($this->jabatanData());

        return redirect('/konfigurasi/jabatan/' . $jabatan->jabatan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jabatan sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Jabatan $jabatan)
    {
        return view('konfigurasi/jabatan/show', compact('jabatan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jabatan $jabatan)
    {
        $level = Level::all();
        return view('konfigurasi/jabatan/edit', compact('level', 'jabatan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JabatanRequest $jabatanRequest, Jabatan $jabatan)
    {
        $jabatan->update($this->jabatanData());

        return redirect('/konfigurasi/jabatan/' . $jabatan->jabatan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jabatan sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jabatan $jabatan)
    {
        $jabatan->delete();

        return redirect('/konfigurasi/jabatan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jabatan sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function jabatanData()
    {
        return [
            'nama_jabatan' => request('nama_jabatan'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif') == 'true' ? true : false,
        ];
    }
}
