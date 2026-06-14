<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
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
        if ($request->ajax() && ! $request->inertia()) {
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
        return Inertia::render('Table/Index', [
            'title' => 'File Petty Cash',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Petty Cash', 'url' => route('financial.petty-cash')]],
            'createUrl' => route('petty-cash.file.create', $pettyCash),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Nama Dokumen', 'data' => 'nama_dokumen'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(PettyCash $pettyCash)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File Petty Cash',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Petty Cash', 'url' => route('financial.petty-cash')], ['label' => 'File Petty Cash', 'url' => route('financial.petty-cash.file', $pettyCash)]],
            'backUrl' => route('financial.petty-cash.file', $pettyCash),
            'method' => 'post',
            'action' => route('petty-cash.file.store', $pettyCash),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PettyCash $pettyCash)
    {
        $request->validate(['gambar' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

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
        return Inertia::render('Show/Index', [
            'title' => 'Detail File Petty Cash',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Petty Cash', 'url' => route('financial.petty-cash')], ['label' => 'File Petty Cash', 'url' => route('financial.petty-cash.file', $pettyCash)]],
            'backUrl' => route('financial.petty-cash.file', $pettyCash),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $file->nama_dokumen],
                ['label' => 'Nama File', 'value' => $file->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $file->tanggal_upload],
            ],
            'image' => asset('storage/file_petty_cash/' . $file->nama_file),
            'actions' => [
                ['label' => 'Ubah', 'url' => route('petty-cash.file.edit', [$pettyCash, $file]), 'color' => 'primary', 'icon' => 'pen'],
                ['label' => 'Download', 'url' => route('petty-cash.file.download', [$pettyCash, $file]), 'color' => 'info', 'icon' => 'download'],
            ],
            'deleteUrl' => route('petty-cash.file.delete', [$pettyCash, $file]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PettyCash $pettyCash, FilePettyCash $file)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah File Petty Cash',
            'breadcrumbs' => [['label' => 'Financial', 'url' => route('menu.financial')], ['label' => 'Petty Cash', 'url' => route('financial.petty-cash')], ['label' => 'File Petty Cash', 'url' => route('financial.petty-cash.file', $pettyCash)]],
            'backUrl' => route('petty-cash.file.show', [$pettyCash, $file]),
            'method' => 'put',
            'action' => route('petty-cash.file.update', [$pettyCash, $file]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $file->nama_dokumen],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PettyCash $pettyCash, FilePettyCash $file)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

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
