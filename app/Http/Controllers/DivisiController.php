<?php

namespace App\Http\Controllers;


use App\Models\Divisi;
use App\Http\Requests\DivisiRequest;
use App\Models\Spbu;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class DivisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Divisi::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == 1 ? 'Aktif' : "Tidak Aktif";
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('divisi.show', $row->divisi_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Divisi',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')]],
            'createUrl' => route('divisi.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Divisi', 'data' => 'nama_divisi'],
                    ['label' => 'Keterangan', 'data' => 'keterangan'],
                    ['label' => 'Aktif', 'data' => 'is_aktif'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::all();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Divisi',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Divisi', 'url' => route('divisi.index')]],
            'backUrl' => route('divisi.index'),
            'method' => 'post',
            'action' => route('divisi.store'),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => null, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_divisi', 'label' => 'Nama Divisi', 'type' => 'text', 'value' => null],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DivisiRequest $divisiRequest)
    {
        $spbu = Spbu::find($divisiRequest->spbu_id);

        $divisi = $spbu->divisi()->where('nama_divisi', request('nama_divisi'))->where('keterangan', request('keterangan'))->where('is_aktif', request('is_aktif') == 'true' ? 1 : 0)->first();
        if (is_null($divisi)) {
            $divisi = $spbu->divisi()->create($this->divisiData());
        }

        return redirect('/konfigurasi/divisi')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Divisi sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Divisi $divisi)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Divisi',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Divisi', 'url' => route('divisi.index')]],
            'backUrl' => route('divisi.index'),
            'fields' => [
                ['label' => 'SPBU', 'value' => optional(\Illuminate\Support\Facades\DB::table('spbu')->where('spbu_id', $divisi->spbu_id)->first())->nama_spbu],
                ['label' => 'Nama Divisi', 'value' => $divisi->nama_divisi],
                ['label' => 'Status', 'value' => filter_var($divisi->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif'],
                ['label' => 'Keterangan', 'value' => $divisi->keterangan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('divisi.edit', $divisi), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('divisi.destroy', $divisi),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Divisi $divisi)
    {
        $spbu = Spbu::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Divisi',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Divisi', 'url' => route('divisi.index')]],
            'backUrl' => route('divisi.index'),
            'method' => 'put',
            'action' => route('divisi.update', $divisi),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $divisi->spbu_id, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_divisi', 'label' => 'Nama Divisi', 'type' => 'text', 'value' => $divisi->nama_divisi],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => filter_var($divisi->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $divisi->keterangan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DivisiRequest $divisiRequest, Divisi $divisi)
    {
        $divisi->update($this->divisiData());

        return redirect('/konfigurasi/divisi')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Divisi sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Divisi $divisi)
    {
        $divisi->delete();

        return redirect('/konfigurasi/divisi/')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Divisi sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function divisiData()
    {
        return [
            'nama_divisi' => request('nama_divisi'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif') == 'true' ? 1 : 0
        ];
    }
}
