<?php

namespace App\Http\Controllers;

use App\Models\FileLampiranLo;
use App\Models\FileLo;
use App\Models\Pengiriman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class FileLampiranLoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Pengiriman $pengiriman, FileLo $file_lo)
    {
        if ($request->ajax()) {
            $data = FileLampiranLo::get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_upload', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal_upload)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pengiriman.file_lo_show.lampiran.show', [$row->file_lo()->first()->pengiriman_id, $row->file_lo()->first()->file_lo_id, $row->file_lampiran_lo_id]) . '" class="mr-2 edit btn btn-success btn-sm">Detail</a><a type="button" target="_blank" href="' . route('pengiriman.file_lo_show.lampiran.unduh', [$row->file_lo()->first()->pengiriman_id, $row->file_lo()->first()->file_lo_id, $row->file_lampiran_lo_id]) . '" class="btn btn-sm btn-info"><i class="fas fa-download"></i> Unduh</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('procurement/file_lampiran_lo/index', compact('pengiriman', 'file_lo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Pengiriman $pengiriman, FileLo $file_lo)
    {
        return view('procurement/file_lampiran_lo/create', compact('pengiriman', 'file_lo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Pengiriman $pengiriman, FileLo $file_lo)
    {
        $foto = 'default.png';
        $filenameWithExt = '';

        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_lampiran_lo', $foto);
        }

        $fileLampiranLo = $file_lo->file_lampiran_lo()->where('nama_file', $foto)->where('nama_dokumen', $filenameWithExt)->where('tanggal_upload', date('Y-m-d'))->first();
        if (is_null($fileLampiranLo)) {
            $fileLampiranLo = $file_lo->file_lampiran_lo()->create($this->fileLampiranLo($foto, $filenameWithExt));
        }

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id . '/file_lo_show/' . $file_lo->file_lo_id . '/lampiran/' . $fileLampiranLo->file_lampiran_lo)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Lampiran Lo sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pengiriman $pengiriman, FileLo $file_lo, FileLampiranLo $fileLampiranLo)
    {
        return view('procurement/file_lampiran_lo/show', compact('pengiriman', 'file_lo', 'fileLampiranLo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pengiriman $pengiriman, FileLo $file_lo, FileLampiranLo $fileLampiranLo)
    {
        return view('procurement/file_lampiran_lo/edit', compact('pengiriman', 'file_lo', 'fileLampiranLo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pengiriman $pengiriman, FileLo $file_lo, FileLampiranLo $fileLampiranLo)
    {

        $foto = $fileLampiranLo->nama_file;
        $filenameWithExt = $fileLampiranLo->nama_dokumen;

        if ($request->hasFile('file')) {
            if ($fileLampiranLo->nama_file != 'default.png') {
                Storage::delete("public/file_lampiran_lo/" . $fileLampiranLo->nama_file);
            }

            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('file')->storeAs('public/file_lampiran_lo', $foto);
        }
        $fileLampiranLo->update($this->fileLampiranLo($foto, $filenameWithExt));

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id . '/file_lo_show/' . $file_lo->file_lo_id . '/lampiran/' . $fileLampiranLo->file_lampiran_lo)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Lampiran Lo sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pengiriman $pengiriman, FileLo $file_lo, FileLampiranLo $fileLampiranLo)
    {
        if ($fileLampiranLo->nama_file != 'default.png') {
            Storage::delete("public/file_lampiran_lo/" . $fileLampiranLo->nama_file);
        }
        $fileLampiranLo->delete();

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id . '/file_lo_show/' . $file_lo->file_lo_id . '/lampiran')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Lampiran Lo sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function fileLampiranLo($file, $dok)
    {
        return [
            'nama_file' => $file,
            'nama_dokumen' => $dok,
            'tanggal_upload' => date('Y-m-d')
        ];
    }

    public function unduh(Pengiriman $pengiriman, FileLo $file_lo, FileLampiranLo $fileLampiranLo)
    {
        return Storage::download('public/file_lampiran_lo/' . $fileLampiranLo->nama_file);
    }
}
