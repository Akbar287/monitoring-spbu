<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
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
        if ($request->ajax() && ! $request->inertia()) {
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
        return Inertia::render('Table/Index', [
            'title' => 'File Penerimaan',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Penerimaan BBM', 'url' => route('penerimaan.index')]],
            'createUrl' => route('penerimaan.file.create', $penerimaan),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Id File', 'data' => 'file_penerimaan_id'],
                    ['label' => 'Nama Dokumen', 'data' => 'nama_dokumen'],
                    ['label' => 'Tanggal', 'data' => 'tanggal_upload'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Penerimaan $penerimaan)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File Penerimaan',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Penerimaan BBM', 'url' => route('penerimaan.index')], ['label' => 'File Penerimaan', 'url' => route('penerimaan.file.index', $penerimaan)]],
            'backUrl' => route('penerimaan.file.index', $penerimaan),
            'method' => 'post',
            'action' => route('penerimaan.file.store', $penerimaan),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Penerimaan $penerimaan)
    {
        $request->validate(['gambar' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

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
        return Inertia::render('Show/Index', [
            'title' => 'Detail File Penerimaan',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Penerimaan BBM', 'url' => route('penerimaan.index')], ['label' => 'File Penerimaan', 'url' => route('penerimaan.file.index', $penerimaan)]],
            'backUrl' => route('penerimaan.file.index', $penerimaan),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $file->nama_dokumen],
                ['label' => 'Nama File', 'value' => $file->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $file->tanggal_upload],
            ],
            'image' => asset('storage/file_penerimaan/' . $file->nama_file),
            'actions' => [['label' => 'Ubah', 'url' => route('penerimaan.file.edit', [$penerimaan, $file]), 'color' => 'primary', 'icon' => 'pen'], ['label' => 'Download', 'url' => route('penerimaan.file.download', [$penerimaan, $file]), 'color' => 'info', 'icon' => 'download']],
            'deleteUrl' => route('penerimaan.file.delete', [$penerimaan, $file]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penerimaan $penerimaan, FilePenerimaan $file)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah File Penerimaan',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Penerimaan BBM', 'url' => route('penerimaan.index')], ['label' => 'File Penerimaan', 'url' => route('penerimaan.file.index', $penerimaan)]],
            'backUrl' => route('penerimaan.file.show', [$penerimaan, $file]),
            'method' => 'put',
            'action' => route('penerimaan.file.update', [$penerimaan, $file]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $file->nama_dokumen],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penerimaan $penerimaan, FilePenerimaan $file)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

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
        if ($request->ajax() && ! $request->inertia()) {
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
        return Inertia::render('Table/Index', [
            'title' => 'Riwayat File Penerimaan',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Riwayat Penerimaan BBM', 'url' => route('penerimaan.riwayat')]],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Id File', 'data' => 'file_penerimaan_id'],
                    ['label' => 'Nama Dokumen', 'data' => 'nama_dokumen'],
                    ['label' => 'Tanggal', 'data' => 'tanggal_upload'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function show_riwayat(Penerimaan $penerimaan, FilePenerimaan $file)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Riwayat File Penerimaan',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Riwayat Penerimaan BBM', 'url' => route('penerimaan.riwayat')], ['label' => 'Riwayat File Penerimaan', 'url' => route('penerimaan.file.riwayat.index', $penerimaan)]],
            'backUrl' => route('penerimaan.file.riwayat.index', $penerimaan),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $file->nama_dokumen],
                ['label' => 'Nama File', 'value' => $file->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $file->tanggal_upload],
            ],
            'image' => asset('storage/file_penerimaan/' . $file->nama_file),
        ]);
    }
}
