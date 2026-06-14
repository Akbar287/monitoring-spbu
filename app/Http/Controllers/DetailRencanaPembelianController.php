<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\DetailRencanaPembelian;
use App\Models\Produk;
use App\Models\RencanaPembelian;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DetailRencanaPembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, RencanaPembelian $rencana)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = DetailRencanaPembelian::select('*')->where('rencana_pembelian_id', $rencana->rencana_pembelian_id);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn = $row->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('jumlah', function ($row) {
                    $actionBtn = number_format($row->jumlah, 6, ".", ",");
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('perencanaan.detail.show', [$row->rencana_pembelian()->first()->rencana_pembelian_id, $row->detail_rencana_pembelian_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Detail Produk Perencanaan',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Perencanaan', 'url' => route('perencanaan.index')], ['label' => $rencana->kode_pembelian, 'url' => route('perencanaan.show', $rencana->rencana_pembelian_id)]],
            'createUrl' => route('perencanaan.detail.create', $rencana->rencana_pembelian_id),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Id Produk', 'data' => 'detail_rencana_pembelian_id'],
                    ['label' => 'Nama Produk', 'data' => 'nama_produk'],
                    ['label' => 'Jumlah', 'data' => 'jumlah'],
                    ['label' => 'Satuan Jumlah', 'data' => 'satuan_jumlah'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function getRandomKode(RencanaPembelian $rencana)
    {
        $temp = $rencana->kode_pembelian;
        return $temp . '-' . ($rencana->detail_rencana_pembelian()->count() + 1);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(RencanaPembelian $rencana)
    {
        $produk = Produk::all();
        $kode = $this->getRandomKode($rencana);
        return Inertia::render('Form/Index', [
            'title' => 'Tambah Detail Produk',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Perencanaan', 'url' => route('perencanaan.index')], ['label' => $rencana->kode_pembelian, 'url' => route('perencanaan.show', $rencana->rencana_pembelian_id)]],
            'backUrl' => route('perencanaan.detail.index', $rencana->rencana_pembelian_id),
            'method' => 'post',
            'action' => route('perencanaan.detail.store', $rencana->rencana_pembelian_id),
            'fields' => [
                ['name' => 'kode', 'label' => 'Kode', 'type' => 'hidden', 'value' => $kode],
                ['name' => 'produk_id', 'label' => 'Produk', 'type' => 'select', 'value' => null,
                    'options' => $produk->map(fn ($s) => ['value' => $s->produk_id, 'label' => $s->nama_produk])->values()->all()],
                ['name' => 'jumlah', 'label' => 'Jumlah', 'type' => 'text', 'value' => null],
                ['name' => 'satuan_jumlah', 'label' => 'Satuan Jumlah', 'type' => 'select', 'value' => 'liter',
                    'options' => [['value' => 'liter', 'label' => 'Liter']]],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RencanaPembelian $rencana, Request $request)
    {
        $request->validate([
            "produk_id" => ["required"],
            "jumlah" => ["required"],
            "satuan_jumlah" => ["required"],
            'kode' => ['required']
        ]);

        $harga = $this->getHargaSatuanProduk(request('produk_id'));

        $detail = $rencana->detail_rencana_pembelian()->where("produk_id", request('produk_id'))->where("satuan_jumlah", request('satuan_jumlah'))->where("rit", $this->checkRecommendedRit(str_replace(',', '', request('jumlah'))))->where("harga", $harga)->where("total_harga", $harga * str_replace(',', '', request('jumlah')))->where("is_confirmation", 0)->where('kode_detail', $request->kode)->where("ms2", 0)->where("delivery", 0)->first();

        if (is_null($detail)) {
            $detail = $rencana->detail_rencana_pembelian()->create($this->detailData(request('kode')));
            $rencana->update(['grand_total' => $rencana->grand_total + ($harga * str_replace(',', '', request('jumlah')))]);
        }

        return redirect('/procurement/perencanaan/' . $rencana->rencana_pembelian_id . '/produk/' . $detail->detail_rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Produk Rencana Pembelian sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        exit;
    }

    /**
     * Display the specified resource.
     */
    public function show(RencanaPembelian $rencana, DetailRencanaPembelian $detail)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Produk',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Perencanaan', 'url' => route('perencanaan.index')], ['label' => $rencana->kode_pembelian, 'url' => route('perencanaan.show', $rencana->rencana_pembelian_id)]],
            'backUrl' => route('perencanaan.detail.index', $rencana->rencana_pembelian_id),
            'fields' => [
                ['label' => 'Produk', 'value' => optional($detail->produk()->first())->nama_produk],
                ['label' => 'Jumlah', 'value' => $detail->jumlah],
                ['label' => 'Satuan Jumlah', 'value' => $detail->satuan_jumlah],
            ],
            'actions' => [
                ['label' => 'Ubah', 'url' => route('perencanaan.detail.edit', [$rencana->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]), 'color' => 'primary', 'icon' => 'pen'],
            ],
            'deleteUrl' => route('perencanaan.detail.destroy', [$rencana->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaPembelian $rencana, DetailRencanaPembelian $detail)
    {
        $produk = Produk::all();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Detail Produk',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Perencanaan', 'url' => route('perencanaan.index')], ['label' => $rencana->kode_pembelian, 'url' => route('perencanaan.show', $rencana->rencana_pembelian_id)]],
            'backUrl' => route('perencanaan.detail.show', [$rencana->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]),
            'method' => 'put',
            'action' => route('perencanaan.detail.update', [$rencana->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]),
            'fields' => [
                ['name' => 'produk_id', 'label' => 'Produk', 'type' => 'select', 'value' => $detail->produk_id,
                    'options' => $produk->map(fn ($s) => ['value' => $s->produk_id, 'label' => $s->nama_produk])->values()->all()],
                ['name' => 'jumlah', 'label' => 'Jumlah', 'type' => 'text', 'value' => $detail->jumlah],
                ['name' => 'satuan_jumlah', 'label' => 'Satuan Jumlah', 'type' => 'select', 'value' => $detail->satuan_jumlah,
                    'options' => [['value' => 'liter', 'label' => 'Liter']]],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RencanaPembelian $rencana, Request $request, DetailRencanaPembelian $detail)
    {
        $totalHargaProduk = $rencana->detail_rencana_pembelian()->where('produk_id', $request->produk_id)->first()->total_harga;

        $detail->update($this->detailData($detail->kode_detail));

        $harga = $this->getHargaSatuanProduk(request('produk_id'));
        $rencana->update(['grand_total' => ($rencana->grand_total - $totalHargaProduk) + $harga * str_replace(',', '', request('jumlah'))]);

        return redirect('/procurement/perencanaan/' . $rencana->rencana_pembelian_id . '/produk/' . $detail->detail_rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Produk Rencana Pembelian sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RencanaPembelian $rencana, DetailRencanaPembelian $detail)
    {
        $detail->delete();

        return redirect('/procurement/perencanaan/' . $rencana->rencana_pembelian_id . '/produk')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Produk Rencana Pembelian sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function detailData($kode)
    {
        $harga = $this->getHargaSatuanProduk(request('produk_id'));
        return [
            "produk_id" => request('produk_id'),
            "jumlah" => str_replace(',', '', request('jumlah')),
            "satuan_jumlah" => request('satuan_jumlah'),
            "rit" => $this->checkRecommendedRit(str_replace(',', '', request('jumlah'))),
            "harga" => $harga,
            "total_harga" => $harga * str_replace(',', '', request('jumlah')),
            "is_confirmation" => 0,
            "ms2" => 0,
            "delivery" => 0,
            'kode_detail' => $kode
        ];
    }

    public function getHargaSatuanProduk($produk)
    {
        $produk = Produk::where('produk_id', $produk)->first();
        if (!is_null($produk)) {
            return is_null($produk->harga()->where('is_default', 1)->first()) ? 0 : $produk->harga()->where('is_default', 1)->first()->harga_beli;
        } else {
            return 0;
        }
    }

    public function checkRecommendedRit($jumlah)
    {
        return ceil($jumlah / 8000);
    }
}
