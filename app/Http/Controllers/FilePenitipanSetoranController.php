<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\FilePenarikan;
use App\Models\Penarikan;
use App\Models\Penjualan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class FilePenitipanSetoranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Penjualan $penjualan, Penarikan $penitipan)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $penitipan->file_penarikan()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    $actionBtn = Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('gambar', function ($row) {
                    $actionBtn = '<img src="' . asset('storage/file_penarikan/' . $row->nama_file) . '" alt="' . $row->nama_dokumen . '" width="120" height="120" class="img img-thumbnail" />';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab' ?
                        '<div class="row"><div class="col-12 d-flex"><a href="' . asset('storage/file_penarikan/' . $row->nama_file) . '" target="_blank" class="btn btn-success btn-sm"><i class="fas fa fa-download"></i> Unduh</a>&nbsp;&nbsp;<form action="' . route('setoran.setoran_bank.file.delete', [$row->penarikan()->first()->penjualan()->first()->penjualan_id, $row->penarikan()->first()->penarikan_id, $row->file_penarikan_id]) . '" method="POST"><input type="hidden" name="_token" value="' . csrf_token() . '"><input type="hidden" name="_method" value="delete" /> <button type="submit" onclick="return confirm(\'Penitipan Setoran akan dihapus!\n Lanjutkan?\')" title="Hapus Data" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus</button></form></div></div>' : '<div class="row"><div class="col-12 d-flex"><a href="' . asset('storage/file_penarikan/' . $row->nama_file) . '" target="_blank" class="btn btn-success btn-sm"><i class="fas fa fa-download"></i> Unduh</a></div></div>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'gambar'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'File Penitipan Setoran',
            'breadcrumbs' => [['label' => 'Setoran', 'url' => route('menu.setoran')], ['label' => 'Setoran ke Bank', 'url' => route('setoran.setoran_bank.index_setoran_bank_penjualan')]],
            'createUrl' => route('setoran.setoran_bank.file.create', [$penjualan, $penitipan]),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Gambar', 'data' => 'gambar', 'raw' => true],
                    ['label' => 'Nama File', 'data' => 'nama_file'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Penjualan $penjualan, Penarikan $penitipan)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File Penitipan Setoran',
            'breadcrumbs' => [['label' => 'Setoran', 'url' => route('menu.setoran')], ['label' => 'Setoran ke Bank', 'url' => route('setoran.setoran_bank.index_setoran_bank_penjualan')], ['label' => 'File Penitipan Setoran', 'url' => route('setoran.setoran_bank.file.index', [$penjualan, $penitipan])]],
            'backUrl' => route('setoran.setoran_bank.file.index', [$penjualan, $penitipan]),
            'method' => 'post',
            'action' => route('setoran.setoran_bank.file.store', [$penjualan, $penitipan]),
            'fields' => [
                ['name' => 'gambar', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file dokumen/gambar yang akan diunggah.'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Penjualan $penjualan, Penarikan $penitipan)
    {
        $request->validate(['gambar' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = 'default.png';
        $filenameWithExt = '';

        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_penarikan', $foto);
        }

        $file = $penitipan->file_penarikan()->where('nama_file', $foto)->where('nama_dokumen', $filenameWithExt)->where('tanggal', date('Y-m-d'))->first();

        if (is_null($file)) {
            $file = $penitipan->file_penarikan()->create($this->filePenarikanData($foto, $filenameWithExt));
        }

        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id . '/bank/' . $penitipan->penarikan_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Penarikan sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Penjualan $penjualan, Penarikan $penitipan, FilePenarikan $file)
    {
        return view('setoran/file_penitipan/show', compact('penjualan', 'penitipan', 'file'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penjualan $penjualan, Penarikan $penitipan, FilePenarikan $file)
    {
        return view('setoran/file_penitipan/edit', compact('penjualan', 'penitipan', 'file'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penjualan $penjualan, Penarikan $penitipan, FilePenarikan $file)
    {
        $request->validate(['gambar' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = $file->nama_file;
        $filenameWithExt = $file->nama_dokumen;

        if ($request->hasFile('gambar')) {
            if ($file->nama_file != 'default.png') {
                Storage::delete("public/file_penarikan/" . $file->nama_file);
            }

            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_penarikan', $foto);
        }
        $file->update($this->filePenarikanData($foto, $filenameWithExt));

        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id . '/bank/' . $penitipan->penarikan_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Penarikan sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penjualan $penjualan, Penarikan $penitipan, FilePenarikan $file)
    {
        $file->delete();

        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id . '/bank/' . $penitipan->penarikan_id . '/file')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> File Penarikan sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function filePenarikanData($file, $dokumen)
    {
        return [
            'nama_file' => $file,
            'nama_dokumen' => $dokumen,
            'tanggal' => date('Y-m-d'),
            'keterangan' => ''
        ];
    }

    public function download(Penjualan $penjualan, Penarikan $penitipan, FilePenarikan $file)
    {
        return Storage::download('public/file_penarikan/' . $file->nama_file);
    }
}
