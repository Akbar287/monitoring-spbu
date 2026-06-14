<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdukRequest;
use App\Models\Produk;
use App\Models\Spbu;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Produk::select('produk.produk_id')->addSelect('produk.nama_produk')->addSelect('produk.is_aktif')->addSelect('detail_produk.nama_jenis')->addSelect('detail_produk.oktan')->join('detail_produk', 'produk.produk_id', 'detail_produk.produk_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif ? "Aktif" : "Tidak Aktif";
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('produk.show', $row->produk_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Produk',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')]],
            'createUrl' => route('produk.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Produk', 'data' => 'nama_produk'],
                    ['label' => 'Nama Jenis', 'data' => 'nama_jenis'],
                    ['label' => 'Oktan', 'data' => 'oktan'],
                    ['label' => 'Aktif', 'data' => 'is_aktif', 'name' => 'keterangan'],
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
            'title' => 'Tambah Produk',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Produk', 'url' => route('produk.index')]],
            'backUrl' => route('produk.index'),
            'method' => 'post',
            'action' => route('produk.store'),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => null, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_produk', 'label' => 'Nama Produk', 'type' => 'text', 'value' => null],
                ['name' => 'singkatan', 'label' => 'Singkatan', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'nama_jenis', 'label' => 'Jenis', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'oktan', 'label' => 'Oktan', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => null, 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']], 'col' => 'col-md-6'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProdukRequest $produkRequest)
    {
        $spbu = Spbu::find($produkRequest->spbu_id);
        $produk = $spbu->produk()->where('nama_produk', request('nama_produk'))->where('is_aktif', request('is_aktif') ? 1 : 0)->first();
        if (is_null($produk)) {
            $produk = $spbu->produk()->create($this->produkData());
        }

        $detail = $produk->detail_produk()->where('nama_jenis', request('nama_jenis'))->where('oktan', request('oktan'))->first();
        if (is_null($detail)) {
            $detail = $produk->detail_produk()->create($this->detailProdukData());
        }

        return redirect('/master/produk')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Produk sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Produk $produk)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Produk',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Produk', 'url' => route('produk.index')]],
            'backUrl' => route('produk.index'),
            'fields' => [
                ['label' => 'SPBU', 'value' => optional(\Illuminate\Support\Facades\DB::table('spbu')->where('spbu_id', $produk->spbu_id)->first())->nama_spbu],
                ['label' => 'Nama Produk', 'value' => $produk->nama_produk],
                ['label' => 'Singkatan', 'value' => $produk->singkatan],
                ['label' => 'Jenis', 'value' => optional($produk->detail_produk()->first())->nama_jenis],
                ['label' => 'Oktan', 'value' => optional($produk->detail_produk()->first())->oktan],
                ['label' => 'Status', 'value' => filter_var($produk->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'Aktif' : 'Tidak Aktif'],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('produk.edit', $produk), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('produk.destroy', $produk),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produk $produk)
    {
        $spbu = Spbu::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Produk',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Produk', 'url' => route('produk.index')]],
            'backUrl' => route('produk.index'),
            'method' => 'put',
            'action' => route('produk.update', $produk),
            'fields' => [
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => $produk->spbu_id, 'options' => $spbu->map(fn ($x) => ['value' => $x->spbu_id, 'label' => $x->nama_spbu . ' - ' . $x->nomor_spbu])->values()->all()],
                ['name' => 'nama_produk', 'label' => 'Nama Produk', 'type' => 'text', 'value' => $produk->nama_produk],
                ['name' => 'singkatan', 'label' => 'Singkatan', 'type' => 'text', 'value' => $produk->singkatan, 'col' => 'col-md-6'],
                ['name' => 'nama_jenis', 'label' => 'Jenis', 'type' => 'text', 'value' => optional($produk->detail_produk()->first())->nama_jenis, 'col' => 'col-md-6'],
                ['name' => 'oktan', 'label' => 'Oktan', 'type' => 'text', 'value' => optional($produk->detail_produk()->first())->oktan, 'col' => 'col-md-6'],
                ['name' => 'is_aktif', 'label' => 'Status', 'type' => 'select', 'value' => filter_var($produk->is_aktif, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false', 'options' => [['value' => 'true', 'label' => 'Aktifkan'], ['value' => 'false', 'label' => 'Jangan Aktifkan']], 'col' => 'col-md-6'],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProdukRequest $produkRequest, Produk $produk)
    {
        $produk->update($this->produkData());
        $produk->detail_produk()->update($this->detailProdukData());

        return redirect('/master/produk')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Produk sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produk $produk)
    {
        $produk->delete();

        return redirect('/master/produk')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Produk sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function produkData()
    {
        return [
            'nama_produk' => request('nama_produk'),
            'is_aktif' => request('is_aktif') ? 1 : 0,
            'singkatan' => request('singkatan'),
        ];
    }

    public function detailProdukData()
    {
        return [
            'nama_jenis' => request('nama_jenis'),
            'oktan' => request('oktan'),
        ];
    }
}
