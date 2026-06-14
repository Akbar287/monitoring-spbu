<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use App\Models\FileFasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class FileFasilitasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Fasilitas $fasilitas)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $fasilitas->file_fasilitas()->select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('fasilitas.file.show', [$row->fasilitas()->first()->fasilitas_id, $row->file_fasilitas_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'File Fasilitas',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Fasilitas', 'url' => route('fasilitas.index')], ['label' => $fasilitas->nama_fasilitas, 'url' => route('fasilitas.show', $fasilitas)]],
            'createUrl' => route('fasilitas.file.create', $fasilitas),
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
    public function create(Fasilitas $fasilitas)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File Fasilitas',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Fasilitas', 'url' => route('fasilitas.index')], ['label' => 'File Fasilitas', 'url' => route('fasilitas.file.index', $fasilitas)]],
            'backUrl' => route('fasilitas.file.index', $fasilitas),
            'method' => 'post',
            'action' => route('fasilitas.file.store', $fasilitas),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Fasilitas $fasilitas)
    {
        $request->validate(['gambar' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

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
        return Inertia::render('Show/Index', [
            'title' => 'Detail File Fasilitas',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Fasilitas', 'url' => route('fasilitas.index')], ['label' => 'File Fasilitas', 'url' => route('fasilitas.file.index', $fasilitas)]],
            'backUrl' => route('fasilitas.file.index', $fasilitas),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $fileFasilitas->nama_dokumen],
                ['label' => 'Nama File', 'value' => $fileFasilitas->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $fileFasilitas->tanggal_upload],
            ],
            'image' => asset('storage/fasilitas/' . $fileFasilitas->nama_file),
            'actions' => [
                ['label' => 'Ubah', 'url' => route('fasilitas.file.edit', [$fasilitas, $fileFasilitas]), 'color' => 'primary', 'icon' => 'pen'],
                ['label' => 'Download', 'url' => route('fasilitas.file.download', [$fasilitas, $fileFasilitas]), 'color' => 'info', 'icon' => 'download'],
            ],
            'deleteUrl' => route('fasilitas.file.delete', [$fasilitas, $fileFasilitas]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fasilitas $fasilitas, FileFasilitas $fileFasilitas)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah File Fasilitas',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Fasilitas', 'url' => route('fasilitas.index')], ['label' => 'File Fasilitas', 'url' => route('fasilitas.file.index', $fasilitas)]],
            'backUrl' => route('fasilitas.file.show', [$fasilitas, $fileFasilitas]),
            'method' => 'put',
            'action' => route('fasilitas.file.update', [$fasilitas, $fileFasilitas]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $fileFasilitas->nama_dokumen],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fasilitas $fasilitas, FileFasilitas $fileFasilitas)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

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
            'tanggal_upload' => request('tanggal_upload') ?? date('Y-m-d'),
        ];
    }

    public function download(Fasilitas $fasilitas, FileFasilitas $fileFasilitas)
    {
        return Storage::download('public/fasilitas/' . $fileFasilitas->nama_file);
    }
}
