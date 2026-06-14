<?php

namespace App\Http\Controllers;

use App\Http\Requests\DombakRequest;
use App\Models\Dombak;
use App\Models\Spbu;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class DombakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Dombak::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('dombak.show', $row->dombak_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Dombak',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')]],
            'createUrl' => route('dombak.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Dombak', 'data' => 'nama_dombak'],
                    ['label' => 'Aktif', 'data' => 'is_aktif'],
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
            'title' => 'Tambah Dombak',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dombak', 'url' => route('dombak.index')]],
            'backUrl' => route('dombak.index'),
            'method' => 'post',
            'action' => route('dombak.store'),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => null, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_dombak', 'label' => 'Nama Dombak', 'type' => 'text', 'value' => null],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DombakRequest $dombakRequest)
    {
        $spbu = Spbu::find($dombakRequest->spbu_id);

        $dombak = $spbu->dombak()->where('nama_dombak', request('nama_dombak'))->where('keterangan', request('keterangan'))->where('is_aktif', request('is_aktif') ? 1 : 0)->first();
        if (is_null($dombak)) {
            $dombak = $spbu->dombak()->create($this->dombakData());
        }

        return redirect('/master/dombak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dombak sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Dombak $dombak)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Dombak',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dombak', 'url' => route('dombak.index')]],
            'backUrl' => route('dombak.index'),
            'fields' => [
                ['label' => 'SPBU', 'value' => optional(\Illuminate\Support\Facades\DB::table('spbu')->where('spbu_id', $dombak->spbu_id)->first())->nama_spbu],
                ['label' => 'Nama Dombak', 'value' => $dombak->nama_dombak],
                ['label' => 'Status', 'value' => filter_var($dombak->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif'],
                ['label' => 'Keterangan', 'value' => $dombak->keterangan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('dombak.edit', $dombak), 'color' => 'primary', 'icon' => 'pen'], ['label' => 'Konversi', 'url' => route('konversi.index', $dombak), 'color' => 'info', 'icon' => 'ruler-vertical']],
            'deleteUrl' => route('dombak.destroy', $dombak),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dombak $dombak)
    {
        $spbu = Spbu::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Dombak',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Dombak', 'url' => route('dombak.index')]],
            'backUrl' => route('dombak.index'),
            'method' => 'put',
            'action' => route('dombak.update', $dombak),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $dombak->spbu_id, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_dombak', 'label' => 'Nama Dombak', 'type' => 'text', 'value' => $dombak->nama_dombak],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => filter_var($dombak->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $dombak->keterangan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DombakRequest $dombakRequest, Dombak $dombak)
    {
        $dombak->update($this->dombakData());

        return redirect('/master/dombak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dombak sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dombak $dombak)
    {
        $dombak->delete();

        return redirect('/master/dombak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dombak sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function dombakData()
    {
        return [
            'nama_dombak' => request('nama_dombak'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif') ? 1 : 0
        ];
    }

    public function api(Request $request)
    {
        $request->validate([
            'dombak_id' => ['required']
        ]);

        $dombak = Dombak::where('dombak_id', $request->dombak_id)->first();
        $data = $dombak->stok_inventory_dombak()->first();

        if ($dombak->konversi()->count() > 0) {
            $min = $dombak->konversi()->select('tinggi')->orderBy('tinggi', 'asc')->limit(1)->first();
            $max = $dombak->konversi()->select('tinggi')->orderBy('tinggi', 'desc')->limit(1)->first();

            if (is_null($dombak)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'dombak not found',
                    'data' => []
                ], 404);
            }

            $tinggi = [
                'min' => $min->tinggi,
                'max' => $max->tinggi
            ];
        } else {
            $tinggi = [
                'min' => 0,
                'max' => 0
            ];
        }

        $dombakLain = null;
        if (!is_null($request->type)) {
            if (request('type') == 'transfer') {
                $dombakLain = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_dombak()->select('dombak.dombak_id')->addSelect('dombak.nama_dombak')->addSelect('stok_inventory_dombak.stok')->addSelect('produk.nama_produk')->join('stok_inventory', 'stok_inventory.stok_inventory_id', 'stok_inventory_dombak.stok_inventory_id')->join('produk', 'produk.produk_id', 'stok_inventory.produk_id')->join('dombak', 'dombak.dombak_id', 'stok_inventory_dombak.dombak_id')->where('stok_inventory_dombak.dombak_id', '!=', $dombak->dombak_id)->get();
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'stok konversi not found',
            'data' => [
                'stok' => number_format($data->stok, 6, ".", ",")
            ],
            'tinggi' => $tinggi,
            'dombak' => $dombakLain
        ], 200);
    }
}
