<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
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
        if ($request->ajax() && ! $request->inertia()) {
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
        return Inertia::render('Table/Index', [
            'title' => 'Detail Produk Pembelian',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembelian', 'url' => route('pembelian.index')], ['label' => $pembelian->kode_pembelian, 'url' => route('pembelian.show', $pembelian->rencana_pembelian_id)]],
            'headerActions' => [['label' => 'Pajak', 'url' => route('pembelian.detail.edit_pajak', $pembelian->rencana_pembelian_id), 'color' => 'info', 'icon' => 'percent']],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Produk', 'data' => 'nama_produk'],
                    ['label' => 'Rit', 'data' => 'rit'],
                    ['label' => 'Quantity', 'data' => 'jumlah'],
                    ['label' => 'Satuan', 'data' => 'satuan_jumlah'],
                    ['label' => 'Harga', 'data' => 'harga'],
                    ['label' => 'Total Harga', 'data' => 'total_harga'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(RencanaPembelian $pembelian, DetailRencanaPembelian $detail)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail Produk',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembelian', 'url' => route('pembelian.index')], ['label' => $pembelian->kode_pembelian, 'url' => route('pembelian.show', $pembelian->rencana_pembelian_id)]],
            'backUrl' => route('pembelian.detail.index', $pembelian->rencana_pembelian_id),
            'fields' => [
                ['label' => 'Produk', 'value' => optional($detail->produk()->first())->nama_produk],
                ['label' => 'Rit', 'value' => number_format($detail->rit, 6, '.', ',')],
                ['label' => 'Quantity', 'value' => number_format($detail->jumlah, 6, '.', ',')],
                ['label' => 'Satuan', 'value' => $detail->satuan_jumlah],
                ['label' => 'Harga', 'value' => 'Rp ' . number_format($detail->harga, 6, '.', ',')],
                ['label' => 'Total Harga', 'value' => 'Rp ' . number_format($detail->total_harga, 6, '.', ',')],
            ],
            'actions' => [
                ['label' => 'Ubah Harga', 'url' => route('pembelian.detail.edit', [$pembelian->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]), 'color' => 'primary', 'icon' => 'pen'],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaPembelian $pembelian, DetailRencanaPembelian $detail)
    {
        return Inertia::render('Pembelian/DetailEdit', [
            'title' => 'Ubah Harga Produk',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembelian', 'url' => route('pembelian.index')], ['label' => $pembelian->kode_pembelian, 'url' => route('pembelian.show', $pembelian->rencana_pembelian_id)]],
            'backUrl' => route('pembelian.detail.show', [$pembelian->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]),
            'action' => route('pembelian.detail.update', [$pembelian->rencana_pembelian_id, $detail->detail_rencana_pembelian_id]),
            'info' => [
                'produk' => optional($detail->produk()->first())->nama_produk,
                'rit' => number_format($detail->rit, 6, '.', ','),
                'jumlah' => number_format($detail->jumlah, 6, '.', ','),
                'satuan' => $detail->satuan_jumlah,
                'harga' => number_format($detail->harga, 6, '.', ','),
            ],
        ]);
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

        if ($pembelian->pajak_pembelian()->count() > 1) {
            $pembelian->pajak_pembelian()->first()->delete();
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
        return Inertia::render('Pembelian/PajakEdit', [
            'title' => 'Ubah Pajak Pembelian',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembelian', 'url' => route('pembelian.index')], ['label' => $pembelian->kode_pembelian, 'url' => route('pembelian.show', $pembelian->rencana_pembelian_id)]],
            'backUrl' => route('pembelian.detail.index', $pembelian->rencana_pembelian_id),
            'action' => route('pembelian.detail.update_pajak', $pembelian->rencana_pembelian_id),
            'values' => [
                'net_price' => number_format($pembelian->pajak_pembelian()->first()->net_price ?? $pembelian->grand_total, 6, '.', ','),
                'ppn' => number_format($pembelian->pajak_pembelian()->first()->ppn ?? $pembelian->grand_total * ($pajakLib->ppn / 100), 6, '.', ','),
                'ppbkb' => number_format($pembelian->pajak_pembelian()->first()->ppbkb ?? $pembelian->grand_total * ($pajakLib->ppbkb / 100), 6, '.', ','),
                'pph' => number_format($pembelian->pajak_pembelian()->first()->pph ?? $pembelian->grand_total * ($pajakLib->pph / 100), 6, '.', ','),
                'total_amount' => number_format($pembelian->pajak_pembelian()->first()->total_amount ?? $pembelian->grand_total + ($pembelian->grand_total * ($pajakLib->ppn / 100)) + ($pembelian->grand_total * ($pajakLib->ppbkb / 100)) + ($pembelian->grand_total * ($pajakLib->pph / 100)), 6, '.', ','),
            ],
        ]);
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
            'total_amount' => (substr_count(request('total_amount'), '.') > 1) ?  str_replace('.', '', request('total_amount')) :  str_replace(',', '', request('total_amount'))
        ];
    }
}
