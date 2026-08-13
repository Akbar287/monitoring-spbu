<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\FileAset;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class FileAsetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Aset $aset)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $aset->file_aset()->select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('aset.file.show', [$row->aset()->first()->aset_id, $row->file_aset_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'File Aset',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Aset', 'url' => route('aset.index')], ['label' => $aset->nama_aset, 'url' => route('aset.show', $aset)]],
            'createUrl' => route('aset.file.create', $aset),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama File', 'data' => 'nama_file'],
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
    public function create(Aset $aset)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File Aset',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Aset', 'url' => route('aset.index')], ['label' => 'File Aset', 'url' => route('aset.file.index', $aset)]],
            'backUrl' => route('aset.file.index', $aset),
            'method' => 'post',
            'action' => route('aset.file.store', $aset),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Aset $aset)
    {
        $request->validate(['gambar' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = 'default.png';
        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/aset', $foto);
        }

        $fileAset = $aset->file_aset()->where('nama_file', $foto != null ? $foto : request('nama_file'))->where('nama_dokumen', $filenameWithExt != null ? $filenameWithExt : request('nama_dokumen'))->where('tanggal_upload', request('tanggal_upload'))->first();
        if (is_null($fileAset)) {
            $fileAset = $aset->file_aset()->create($this->fileAsetData($foto, $filenameWithExt));
        }

        return redirect('/master/aset/' . $aset->aset_id . '/file/' . $fileAset->file_aset_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Aset sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Aset $aset, FileAset $fileAset)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail File Aset',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Aset', 'url' => route('aset.index')], ['label' => 'File Aset', 'url' => route('aset.file.index', $aset)]],
            'backUrl' => route('aset.file.index', $aset),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $fileAset->nama_dokumen],
                ['label' => 'Nama File', 'value' => $fileAset->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $fileAset->tanggal_upload],
            ],
            'image' => route('aset.file.download', [$aset, $fileAset, 'preview' => 1]),
            'actions' => [
                ['label' => 'Ubah', 'url' => route('aset.file.edit', [$aset, $fileAset]), 'color' => 'primary', 'icon' => 'pen'],
                ['label' => 'Download', 'url' => route('aset.file.download', [$aset, $fileAset]), 'color' => 'info', 'icon' => 'download'],
            ],
            'deleteUrl' => route('aset.file.delete', [$aset, $fileAset]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Aset $aset, FileAset $fileAset)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah File Aset',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Aset', 'url' => route('aset.index')], ['label' => 'File Aset', 'url' => route('aset.file.index', $aset)]],
            'backUrl' => route('aset.file.show', [$aset, $fileAset]),
            'method' => 'put',
            'action' => route('aset.file.update', [$aset, $fileAset]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $fileAset->nama_dokumen],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Aset $aset, FileAset $fileAset)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

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
            'tanggal_upload' => request('tanggal_upload') ?? date('Y-m-d'),
        ];
    }

    public function download(Request $request, Aset $aset, FileAset $fileAset)
    {
        $path = 'public/aset/' . $fileAset->nama_file;
        $name = $fileAset->nama_dokumen ?: $fileAset->nama_file;

        if (! Storage::exists($path)) {
            abort(404);
        }

        if ($request->boolean('preview')) {
            return Storage::response($path, $name, [], 'inline');
        }

        return Storage::download($path, $name);
    }
}
