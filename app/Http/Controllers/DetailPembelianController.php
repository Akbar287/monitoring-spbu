<?php

namespace App\Http\Controllers;

use App\Models\DetailRencanaPembelian;
use App\Models\PajakPembelianLib;
use App\Models\RencanaPembelian;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DetailPembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, RencanaPembelian $pembelian)
    {
        if ($request->ajax()) {
            $data = DetailRencanaPembelian::select('*')->where('rencana_pembelian_id', $pembelian->rencana_pembelian_id);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn = $row->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('harga', function ($row) {
                    $actionBtn = 'Rp. ' . number_format($row->harga, 6, ".", ",");
                    return $actionBtn;
                })
                ->addColumn('total_harga', function ($row) {
                    $actionBtn = 'Rp. ' . number_format($row->total_harga, 6, ".", ",");
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pembelian.detail.show', [$row->rencana_pembelian()->first()->rencana_pembelian_id, $row->detail_rencana_pembelian_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $pajakLib = PajakPembelianLib::where('is_aktif', 1)->first();
        return view('procurement/pembelian_detail/index', compact('pembelian', 'pajakLib'));
    }

    /**
     * Display the specified resource.
     */
    public function show(RencanaPembelian $pembelian, DetailRencanaPembelian $detail)
    {
        return view('procurement/pembelian_detail/show', compact('pembelian', 'detail'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaPembelian $pembelian, DetailRencanaPembelian $detail)
    {
        return view('procurement/pembelian_detail/edit', compact('pembelian', 'detail'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RencanaPembelian $pembelian, DetailRencanaPembelian $detail)
    {
        $totalHarga = str_replace(',', '', $detail->jumlah) * str_replace(',', '', $request->harga);
        $detail->update($this->detailData($totalHarga));

        unset($totalHarga);
        $grandTotal = 0;

        foreach ($pembelian->detail_rencana_pembelian()->get() as $r) {
            $grandTotal = $grandTotal + $r->total_harga;
        }
        $pembelian->update([
            'grand_total' => $grandTotal
        ]);

        $pajakLib = PajakPembelianLib::where('is_aktif', 1)->first();
        $pajak = [
            'pajak_pembelian_lib_id' => $pajakLib->pajak_pembelian_lib_id,
            'ppn' => ($pajakLib->ppn / 100) * $grandTotal,
            'pph' => ($pajakLib->pph / 100) * $grandTotal,
            'ppbkb' => ($pajakLib->ppbkb / 100) * $grandTotal,
            'net_price' => $grandTotal,
            'total_amount' => $grandTotal + (($pajakLib->ppn / 100) * $grandTotal) + (($pajakLib->pph / 100) * $grandTotal) + (($pajakLib->ppbkb / 100) * $grandTotal)
        ];
        if ($pembelian->pajak_pembelian()->count() > 0) {
            $pembelian->pajak_pembelian()->delete();
        }

        if (is_null($pembelian->pajak_pembelian()->where('pajak_pembelian_lib_id', $pajakLib->pajak_pembelian_lib_id)->where('ppn', $pajak['ppn'])->where('pph', $pajak['pph'])->where('ppbkb', $pajak['ppbkb'])->where('net_price', $pajak['net_price'])->where('total_amount', $pajak['total_amount'])->first())) {
            $pembelian->pajak_pembelian()->create($pajak);
        }

        return redirect('/procurement/pembelian/' . $pembelian->rencana_pembelian_id . '/produk/' . $detail->detail_rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Produk Rencana Pembelian sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function detailData($totalHarga = 0)
    {
        return [
            "rit" => str_replace(',', '', request('rit')),
            "harga" => floatval(str_replace(',', '', request('harga'))),
            "total_harga" => floatval($totalHarga)
        ];
    }

    public function edit_pajak(RencanaPembelian $pembelian)
    {
        $pajakLib = PajakPembelianLib::where('is_aktif', 1)->first();
        return view('procurement/pembelian_detail/pajak', compact('pembelian', 'pajakLib'));
    }
    public function update_pajak(RencanaPembelian $pembelian)
    {
        if ($pembelian->pajak_pembelian()->count() > 0) {
            $pembelian->pajak_pembelian()->first()->update($this->pajakData());
        } else {
            $pembelian->pajak_pembelian()->create($this->pajakData());
        }
        return redirect('/procurement/pembelian/' . $pembelian->rencana_pembelian_id . '/produk')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pajak sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function pajakData()
    {
        $pajakLib = PajakPembelianLib::where('is_aktif', 1)->first();
        return [
            'pajak_pembelian_lib_id' => $pajakLib->pajak_pembelian_lib_id,
            'ppn' => str_replace(',', '', request('ppn')),
            'pph' => str_replace(',', '', request('pph')),
            'ppbkb' => str_replace(',', '', request('ppbkb')),
            'net_price' => str_replace(',', '', request('net_price')),
            'total_amount' => str_replace('.', '', str_replace(',', '', request('total_amount')))
        ];
    }
}
