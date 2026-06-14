<?php

namespace App\Http\Controllers;

use App\Http\Requests\JabatanRequest;
use App\Models\Jabatan;
use App\Models\Level;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class JabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Jabatan::select('jabatan.*')->addSelect('level.nama_level')->join('level', 'level.level_id', 'jabatan.level_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('jabatan.show', $row->jabatan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Jabatan',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')]],
            'createUrl' => route('jabatan.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Jabatan', 'data' => 'nama_jabatan'],
                    ['label' => 'Nama Level', 'data' => 'nama_level'],
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
        $level = Level::all();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Jabatan',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Jabatan', 'url' => route('jabatan.index')]],
            'backUrl' => route('jabatan.index'),
            'method' => 'post',
            'action' => route('jabatan.store'),
            'fields' => [
                ['name' => 'level_id', 'label' => 'Level', 'type' => 'select', 'value' => null, 'options' => $level->map(fn ($x) => ['value' => $x->level_id, 'label' => $x->nama_level])->values()->all()],
                ['name' => 'nama_jabatan', 'label' => 'Nama Jabatan', 'type' => 'text', 'value' => null],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JabatanRequest $jabatanRequest)
    {
        $level = Level::find($jabatanRequest->level_id);

        $jabatan = $level->jabatan()->where('nama_jabatan', request('nama_jabatan'))->where('keterangan', request('keterangan'))->where('is_aktif', request('is_aktif') == 'true' ? 1 : 0)->first();
        if (is_null($jabatan)) {
            $jabatan = $level->jabatan()->create($this->jabatanData());
        }

        return redirect('/konfigurasi/jabatan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jabatan sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Jabatan $jabatan)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Jabatan',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Jabatan', 'url' => route('jabatan.index')]],
            'backUrl' => route('jabatan.index'),
            'fields' => [
                ['label' => 'Level', 'value' => optional(\Illuminate\Support\Facades\DB::table('level')->where('level_id', $jabatan->level_id)->first())->nama_level],
                ['label' => 'Nama Jabatan', 'value' => $jabatan->nama_jabatan],
                ['label' => 'Status', 'value' => filter_var($jabatan->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif'],
                ['label' => 'Keterangan', 'value' => $jabatan->keterangan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('jabatan.edit', $jabatan), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('jabatan.destroy', $jabatan),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Jabatan $jabatan)
    {
        $level = Level::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Jabatan',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Jabatan', 'url' => route('jabatan.index')]],
            'backUrl' => route('jabatan.index'),
            'method' => 'put',
            'action' => route('jabatan.update', $jabatan),
            'fields' => [
                ['name' => 'level_id', 'label' => 'Level', 'type' => 'select', 'value' => $jabatan->level_id, 'options' => $level->map(fn ($x) => ['value' => $x->level_id, 'label' => $x->nama_level])->values()->all()],
                ['name' => 'nama_jabatan', 'label' => 'Nama Jabatan', 'type' => 'text', 'value' => $jabatan->nama_jabatan],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => filter_var($jabatan->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $jabatan->keterangan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JabatanRequest $jabatanRequest, Jabatan $jabatan)
    {
        $jabatan->update($this->jabatanData());

        return redirect('/konfigurasi/jabatan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jabatan sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jabatan $jabatan)
    {
        $jabatan->delete();

        return redirect('/konfigurasi/jabatan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Jabatan sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function jabatanData()
    {
        return [
            'nama_jabatan' => request('nama_jabatan'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif') == 'true' ? 1 : 0,
        ];
    }
}
