<?php

namespace App\Http\Controllers;

use App\Http\Requests\NozzleRequest;
use App\Models\Dispenser;
use App\Models\Nozzle;
use App\Models\Produk;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class NozzleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Nozzle::select('*')->addSelect('dispenser.nama_dispenser')->addSelect('nozzle.keterangan')->join('dispenser', 'nozzle.dispenser_id', 'dispenser.dispenser_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn =  $row->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('nama_payung', function ($row) {
                    $actionBtn =  $row->dispenser()->first()->payung()->first()->nama_payung;
                    return $actionBtn;
                })
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn =  $row->is_aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('nozzle.show', $row->nozzle_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Nozzle',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')]],
            'createUrl' => route('nozzle.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Payung', 'data' => 'nama_payung'],
                    ['label' => 'Nama Dispenser', 'data' => 'nama_dispenser'],
                    ['label' => 'Nama Produk', 'data' => 'nama_produk', 'name' => 'nama_payung'],
                    ['label' => 'Nama Nozzle', 'data' => 'nama_nozzle'],
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
        $dispenser = Dispenser::all();
        $produk = Produk::all();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Nozzle',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Nozzle', 'url' => route('nozzle.index')]],
            'backUrl' => route('nozzle.index'),
            'method' => 'post',
            'action' => route('nozzle.store'),
            'fields' => [
                ['name' => 'dispenser_id', 'label' => 'Dispenser', 'type' => 'select', 'value' => null, 'options' => $dispenser->map(fn ($x) => ['value' => $x->dispenser_id, 'label' => $x->nama_dispenser])->values()->all(), 'col' => 'col-md-6'],
                ['name' => 'produk_id', 'label' => 'Produk', 'type' => 'select', 'value' => null, 'options' => $produk->map(fn ($x) => ['value' => $x->produk_id, 'label' => $x->nama_produk])->values()->all(), 'col' => 'col-md-6'],
                ['name' => 'nama_nozzle', 'label' => 'Nama Nozzle', 'type' => 'text', 'value' => null],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NozzleRequest $nozzleRequest)
    {
        $dispenser = Dispenser::find($nozzleRequest->dispenser_id);

        $nozzle = $dispenser->nozzle()->where('produk_id', request('produk_id'))->where('nama_nozzle', request('nama_nozzle'))->where('keterangan', request('keterangan'))->where('is_aktif', request('is_aktif') ? 1 : 0)->first();
        if (is_null($nozzle)) {
            $nozzle = $dispenser->nozzle()->create($this->nozzleData());
        }

        return redirect('/master/nozzle')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Nozzle sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Nozzle $nozzle)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Nozzle',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Nozzle', 'url' => route('nozzle.index')]],
            'backUrl' => route('nozzle.index'),
            'fields' => [
                ['label' => 'Dispenser', 'value' => optional(\Illuminate\Support\Facades\DB::table('dispenser')->where('dispenser_id', $nozzle->dispenser_id)->first())->nama_dispenser],
                ['label' => 'Produk', 'value' => optional(\Illuminate\Support\Facades\DB::table('produk')->where('produk_id', $nozzle->produk_id)->first())->nama_produk],
                ['label' => 'Nama Nozzle', 'value' => $nozzle->nama_nozzle],
                ['label' => 'Status', 'value' => filter_var($nozzle->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif'],
                ['label' => 'Keterangan', 'value' => $nozzle->keterangan],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('nozzle.edit', $nozzle), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('nozzle.destroy', $nozzle),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nozzle $nozzle)
    {
        $dispenser = Dispenser::all();
        $produk = Produk::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Nozzle',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Nozzle', 'url' => route('nozzle.index')]],
            'backUrl' => route('nozzle.index'),
            'method' => 'put',
            'action' => route('nozzle.update', $nozzle),
            'fields' => [
                ['name' => 'dispenser_id', 'label' => 'Dispenser', 'type' => 'select', 'value' => $nozzle->dispenser_id, 'options' => $dispenser->map(fn ($x) => ['value' => $x->dispenser_id, 'label' => $x->nama_dispenser])->values()->all(), 'col' => 'col-md-6'],
                ['name' => 'produk_id', 'label' => 'Produk', 'type' => 'select', 'value' => $nozzle->produk_id, 'options' => $produk->map(fn ($x) => ['value' => $x->produk_id, 'label' => $x->nama_produk])->values()->all(), 'col' => 'col-md-6'],
                ['name' => 'nama_nozzle', 'label' => 'Nama Nozzle', 'type' => 'text', 'value' => $nozzle->nama_nozzle],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => filter_var($nozzle->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $nozzle->keterangan],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NozzleRequest $nozzleRequest, Nozzle $nozzle)
    {
        $nozzle->update($this->nozzleData());

        return redirect('/master/nozzle')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Nozzle sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nozzle $nozzle)
    {
        $nozzle->delete();

        return redirect('/master/nozzle')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Nozzle sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function nozzleData()
    {
        return [
            'produk_id' => request('produk_id'),
            'nama_nozzle' => request('nama_nozzle'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif') ? 1 : 0,
        ];
    }
}
