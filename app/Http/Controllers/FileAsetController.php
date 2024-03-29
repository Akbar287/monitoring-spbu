<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\FileAset;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class FileAsetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Aset $aset)
    {
        if ($request->ajax()) {
            $data = FileAset::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('aset.file.show', [$row->aset()->first()->aset_id, $row->file_aset_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/aset/file/index', compact('aset'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Aset $aset)
    {
        return view('master/aset/file/create', compact('aset'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Aset $aset)
    {
        $foto = 'default.png';
        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/aset', $foto);
        }

        $fileAset = $aset->file_aset()->create($this->fileAsetData($foto, $filenameWithExt));

        return redirect('/master/aset/' . $aset->aset_id . '/file/' . $fileAset->file_aset_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Aset sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Aset $aset, FileAset $fileAset)
    {
        return view('master/aset/file/show', compact('aset', 'fileAset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Aset $aset, FileAset $fileAset)
    {
        return view('master/aset/file/edit', compact('aset', 'fileAset'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Aset $aset, FileAset $fileAset)
    {
        $foto = $fileAset->nama_file;
        $filenameWithExt = $fileAset->nama_dokumen;

        if ($request->hasFile('gambar')) {
            if ($fileAset->nama_file != 'default.png') {
                Storage::delete("public/aset/" . $fileAset->nama_file);
            }

            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/aset', $foto);
        }
        $fileAset->update($this->fileAsetData($foto, $filenameWithExt));

        return redirect('/master/aset/' . $aset->aset_id . '/file/' . $fileAset->file_aset_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Aset sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Aset $aset, FileAset $fileAset)
    {
        if ($fileAset->nama_file != 'default.png') {
            Storage::delete("public/aset/" . $fileAset->nama_file);
        }
        $fileAset->delete();

        return redirect('/master/aset/' . $aset->aset_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Aset sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function fileAsetData($file = null, $dokumen = null)
    {
        return [
            'nama_file' => $file != null ? $file : request('nama_file'),
            'nama_dokumen' => $dokumen != null ? $dokumen : request('nama_dokumen'),
            'tanggal_upload' => request('tanggal_upload'),
        ];
    }

    public function download(Aset $aset, FileAset $fileAset)
    {
        return Storage::download('public/aset/' . $fileAset->nama_file);
    }
}
