<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\DokumenStok;
use App\Models\FileDokumenStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class FileDokumenStokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DokumenStok $stok)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $stok->file_dokumen_stok()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('stok-taking.file.show', [$row->dokumen_stok()->first()->dokumen_stok_id, $row->file_dokumen_stok_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'File Stok Taking',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Stok Taking', 'url' => route('stok-taking.index')]],
            'createUrl' => route('stok-taking.file.create', $stok),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Dokumen', 'data' => 'nama_dokumen'],
                    ['label' => 'Tanggal Upload', 'data' => 'tanggal_upload'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(DokumenStok $stok)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File Stok Taking',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Stok Taking', 'url' => route('stok-taking.index')], ['label' => 'File Stok Taking', 'url' => route('stok-taking.file.index', $stok)]],
            'backUrl' => route('stok-taking.file.index', $stok),
            'method' => 'post',
            'action' => route('stok-taking.file.store', $stok),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DokumenStok $stok)
    {
        $request->validate(['gambar' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = 'default.png';
        $filenameWithExt = null;

        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_dokumen_stok', $foto);
        }

        $file = $stok->file_dokumen_stok()->where('tanggal_upload', date('Y-m-d'))->where('nama_dokumen', $filenameWithExt)->where('nama_file', $foto)->first();
        if (is_null($file)) {
            $file = $stok->file_dokumen_stok()->create($this->fileDokumenStokData($filenameWithExt, $foto));
        }
        return redirect('/stok/stok-taking/' . $stok->dokumen_stok_id . '/file/' . $file->file_dokumen_stok_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Fasilitas sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(DokumenStok $stok, FileDokumenStok $file)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail File Stok Taking',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Stok Taking', 'url' => route('stok-taking.index')], ['label' => 'File Stok Taking', 'url' => route('stok-taking.file.index', $stok)]],
            'backUrl' => route('stok-taking.file.index', $stok),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $file->nama_dokumen],
                ['label' => 'Nama File', 'value' => $file->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $file->tanggal_upload],
            ],
            'image' => asset('storage/file_dokumen_stok/' . $file->nama_file),
            'actions' => [
                ['label' => 'Ubah', 'url' => route('stok-taking.file.edit', [$stok, $file]), 'color' => 'primary', 'icon' => 'pen'],
                ['label' => 'Download', 'url' => route('stok-taking.file.download', [$stok, $file]), 'color' => 'info', 'icon' => 'download'],
            ],
            'deleteUrl' => route('stok-taking.file.delete', [$stok, $file]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DokumenStok $stok, FileDokumenStok $file)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah File Stok Taking',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Stok Taking', 'url' => route('stok-taking.index')], ['label' => 'File Stok Taking', 'url' => route('stok-taking.file.index', $stok)]],
            'backUrl' => route('stok-taking.file.show', [$stok, $file]),
            'method' => 'put',
            'action' => route('stok-taking.file.update', [$stok, $file]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $file->nama_dokumen],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DokumenStok $stok, FileDokumenStok $file)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = $file->nama_file;
        $filenameWithExt = $file->nama_dokumen;

        if ($request->hasFile('gambar')) {
            if ($file->nama_file != 'default.png') {
                Storage::delete("public/file_dokumen_stok/" . $file->nama_file);
            }

            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_dokumen_stok', $foto);
        }
        $file->update($this->fileDokumenStokData($filenameWithExt, $foto));

        return redirect('/stok/stok-taking/' . $stok->dokumen_stok_id . '/file/' . $file->file_dokumen_stok_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File Dokumen Stok sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DokumenStok $stok, FileDokumenStok $file)
    {
        if ($file->nama_file != 'default.png') {
            Storage::delete("public/file_dokumen_stok/" . $file->nama_file);
        }
        $file->delete();

        return redirect('/stok/stok-taking/' . $stok->dokumen_stok_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File Dokumen Stok sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function download(DokumenStok $stok, FileDokumenStok $file)
    {
        return Storage::download('public/file_dokumen_stok/' . $file->nama_file);
    }

    public function fileDokumenStokData($namaDokumen, $namaFile)
    {
        return [
            'tanggal_upload' => date('Y-m-d'),
            'nama_dokumen' => $namaDokumen,
            'nama_file' => $namaFile
        ];
    }
}
