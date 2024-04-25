<?php

namespace App\Http\Controllers;

use App\Models\FilePenerimaan;
use App\Models\Penerimaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class FilePenerimaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Penerimaan $penerimaan)
    {
        if ($request->ajax()) {
            $data = $penerimaan->file_penerimaan()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_upload', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal_upload)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('penerimaan.file.show', [$row->penerimaan()->first()->penerimaan_id, $row->file_penerimaan_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('stok/file_penerimaan/index', compact('penerimaan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Penerimaan $penerimaan)
    {
        return view('stok/file_penerimaan/create', compact('penerimaan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Penerimaan $penerimaan)
    {
        $foto = 'default.png';
        $filenameWithExt = '';

        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_penerimaan', $foto);
        }

        $file = $penerimaan->file_penerimaan()->where('nama_file', $foto)->where('nama_dokumen', $filenameWithExt)->where('tanggal_upload', date('Y-m-d'))->first();
        if (is_null($file)) {
            $file = $penerimaan->file_penerimaan()->create($this->filePenerimaanData($foto, $filenameWithExt));
        }

        return redirect('/stok/penerimaan/verifikasi/' . $penerimaan->penerimaan_id . '/file/' . $file->file_penerimaan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Penerimaan sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Penerimaan $penerimaan, FilePenerimaan $file)
    {
        return view('stok/file_penerimaan/show', compact('penerimaan', 'file'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penerimaan $penerimaan, FilePenerimaan $file)
    {
        return view('stok/file_penerimaan/edit', compact('penerimaan', 'file'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penerimaan $penerimaan, FilePenerimaan $file)
    {
        $foto = $file->nama_file;
        $filenameWithExt = $file->nama_dokumen;

        if ($request->hasFile('gambar')) {
            if ($file->nama_file != 'default.png') {
                Storage::delete("public/file_penerimaan/" . $file->nama_file);
            }

            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_penerimaan', $foto);
        }
        $file->update($this->filePenerimaanData($foto, $filenameWithExt));

        return redirect('/stok/penerimaan/verifikasi/' . $penerimaan->penerimaan_id . '/file/' . $file->file_penerimaan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Penerimaan sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penerimaan $penerimaan, FilePenerimaan $file)
    {
        if ($file->nama_file != 'default.png') {
            Storage::delete("public/file_penerimaan/" . $file->nama_file);
        }
        $file->delete();

        return redirect('/stok/penerimaan/verifikasi/' . $penerimaan->penerimaan_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Penerimaan sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function filePenerimaanData($file, $dokumen)
    {
        return [
            'nama_file' => $file,
            'nama_dokumen' => $dokumen,
            'tanggal_upload' => date('Y-m-d')
        ];
    }

    public function download(Penerimaan $penerimaan, FilePenerimaan $file)
    {
        return Storage::download('public/file_penerimaan/' . $file->nama_file);
    }

    public function index_riwayat(Request $request, Penerimaan $penerimaan)
    {
        if ($request->ajax()) {
            $data = $penerimaan->file_penerimaan()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_upload', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal_upload)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('penerimaan.file.riwayat.show', [$row->penerimaan()->first()->penerimaan_id, $row->file_penerimaan_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('stok/file_penerimaan/index_riwayat', compact('penerimaan'));
    }

    public function show_riwayat(Penerimaan $penerimaan, FilePenerimaan $file)
    {
        return view('stok/file_penerimaan/show_riwayat', compact('penerimaan', 'file'));
    }
}
