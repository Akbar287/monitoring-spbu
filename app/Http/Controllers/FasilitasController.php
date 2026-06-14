<?php

namespace App\Http\Controllers;

use App\Http\Requests\FasilitasRequest;
use App\Models\Fasilitas;
use App\Models\Spbu;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class FasilitasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Fasilitas::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('fasilitas.show', $row->fasilitas_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Fasilitas',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')]],
            'createUrl' => route('fasilitas.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Fasilitas', 'data' => 'nama_fasilitas'],
                    ['label' => 'Jumlah', 'data' => 'jumlah'],
                    ['label' => 'Tanggal Digunakan', 'data' => 'tanggal_digunakan'],
                    ['label' => 'Keterangan', 'data' => 'keterangan'],
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
            'title' => 'Tambah Fasilitas',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Fasilitas', 'url' => route('fasilitas.index')]],
            'backUrl' => route('fasilitas.index'),
            'method' => 'post',
            'action' => route('fasilitas.store'),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => null, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_fasilitas', 'label' => 'Nama Fasilitas', 'type' => 'text', 'value' => null],
                ['name' => 'jumlah', 'label' => 'Jumlah', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'tanggal_digunakan', 'label' => 'Tanggal Digunakan', 'type' => 'date', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FasilitasRequest $fasilitasRequest)
    {
        $spbu = Spbu::find($fasilitasRequest->spbu_id);
        $fasilitas = $spbu->fasilitas()->where('nama_fasilitas', request('nama_fasilitas'))->where('jumlah', request('jumlah'))->where('tanggal_digunakan', request('tanggal_digunakan'))->where('keterangan', request('keterangan'))->first();
        if (is_null($fasilitas)) {
            $fasilitas = $spbu->fasilitas()->create($this->fasilitasData());
        }

        return redirect('/master/fasilitas')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Fasilitas sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fasilitas $fasilitas)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Fasilitas',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Fasilitas', 'url' => route('fasilitas.index')]],
            'backUrl' => route('fasilitas.index'),
            'fields' => [
                ['label' => 'SPBU', 'value' => optional(\Illuminate\Support\Facades\DB::table('spbu')->where('spbu_id', $fasilitas->spbu_id)->first())->nama_spbu],
                ['label' => 'Nama Fasilitas', 'value' => $fasilitas->nama_fasilitas],
                ['label' => 'Jumlah', 'value' => $fasilitas->jumlah],
                ['label' => 'Tanggal Digunakan', 'value' => $fasilitas->tanggal_digunakan],
                ['label' => 'Keterangan', 'value' => $fasilitas->keterangan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('fasilitas.edit', $fasilitas), 'color' => 'primary', 'icon' => 'pen'], ['label' => 'File', 'url' => route('fasilitas.file.index', $fasilitas), 'color' => 'info', 'icon' => 'folder-open']],
            'deleteUrl' => route('fasilitas.destroy', $fasilitas),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fasilitas $fasilitas)
    {
        $spbu = Spbu::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Fasilitas',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Fasilitas', 'url' => route('fasilitas.index')]],
            'backUrl' => route('fasilitas.index'),
            'method' => 'put',
            'action' => route('fasilitas.update', $fasilitas),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $fasilitas->spbu_id, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_fasilitas', 'label' => 'Nama Fasilitas', 'type' => 'text', 'value' => $fasilitas->nama_fasilitas],
                ['name' => 'jumlah', 'label' => 'Jumlah', 'type' => 'text', 'value' => $fasilitas->jumlah, 'col' => 'col-md-6'],
                ['name' => 'tanggal_digunakan', 'label' => 'Tanggal Digunakan', 'type' => 'date', 'value' => $fasilitas->tanggal_digunakan, 'col' => 'col-md-6'],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $fasilitas->keterangan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FasilitasRequest $fasilitasRequest, Fasilitas $fasilitas)
    {
        $fasilitas->update($this->fasilitasData());

        return redirect('/master/fasilitas')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Fasilitas sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fasilitas $fasilitas)
    {
        $fasilitas->delete();

        return redirect('/master/fasilitas')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Fasilitas sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function fasilitasData()
    {
        return [
            'nama_fasilitas' => request('nama_fasilitas'),
            'jumlah' => request('jumlah'),
            'tanggal_digunakan' => request('tanggal_digunakan'),
            'keterangan' => request('keterangan'),
        ];
    }
}
