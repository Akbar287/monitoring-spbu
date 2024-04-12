<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenjualanDetailRequest;
use App\Models\DetailPenjualan;
use App\Models\Nozzle;
use App\Models\Penjualan;
use App\Models\StandMeter;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class OpenPenjualanDetailController extends Controller
{
    public function index(Request $request, Penjualan $penjualan)
    {
        if ($request->ajax()) {
            $data = $penjualan->detail_penjualan()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_nozzle', function ($row) {
                    return $row->stand_meter()->first()->nozzle()->first()->nama_nozzle;
                })
                ->addColumn('stand_meter_awal', function ($row) {
                    return number_format($row->stand_meter()->first()->stand_meter_awal, 0, ".", ",");
                })
                ->addColumn('stand_meter_akhir', function ($row) {
                    return number_format($row->stand_meter()->first()->stand_meter_akhir, 0, ".", ",");
                })
                ->addColumn('harga', function ($row) {
                    return 'Rp. ' . number_format($row->harga()->first()->harga_jual, 2, ".", ",");
                })
                ->addColumn('liter', function ($row) {
                    return number_format($row->liter, 0, ".", ",");
                })
                ->addColumn('total_detail_jual', function ($row) {
                    return 'Rp. ' . number_format($row->total_detail_jual, 2, ".", ",");
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('setoran.open.detail.show', [$row->penjualan()->first()->penjualan_id, $row->detail_penjualan_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('setoran/detail_open_penjualan/index', compact('penjualan'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Penjualan $penjualan)
    {
        $nozzle = Nozzle::select('nozzle.nozzle_id', 'nozzle.nama_nozzle', 'stand_meter.stand_meter_awal', 'stand_meter.stand_meter_akhir', 'harga.harga_jual')->join('stand_meter', 'stand_meter.nozzle_id', 'nozzle.nozzle_id')->join('produk', 'produk.produk_id', 'nozzle.produk_id')->join('harga', 'harga.produk_id', 'produk.produk_id')->whereNotIn('stand_meter_id', $penjualan->detail_penjualan->pluck('stand_meter_id'))->where('stand_meter.tanggal', $penjualan->tanggal)->where('stand_meter.jam_kerja_id', $penjualan->jam_kerja_id)->where('harga.is_default', true)->orderBy('nozzle.nama_nozzle')->get();

        return view('setoran/detail_open_penjualan/create', compact('penjualan', 'nozzle'));
    }

    public function create_api(Request $request, Penjualan $penjualan)
    {
        $request->validate([
            'stand_meter_id' => ['required']
        ]);

        $sm = StandMeter::where('stand_meter_id', $request->stand_meter_id)->first();
        $harga = $penjualan->produk()->first()->harga()->where('is_default', true)->first();
        if (is_null($sm)) {
            return response()->json([
                'data' => null,
                'message' => 'stand meter not found',
                'code' => 404
            ]);
            exit;
        } else {
            return response()->json([
                'data' => [
                    'stand_meter_awal' => number_format($sm->stand_meter_awal, 0, ".", ','),
                    'stand_meter_akhir' => number_format($sm->stand_meter_akhir, 0, ".", ','),
                    'harga_jual' => number_format($harga->harga_jual, 0, '.', ','),
                    'total_penjualan' => number_format(($sm->stand_meter_akhir - $sm->stand_meter_awal) * $harga->harga_jual, 0, '.', ',')
                ],
                'message' => 'success',
                'code' => 200
            ], 200);
            exit;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PenjualanDetailRequest $penjualanDetailRequest, Penjualan $penjualan)
    {
        $sm = Nozzle::where('nozzle_id', $penjualanDetailRequest->nozzle_id)->first()->stand_meter()->where('tanggal', $penjualan->tanggal)->where('jam_kerja_id', $penjualan->jam_kerja_id)->first();

        if (!is_null($sm)) {
            $detail = $penjualan->detail_penjualan()->where('stand_meter_id', $sm->stand_meter_id)->first();
            if (is_null($detail)) {
                $harga = $sm->nozzle()->first()->produk()->first()->harga()->where('is_default', true)->first();

                $detail = $penjualan->detail_penjualan()->create($this->openPenjualanDetailData([$sm->stand_meter_id, $harga->harga_id, $sm->stand_meter_akhir - $sm->stand_meter_awal, ($sm->stand_meter_akhir - $sm->stand_meter_awal) * $harga->harga_jual]));
            }

            $total = 0;
            foreach ($penjualan->detail_penjualan()->select('total_detail_jual')->get() as $dp) {
                $total += $dp->total_detail_jual ?? 0;
            }
            $penjualan->update(['total_penjualan' => $total]);

            return redirect('/setoran/open-penjualan/' . $penjualan->penjualan_id . '/detail/' . $detail->detail_penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Detail Penjualan sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        } else {
            return redirect('/setoran/open-penjualan/' . $penjualan->penjualan_id . '/detail/create')->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Gagal!</strong> Buat Stand Meter Terlebih dahulu sebelum di tambahkan ke open penjualan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Penjualan $penjualan, DetailPenjualan $detail)
    {
        return view('setoran/detail_open_penjualan/show', compact('penjualan', 'detail'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penjualan $penjualan, DetailPenjualan $detail)
    {
        $detail->delete();

        $total = 0;
        foreach ($penjualan->detail_penjualan()->select('total_detail_jual')->get() as $dp) {
            $total += $dp->total_detail_jual ?? 0;
        }
        $penjualan->update(['total_penjualan' => $total]);

        return redirect('/setoran/open-penjualan/' . $penjualan->penjualan_id . '/detail')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Detail Penjualan sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function openPenjualanDetailData($data)
    {
        return [
            'stand_meter_id' => $data['0'],
            'harga_id' => $data['1'],
            'liter' => $data['2'],
            'total_detail_jual' => $data['3'],
            'is_confirmed' => false
        ];
    }
}
