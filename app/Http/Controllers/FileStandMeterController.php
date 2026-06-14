<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\FileStandMeter;
use App\Models\StandMeter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class FileStandMeterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, StandMeter $standMeter)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $standMeter->file_stand_meter()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('totalisator.file.show', [$row->stand_meter()->first()->stand_meter_id, $row->file_stand_meter_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'File Totalisator',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Totalisator', 'url' => route('totalisator.index')]],
            'createUrl' => route('totalisator.file.create', $standMeter),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Id File', 'data' => 'file_stand_meter_id'],
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
    public function create(StandMeter $standMeter)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File Totalisator',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Totalisator', 'url' => route('totalisator.index')], ['label' => 'File Totalisator', 'url' => route('totalisator.file.index', $standMeter)]],
            'backUrl' => route('totalisator.file.index', $standMeter),
            'method' => 'post',
            'action' => route('totalisator.file.store', $standMeter),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, StandMeter $standMeter)
    {
        $request->validate(['gambar' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = 'default.png';
        $filenameWithExt = 'default.png';
        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_stand_meter', $foto);
        }

        $file = $standMeter->file_stand_meter()->where('nama_file', $foto)->where('nama_dokumen', $filenameWithExt)->where('tanggal_upload', date('Y-m-d'))->first();
        if (is_null($file)) {
            $file = $standMeter->file_stand_meter()->create($this->fileStandMeterData($foto, $filenameWithExt));
        }

        return redirect('/stok/totalisator/' . $standMeter->stand_meter_id . '/file/' . $file->file_stand_meter_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File Stand Meter sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(StandMeter $standMeter, FileStandMeter $file)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail File Totalisator',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Totalisator', 'url' => route('totalisator.index')], ['label' => 'File Totalisator', 'url' => route('totalisator.file.index', $standMeter)]],
            'backUrl' => route('totalisator.file.index', $standMeter),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $file->nama_dokumen],
                ['label' => 'Nama File', 'value' => $file->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $file->tanggal_upload],
            ],
            'image' => asset('storage/file_stand_meter/' . $file->nama_file),
            'actions' => [['label' => 'Ubah', 'url' => route('totalisator.file.edit', [$standMeter, $file]), 'color' => 'primary', 'icon' => 'pen'], ['label' => 'Download', 'url' => route('totalisator.file.download', [$standMeter, $file]), 'color' => 'info', 'icon' => 'download']],
            'deleteUrl' => route('totalisator.file.delete', [$standMeter, $file]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StandMeter $standMeter, FileStandMeter $file)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah File Totalisator',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Totalisator', 'url' => route('totalisator.index')], ['label' => 'File Totalisator', 'url' => route('totalisator.file.index', $standMeter)]],
            'backUrl' => route('totalisator.file.show', [$standMeter, $file]),
            'method' => 'put',
            'action' => route('totalisator.file.update', [$standMeter, $file]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $file->nama_dokumen],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StandMeter $standMeter, FileStandMeter $file)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = $file->nama_file;
        $filenameWithExt = $file->nama_dokumen;

        if ($request->hasFile('gambar')) {
            if ($file->nama_file != 'default.png') {
                Storage::delete("public/file_stand_meter/" . $file->nama_file);
            }

            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_stand_meter', $foto);
        }

        $file->update($this->fileStandMeterData($foto, $filenameWithExt));

        return redirect('/stok/totalisator/' . $standMeter->stand_meter_id . '/file/' . $file->file_stand_meter_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File Stand Meter sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StandMeter $standMeter, FileStandMeter $file)
    {
        if ($file->nama_file != 'default.png') {
            Storage::delete("public/file_stand_meter/" . $file->nama_file);
        }

        $file->delete();

        return redirect('/stok/totalisator/' . $standMeter->stand_meter_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File Stand Meter sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function fileStandMeterData($foto, $dokumen)
    {
        return [
            'nama_file' => $foto,
            'nama_dokumen' => $dokumen,
            'tanggal_upload' => date('Y-m-d')
        ];
    }

    public function riwayat_index(Request $request, StandMeter $standMeter)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $standMeter->file_stand_meter()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('totalisator.file.riwayat.show', [$row->stand_meter()->first()->stand_meter_id, $row->file_stand_meter_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Riwayat File Totalisator',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Riwayat Totalisator', 'url' => route('totalisator.riwayat')]],
            'createUrl' => route('totalisator.file.riwayat.create', $standMeter),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Id File', 'data' => 'file_stand_meter_id'],
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
    public function riwayat_create(StandMeter $standMeter)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Riwayat File Totalisator',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Riwayat Totalisator', 'url' => route('totalisator.riwayat')], ['label' => 'Riwayat File Totalisator', 'url' => route('totalisator.file.riwayat.index', $standMeter)]],
            'backUrl' => route('totalisator.file.riwayat.index', $standMeter),
            'method' => 'post',
            'action' => route('totalisator.file.riwayat.store', $standMeter),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function riwayat_store(Request $request, StandMeter $standMeter)
    {
        $request->validate(['gambar' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = 'default.png';
        $filenameWithExt = 'default.png';
        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_stand_meter', $foto);
        }

        $file = $standMeter->file_stand_meter()->where('nama_file', $foto)->where('nama_dokumen', $filenameWithExt)->where('tanggal_upload', date('Y-m-d'))->first();
        if (is_null($file)) {
            $file = $standMeter->file_stand_meter()->create($this->fileStandMeterData($foto, $filenameWithExt));
        }

        return redirect('/stok/totalisator/riwayat/' . $standMeter->stand_meter_id . '/file/' . $file->file_stand_meter_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File Stand Meter sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function riwayat_show(StandMeter $standMeter, FileStandMeter $file)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Riwayat File Totalisator',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Riwayat Totalisator', 'url' => route('totalisator.riwayat')], ['label' => 'Riwayat File Totalisator', 'url' => route('totalisator.file.riwayat.index', $standMeter)]],
            'backUrl' => route('totalisator.file.riwayat.index', $standMeter),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $file->nama_dokumen],
                ['label' => 'Nama File', 'value' => $file->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $file->tanggal_upload],
            ],
            'image' => asset('storage/file_stand_meter/' . $file->nama_file),
            'actions' => [['label' => 'Ubah', 'url' => route('totalisator.file.riwayat.edit', [$standMeter, $file]), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('totalisator.file.riwayat.delete', [$standMeter, $file]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function riwayat_edit(StandMeter $standMeter, FileStandMeter $file)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Riwayat File Totalisator',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Riwayat Totalisator', 'url' => route('totalisator.riwayat')], ['label' => 'Riwayat File Totalisator', 'url' => route('totalisator.file.riwayat.index', $standMeter)]],
            'backUrl' => route('totalisator.file.riwayat.show', [$standMeter, $file]),
            'method' => 'put',
            'action' => route('totalisator.file.riwayat.update', [$standMeter, $file]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $file->nama_dokumen],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function riwayat_update(Request $request, StandMeter $standMeter, FileStandMeter $file)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = $file->nama_file;
        $filenameWithExt = $file->nama_dokumen;

        if ($request->hasFile('gambar')) {
            if ($file->nama_file != 'default.png') {
                Storage::delete("public/file_stand_meter/" . $file->nama_file);
            }

            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_stand_meter', $foto);
        }

        $file->update($this->fileStandMeterData($foto, $filenameWithExt));

        return redirect('/stok/totalisator/riwayat/' . $standMeter->stand_meter_id . '/file/' . $file->file_stand_meter_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File Stand Meter sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function riwayat_destroy(StandMeter $standMeter, FileStandMeter $file)
    {
        if ($file->nama_file != 'default.png') {
            Storage::delete("public/file_stand_meter/" . $file->nama_file);
        }

        $file->delete();

        return redirect('/stok/totalisator/riwayat/' . $standMeter->stand_meter_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File Stand Meter sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function download(StandMeter $standMeter, FileStandMeter $file)
    {
        return Storage::download('public/file_stand_meter/' . $file->nama_file);
    }
}
