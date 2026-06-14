<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
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
        if ($request->ajax() && ! $request->inertia()) {
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
        return Inertia::render('Table/Index', [
            'title' => 'Lampiran LO',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Delivery', 'url' => route('pengiriman.index')]],
            'createUrl' => route('pengiriman.file_lo_show.lampiran.create', [$pengiriman, $file_lo]),
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
    public function create(Pengiriman $pengiriman, FileLo $file_lo)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Lampiran LO',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Delivery', 'url' => route('pengiriman.index')], ['label' => 'Lampiran LO', 'url' => route('pengiriman.file_lo_show.lampiran.index', [$pengiriman, $file_lo])]],
            'backUrl' => route('pengiriman.file_lo_show.lampiran.index', [$pengiriman, $file_lo]),
            'method' => 'post',
            'action' => route('pengiriman.file_lo_show.lampiran.store', [$pengiriman, $file_lo]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Pengiriman $pengiriman, FileLo $file_lo)
    {
        $request->validate(['gambar' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

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
        return Inertia::render('Show/Index', [
            'title' => 'Detail Lampiran LO',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Delivery', 'url' => route('pengiriman.index')], ['label' => 'Lampiran LO', 'url' => route('pengiriman.file_lo_show.lampiran.index', [$pengiriman, $file_lo])]],
            'backUrl' => route('pengiriman.file_lo_show.lampiran.index', [$pengiriman, $file_lo]),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $fileLampiranLo->nama_dokumen],
                ['label' => 'Nama File', 'value' => $fileLampiranLo->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $fileLampiranLo->tanggal_upload],
            ],
            'image' => asset('storage/file_lampiran_lo/' . $fileLampiranLo->nama_file),
            'actions' => [
                ['label' => 'Ubah', 'url' => route('pengiriman.file_lo_show.lampiran.edit', [$pengiriman, $file_lo, $fileLampiranLo]), 'color' => 'primary', 'icon' => 'pen'],
                ['label' => 'Download', 'url' => route('pengiriman.file_lo_show.lampiran.unduh', [$pengiriman, $file_lo, $fileLampiranLo]), 'color' => 'info', 'icon' => 'download'],
            ],
            'deleteUrl' => route('pengiriman.file_lo_show.lampiran.destroy', [$pengiriman, $file_lo, $fileLampiranLo]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pengiriman $pengiriman, FileLo $file_lo, FileLampiranLo $fileLampiranLo)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Lampiran LO',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Delivery', 'url' => route('pengiriman.index')], ['label' => 'Lampiran LO', 'url' => route('pengiriman.file_lo_show.lampiran.index', [$pengiriman, $file_lo])]],
            'backUrl' => route('pengiriman.file_lo_show.lampiran.show', [$pengiriman, $file_lo, $fileLampiranLo]),
            'method' => 'put',
            'action' => route('pengiriman.file_lo_show.lampiran.update', [$pengiriman, $file_lo, $fileLampiranLo]),
            'fields' => [
                ['name' => 'file', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $fileLampiranLo->nama_dokumen],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pengiriman $pengiriman, FileLo $file_lo, FileLampiranLo $fileLampiranLo)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);


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
