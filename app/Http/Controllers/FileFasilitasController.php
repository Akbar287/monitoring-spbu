<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use App\Models\FileFasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class FileFasilitasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Fasilitas $fasilitas)
    {
        if ($request->ajax()) {
            $data = FileFasilitas::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('fasilitas.file.show', [$row->fasilitas()->first()->fasilitas_id, $row->file_fasilitas_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/fasilitas/file/index', compact('fasilitas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Fasilitas $fasilitas)
    {
        return view('master/fasilitas/file/create', compact('fasilitas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Fasilitas $fasilitas)
    {
        $foto = 'default.png';
        $filenameWithExt = '';

        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/fasilitas', $foto);
        }

        $fileFasilitas = $fasilitas->file_fasilitas()->where('nama_file', $foto != null ? $foto : request('nama_file'))->where('nama_dokumen', $filenameWithExt != null ? $filenameWithExt : request('nama_dokumen'))->where('tanggal_upload', request('tanggal_upload'))->first();
        if (is_null($fileFasilitas)) {
            $fileFasilitas = $fasilitas->file_fasilitas()->create($this->fileFasilitasData($foto, $filenameWithExt));
        }

        return redirect('/master/fasilitas/' . $fasilitas->fasilitas_id . '/file/' . $fileFasilitas->file_fasilitas_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Fasilitas sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fasilitas $fasilitas, FileFasilitas $fileFasilitas)
    {
        return view('master/fasilitas/file/show', compact('fileFasilitas', 'fasilitas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fasilitas $fasilitas, FileFasilitas $fileFasilitas)
    {
        return view('master/fasilitas/file/edit', compact('fasilitas', 'fileFasilitas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fasilitas $fasilitas, FileFasilitas $fileFasilitas)
    {
        $foto = $fileFasilitas->nama_file;
        $filenameWithExt = $fileFasilitas->nama_dokumen;

        if ($request->hasFile('gambar')) {
            if ($fileFasilitas->nama_file != 'default.png') {
                Storage::delete("public/fasilitas/" . $fileFasilitas->nama_file);
            }

            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/fasilitas', $foto);
        }
        $fileFasilitas->update($this->fileFasilitasData($foto, $filenameWithExt));

        return redirect('/master/fasilitas/' . $fasilitas->fasilitas_id . '/file/' . $fileFasilitas->file_fasilitas_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Fasilitas sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fasilitas $fasilitas, FileFasilitas $fileFasilitas)
    {
        if ($fileFasilitas->nama_file != 'default.png') {
            Storage::delete("public/fasilitas/" . $fileFasilitas->nama_file);
        }
        $fileFasilitas->delete();

        return redirect('/master/fasilitas/' . $fasilitas->fasilitas_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Fasilitas sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function fileFasilitasData($file = null, $dokumen = null)
    {
        return [
            'nama_file' => $file != null ? $file : request('nama_file'),
            'nama_dokumen' => $dokumen != null ? $dokumen : request('nama_dokumen'),
            'tanggal_upload' => request('tanggal_upload'),
        ];
    }

    public function download(Fasilitas $fasilitas, FileFasilitas $fileFasilitas)
    {
        return Storage::download('public/fasilitas/' . $fileFasilitas->nama_file);
    }
}
