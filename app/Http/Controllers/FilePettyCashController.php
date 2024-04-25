<?php

namespace App\Http\Controllers;

use App\Models\FilePettyCash;
use App\Models\PettyCash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class FilePettyCashController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PettyCash $pettyCash)
    {
        if ($request->ajax()) {
            $data = $pettyCash->file_petty_cash()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return $row->tanggal_upload;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('petty-cash.file.show', [$row->petty_cash()->first()->petty_cash_id, $row->file_petty_cash_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('financial/petty_cash/file/index', compact('pettyCash'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(PettyCash $pettyCash)
    {
        return view('financial/petty_cash/file/create', compact('pettyCash'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PettyCash $pettyCash)
    {
        $foto = 'default.png';
        $filenameWithExt = '';

        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_petty_cash', $foto);
        }

        $file = $pettyCash->file_petty_cash()->where('nama_file', $foto)->where('nama_dokumen', $filenameWithExt)->where('tanggal_upload', date('Y-m-d'))->first();
        if (is_null($file)) {
            $file = $pettyCash->file_petty_cash()->create($this->filePettyCashData($foto, $filenameWithExt));
        }

        return redirect('/financial/petty-cash/' . $pettyCash->petty_cash_id . '/file/' . $file->file_petty_cash_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Petty Cash sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(PettyCash $pettyCash, FilePettyCash $file)
    {
        return view('financial/petty_cash/file/show', compact('pettyCash', 'file'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PettyCash $pettyCash, FilePettyCash $file)
    {
        return view('financial/petty_cash/file/edit', compact('pettyCash', 'file'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PettyCash $pettyCash, FilePettyCash $file)
    {
        $foto = $file->nama_file;
        $filenameWithExt = $file->nama_dokumen;

        if ($request->hasFile('gambar')) {
            if ($file->nama_file != 'default.png') {
                Storage::delete("public/file_petty_cash/" . $file->nama_file);
            }

            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_petty_cash', $foto);
        }
        $file->update($this->filePettyCashData($foto, $filenameWithExt));

        return redirect('/financial/petty-cash/' . $pettyCash->petty_cash_id . '/file/' . $file->file_petty_cash_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Petty Cash sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PettyCash $pettyCash, FilePettyCash $file)
    {
        if ($file->nama_file != 'default.png') {
            Storage::delete("public/file_petty_cash/" . $file->nama_file);
        }
        $file->delete();

        return redirect('/financial/petty-cash/' . $pettyCash->petty_cash_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Petty Cash sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function filePettyCashData($file, $dokumen)
    {
        return [
            'nama_file' => $file,
            'nama_dokumen' => $dokumen,
            'tanggal_upload' => date('Y-m-d')
        ];
    }

    public function download(PettyCash $pettyCash, FilePettyCash $file)
    {
        return Storage::download('public/file_petty_cash/' . $file->nama_file);
    }
}
