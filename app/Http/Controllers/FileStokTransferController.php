<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\DokumenStok;
use App\Models\DombakTransfer;
use App\Models\FileDokumenStok;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class FileStokTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DombakTransfer $dombakTransfer, DokumenStok $dokumenStok)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $dokumenStok->file_dokumen_stok()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_dokumen', function ($row) {
                    return $row->nama_dokumen;
                })
                ->addColumn('tanggal_upload', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal_upload)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('stok-transfer.file.show', [$row->dokumen_stok()->first()->dombak_transfer()->first()->dombak_transfer_id, $row->dokumen_stok()->first()->dokumen_stok_id, $row->file_dokumen_stok_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'File Stok Transfer',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Stok Transfer', 'url' => route('stok-transfer.index')]],
            'createUrl' => route('stok-transfer.file.create', [$dombakTransfer, $dokumenStok]),
            'table' => [
                'url' => $request->url(),
                'columns' => [
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
    public function create(DombakTransfer $dombakTransfer, DokumenStok $dokumenStok)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File Stok Transfer',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Stok Transfer', 'url' => route('stok-transfer.index')], ['label' => 'File Stok Transfer', 'url' => route('stok-transfer.file.index', [$dombakTransfer, $dokumenStok])]],
            'backUrl' => route('stok-transfer.file.index', [$dombakTransfer, $dokumenStok]),
            'method' => 'post',
            'action' => route('stok-transfer.file.store', [$dombakTransfer, $dokumenStok]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DombakTransfer $dombakTransfer, DokumenStok $dokumenStok)
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

        $file = $dokumenStok->file_dokumen_stok()->where('tanggal_upload', date('Y-m-d'))->where('nama_dokumen', $filenameWithExt)->where('nama_file', $foto)->first();
        if (is_null($file)) {
            $file = $dokumenStok->file_dokumen_stok()->create($this->fileDokumenStokData($filenameWithExt, $foto));
        }

        return redirect('/stok/stok-transfer/' . $dombakTransfer->dombak_transfer_id . '/stok/' . $dokumenStok->dokumen_stok_id . '/file/' . $file->file_dokumen_stok_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Dombak Transfer sudah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(DombakTransfer $dombakTransfer, DokumenStok $dokumenStok, FileDokumenStok $fileDokumenStok)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail File Stok Transfer',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Stok Transfer', 'url' => route('stok-transfer.index')], ['label' => 'File Stok Transfer', 'url' => route('stok-transfer.file.index', [$dombakTransfer, $dokumenStok])]],
            'backUrl' => route('stok-transfer.file.index', [$dombakTransfer, $dokumenStok]),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $fileDokumenStok->nama_dokumen],
                ['label' => 'Nama File', 'value' => $fileDokumenStok->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $fileDokumenStok->tanggal_upload],
            ],
            'image' => asset('storage/file_dokumen_stok/' . $fileDokumenStok->nama_file),
            'actions' => [['label' => 'Ubah', 'url' => route('stok-transfer.file.edit', [$dombakTransfer, $dokumenStok, $fileDokumenStok]), 'color' => 'primary', 'icon' => 'pen'], ['label' => 'Download', 'url' => route('stok-transfer.file.download', [$dombakTransfer, $dokumenStok, $fileDokumenStok]), 'color' => 'info', 'icon' => 'download']],
            'deleteUrl' => route('stok-transfer.file.delete', [$dombakTransfer, $dokumenStok, $fileDokumenStok]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DombakTransfer $dombakTransfer, DokumenStok $dokumenStok, FileDokumenStok $fileDokumenStok)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah File Stok Transfer',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Stok Transfer', 'url' => route('stok-transfer.index')], ['label' => 'File Stok Transfer', 'url' => route('stok-transfer.file.index', [$dombakTransfer, $dokumenStok])]],
            'backUrl' => route('stok-transfer.file.show', [$dombakTransfer, $dokumenStok, $fileDokumenStok]),
            'method' => 'put',
            'action' => route('stok-transfer.file.update', [$dombakTransfer, $dokumenStok, $fileDokumenStok]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $fileDokumenStok->nama_dokumen],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DombakTransfer $dombakTransfer, DokumenStok $dokumenStok, FileDokumenStok $fileDokumenStok)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = $fileDokumenStok->nama_file;
        $filenameWithExt = $fileDokumenStok->nama_dokumen;

        if ($request->hasFile('gambar')) {
            if ($fileDokumenStok->nama_file != 'default.png') {
                Storage::delete("public/file_dokumen_stok/" . $fileDokumenStok->nama_file);
            }

            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_dokumen_stok', $foto);
        }
        $fileDokumenStok->update($this->fileDokumenStokData($filenameWithExt, $foto));
        return redirect('/stok/stok-transfer/' . $dombakTransfer->dombak_transfer_id . '/stok/' . $dokumenStok->dokumen_stok_id . '/file/' . $fileDokumenStok->file_dokumen_stok_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Dombak Transfer sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DombakTransfer $dombakTransfer, DokumenStok $dokumenStok, FileDokumenStok $fileDokumenStok)
    {
        if ($fileDokumenStok->nama_file != 'default.png') {
            Storage::delete("public/file_dokumen_stok/" . $fileDokumenStok->nama_file);
        }
        $fileDokumenStok->delete();

        return redirect('/stok/stok-transfer/' . $dombakTransfer->dombak_transfer_id . '/stok/' . $dokumenStok->dokumen_stok_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Dombak Transfer sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
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
