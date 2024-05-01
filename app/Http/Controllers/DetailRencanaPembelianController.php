<?php

namespace App\Http\Controllers;

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
        if ($request->ajax()) {
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
        return view('procurement/perencanaan_detail/index', compact('rencana'));
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
        return view('procurement/perencanaan_detail/create', compact('rencana', 'produk', 'kode'));
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

        $detail = $rencana->detail_rencana_pembelian()->where("produk_id", request('produk_id'))->where("satuan_jumlah", request('satuan_jumlah'))->where("rit", $this->checkRecommendedRit(str_replace(',', '', request('jumlah'))))->where("harga", $harga)->where("total_harga", $harga * str_replace(',', '', request('jumlah')))->where("is_confirmation", false)->where('kode_detail', $request->kode)->where("ms2", false)->where("delivery", false)->first();

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
        return view('procurement/perencanaan_detail/show', compact('rencana', 'detail'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaPembelian $rencana, DetailRencanaPembelian $detail)
    {
        $produk = Produk::all();
        return view('procurement/perencanaan_detail/edit', compact('rencana', 'produk', 'detail'));
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
            "is_confirmation" => false,
            "ms2" => false,
            "delivery" => false,
            'kode_detail' => $kode
        ];
    }

    public function getHargaSatuanProduk($produk)
    {
        $produk = Produk::where('produk_id', $produk)->first();

        if (!is_null($produk)) {
            return is_null($produk->harga()->where('is_default', true)->first()) ? 0 : $produk->harga()->where('is_default', true)->first()->harga_beli;
        } else {
            return 0;
        }
    }

    public function checkRecommendedRit($jumlah)
    {
        return ceil($jumlah / 8000);
    }
}
