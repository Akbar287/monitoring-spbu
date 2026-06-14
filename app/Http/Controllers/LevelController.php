<?php

namespace App\Http\Controllers;

use App\Http\Requests\LevelRequest;
use App\Models\Divisi;
use App\Models\Level;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Level::select('level.*')->addSelect('divisi.nama_divisi')->join('divisi', 'divisi.divisi_id', 'level.divisi_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('level.show', $row->level_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Level',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')]],
            'createUrl' => route('level.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Level', 'data' => 'nama_level'],
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
        $divisi = Divisi::all();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Level',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Level', 'url' => route('level.index')]],
            'backUrl' => route('level.index'),
            'method' => 'post',
            'action' => route('level.store'),
            'fields' => [
                ['name' => 'divisi_id', 'label' => 'Divisi', 'type' => 'select', 'value' => null, 'options' => $divisi->map(fn ($x) => ['value' => $x->divisi_id, 'label' => $x->nama_divisi])->values()->all()],
                ['name' => 'nama_level', 'label' => 'Nama Level', 'type' => 'text', 'value' => null],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LevelRequest $levelRequest)
    {
        $divisi = Divisi::find($levelRequest->divisi_id);

        $level = $divisi->level()->where('nama_level', request('nama_level'))->where('keterangan', request('keterangan'))->where('is_aktif', request('is_aktif') == 'true' ? 1 : 0)->first();
        if (is_null($level)) {
            $level = $divisi->level()->create($this->levelData());
        }

        return redirect('/konfigurasi/level')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Level sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Level $level)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Level',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Level', 'url' => route('level.index')]],
            'backUrl' => route('level.index'),
            'fields' => [
                ['label' => 'Divisi', 'value' => optional(\Illuminate\Support\Facades\DB::table('divisi')->where('divisi_id', $level->divisi_id)->first())->nama_divisi],
                ['label' => 'Nama Level', 'value' => $level->nama_level],
                ['label' => 'Status', 'value' => filter_var($level->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif'],
                ['label' => 'Keterangan', 'value' => $level->keterangan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('level.edit', $level), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('level.destroy', $level),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Level $level)
    {
        $divisi = Divisi::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Level',
            'breadcrumbs' => [['label' => 'Konfigurasi', 'url' => route('menu.konfigurasi')], ['label' => 'Level', 'url' => route('level.index')]],
            'backUrl' => route('level.index'),
            'method' => 'put',
            'action' => route('level.update', $level),
            'fields' => [
                ['name' => 'divisi_id', 'label' => 'Divisi', 'type' => 'select', 'value' => $level->divisi_id, 'options' => $divisi->map(fn ($x) => ['value' => $x->divisi_id, 'label' => $x->nama_divisi])->values()->all()],
                ['name' => 'nama_level', 'label' => 'Nama Level', 'type' => 'text', 'value' => $level->nama_level],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => filter_var($level->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $level->keterangan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LevelRequest $levelRequest, Level $level)
    {
        $level->update($this->levelData());

        return redirect('/konfigurasi/level')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Level sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Level $level)
    {
        $level->delete();

        return redirect('/konfigurasi/level')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Divisi sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function levelData()
    {
        return [
            'nama_level' => request('nama_level'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif') == 'true' ? 1 : 0,
        ];
    }
}
