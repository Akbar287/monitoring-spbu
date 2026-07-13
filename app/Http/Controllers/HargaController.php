<?php

namespace App\Http\Controllers;

use App\Http\Requests\HargaRequest;
use App\Models\Harga;
use App\Models\JamKerja;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class HargaController extends Controller
{
    public function riwayat_index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Harga::where('is_default', 0)->orderBy('tanggal', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('produk', function ($row) {
                    return $row->produk()->first()->nama_produk;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->jam_kerja()->first()->nama_jam_kerja;
                })
                ->addColumn('harga_beli', function ($row) {
                    return 'Rp. ' . number_format($row->harga_beli, 6, ".", ",");
                })
                ->addColumn('harga_jual', function ($row) {
                    return 'Rp. ' . number_format($row->harga_jual, 6, ".", ",");
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('harga.riwayat.show', $row->harga_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Riwayat Harga',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')]],
            'headerActions' => [['label' => 'Harga Aktif', 'url' => route('harga.index'), 'color' => 'primary', 'icon' => 'tags']],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Produk', 'data' => 'produk'],
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Jam Kerja', 'data' => 'jam_kerja'],
                    ['label' => 'Harga Jual', 'data' => 'harga_jual'],
                    ['label' => 'Harga Beli', 'data' => 'harga_beli'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }
    public function riwayat_show(Harga $harga)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Harga',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => route('menu.master')],
                ['label' => 'Harga', 'url' => route('harga.index')],
                ['label' => 'Riwayat', 'url' => route('harga.riwayat.index')],
            ],
            'backUrl' => route('harga.riwayat.index'),
            'fields' => [
                ['label' => 'Produk', 'value' => optional($harga->produk()->first())->nama_produk],
                ['label' => 'Jam Kerja', 'value' => optional($harga->jam_kerja()->first())->nama_jam_kerja],
                ['label' => 'Tanggal', 'value' => $harga->tanggal],
                ['label' => 'Harga Jual', 'value' => 'Rp. ' . number_format($harga->harga_jual, 6, '.', ',')],
                ['label' => 'Harga Beli', 'value' => 'Rp. ' . number_format($harga->harga_beli, 6, '.', ',')],
                ['label' => 'Status', 'value' => $harga->is_default ? 'Aktif' : 'Tidak Aktif'],
            ],
            'deleteUrl' => route('harga.destroy', $harga->harga_id),
            'deleteConfirm' => "Harga akan dihapus!\n Lanjutkan?",
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Harga::where('is_default', 1)->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('produk', function ($row) {
                    return $row->produk()->first()->nama_produk;
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->jam_kerja()->first()->nama_jam_kerja;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('harga_beli', function ($row) {
                    return 'Rp. ' . number_format($row->harga_beli, 6, ".", ",");
                })
                ->addColumn('harga_jual', function ($row) {
                    return 'Rp. ' . number_format($row->harga_jual, 6, ".", ",");
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('harga.show', $row->harga_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Harga',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')]],
            'createUrl' => route('harga.create'),
            'headerActions' => [['label' => 'Riwayat', 'url' => route('harga.riwayat.index'), 'color' => 'info', 'icon' => 'history']],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Produk', 'data' => 'produk'],
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Jam Kerja', 'data' => 'jam_kerja'],
                    ['label' => 'Harga Jual', 'data' => 'harga_jual'],
                    ['label' => 'Harga Beli', 'data' => 'harga_beli'],
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
        $jamKerja = JamKerja::get();
        $produk = Produk::where('is_aktif', 1)->get();
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Harga',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Harga', 'url' => route('harga.index')]],
            'backUrl' => route('harga.index'),
            'method' => 'post',
            'action' => route('harga.store'),
            'fields' => [
                ['name' => 'produk_id', 'label' => 'Produk', 'type' => 'select', 'value' => null, 'options' => $produk->map(fn ($x) => ['value' => $x->produk_id, 'label' => $x->nama_produk])->values()->all(), 'col' => 'col-md-6'],
                ['name' => 'jam_kerja_id', 'label' => 'Jam Kerja', 'type' => 'select', 'value' => null, 'options' => $jamKerja->map(fn ($x) => ['value' => $x->jam_kerja_id, 'label' => $x->nama_jam_kerja])->values()->all(), 'col' => 'col-md-6'],
                ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'value' => null],
                ['name' => 'harga_jual', 'label' => 'Harga Jual', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'harga_beli', 'label' => 'Harga Beli', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HargaRequest $hargaRequest)
    {
        $produk = Produk::where('produk_id', $hargaRequest->produk_id)->first();

        $check = $produk->harga()->where('is_default', 1)->first();
        if (!is_null($check)) {
            $check->update([
                'is_default' => 0
            ]);
        }


        $harga = $produk->harga()->where('jam_kerja_id', request('jam_kerja_id'))->where('tanggal', request('tanggal'))->where('harga_jual', str_replace(',', '', request('harga_jual')))->where('harga_beli', str_replace(',', '', request('harga_beli')))->where('is_default', 1)->first();
        if (is_null($harga)) {
            $harga = $produk->harga()->create($this->hargaData());
        }

        return redirect('/master/harga')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Harga sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Harga $harga)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Harga',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Harga', 'url' => route('harga.index')]],
            'backUrl' => route('harga.index'),
            'fields' => [
                ['label' => 'Produk', 'value' => optional(\Illuminate\Support\Facades\DB::table('produk')->where('produk_id', $harga->produk_id)->first())->nama_produk],
                ['label' => 'Jam Kerja', 'value' => optional(\Illuminate\Support\Facades\DB::table('jam_kerja')->where('jam_kerja_id', $harga->jam_kerja_id)->first())->nama_jam_kerja],
                ['label' => 'Tanggal', 'value' => $harga->tanggal],
                ['label' => 'Harga Jual', 'value' => $harga->harga_jual],
                ['label' => 'Harga Beli', 'value' => $harga->harga_beli],
            ],
            'actions' => [['label' => 'Ubah', 'url' => route('harga.edit', $harga), 'color' => 'primary', 'icon' => 'pen']],
            'deleteUrl' => route('harga.destroy', $harga),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Harga $harga)
    {
        $jamKerja = JamKerja::get();
        $produk = Produk::where('is_aktif', 1)->get();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Harga',
            'breadcrumbs' => [['label' => 'Master Data', 'url' => route('menu.master')], ['label' => 'Harga', 'url' => route('harga.index')]],
            'backUrl' => route('harga.index'),
            'method' => 'put',
            'action' => route('harga.update', $harga),
            'fields' => [
                ['name' => 'produk_id', 'label' => 'Produk', 'type' => 'select', 'value' => $harga->produk_id, 'options' => $produk->map(fn ($x) => ['value' => $x->produk_id, 'label' => $x->nama_produk])->values()->all(), 'col' => 'col-md-6'],
                ['name' => 'jam_kerja_id', 'label' => 'Jam Kerja', 'type' => 'select', 'value' => $harga->jam_kerja_id, 'options' => $jamKerja->map(fn ($x) => ['value' => $x->jam_kerja_id, 'label' => $x->nama_jam_kerja])->values()->all(), 'col' => 'col-md-6'],
                ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'value' => $harga->tanggal],
                ['name' => 'harga_jual', 'label' => 'Harga Jual', 'type' => 'text', 'value' => $harga->harga_jual, 'col' => 'col-md-6'],
                ['name' => 'harga_beli', 'label' => 'Harga Beli', 'type' => 'text', 'value' => $harga->harga_beli, 'col' => 'col-md-6'],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HargaRequest $hargaRequest, Harga $harga)
    {
        $harga->update($this->hargaData());

        return redirect('/master/harga')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Harga sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Harga $harga)
    {
        $temp = $harga->is_default;

        $harga->delete();

        if ($temp) {
            $harga->produk()->first()->harga()->orderBy('tanggal', 'desc')->first()->update([
                'is_default' => 1
            ]);
        }

        return redirect('/master/harga')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Harga sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function hargaData()
    {
        return [
            'jam_kerja_id' => request('jam_kerja_id'),
            'tanggal' => request('tanggal'),
            'harga_jual' => str_replace(',', '', request('harga_jual')),
            'harga_beli' => str_replace(',', '', request('harga_beli')),
            'is_default' => 1,
        ];
    }
}
