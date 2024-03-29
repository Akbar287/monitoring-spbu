<?php

namespace App\Http\Controllers;

use App\Http\Requests\LevelRequest;
use App\Models\Divisi;
use App\Models\Level;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Level::select('level.*')->addSelect('divisi.nama_divisi')->join('divisi', 'divisi.divisi_id', 'level.divisi_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == true ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('level.show', $row->level_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('konfigurasi/level/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $divisi = Divisi::all();
        return view('konfigurasi/level/create', compact('divisi'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LevelRequest $levelRequest)
    {
        $divisi = Divisi::find($levelRequest->divisi_id);
        $level = $divisi->level()->create($this->levelData());

        return redirect('/konfigurasi/level/' . $level->level_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Level sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Level $level)
    {
        return view('konfigurasi/level/show', compact('level'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Level $level)
    {
        $divisi = Divisi::all();
        return view('konfigurasi/level/edit', compact('divisi', 'level'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LevelRequest $levelRequest, Level $level)
    {
        $level->update($this->levelData());

        return redirect('/konfigurasi/level/' . $level->level_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Level sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Level $level)
    {
        $level->delete();

        return redirect('/konfigurasi/level')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Divisi sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function levelData()
    {
        return [
            'nama_level' => request('nama_level'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif') == 'true' ? true : false,
        ];
    }
}
