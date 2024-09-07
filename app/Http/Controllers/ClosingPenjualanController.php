<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use App\Models\StatusSetoran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ClosingPenjualanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $waktuMulai = $request->has('waktu_mulai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_mulai) : Carbon::now()->firstOfMonth();
        $waktuSelesai = $request->has('waktu_selesai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_selesai) : Carbon::now()->endOfMonth();
        if ($waktuMulai->gt($waktuSelesai)) {
            $temp = $waktuMulai;
            $waktuMulai = $waktuSelesai;
            $waktuSelesai = $temp;
        }
        $waktuMulai = $waktuMulai->format('Y-m-d');
        $waktuSelesai = $waktuSelesai->format('Y-m-d');

        if ($request->ajax()) {
            $data = StatusSetoran::where('nama_status', 'Closing Penjualan')->first()->penjualan()->whereBetween('penjualan.tanggal', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    $actionBtn =  $row->jam_kerja()->first()->nama_jam_kerja;
                    return $actionBtn;
                })
                ->addColumn('stand_meter', function ($row) {
                    $actionBtn = array_column($row->detail_penjualan()->select('nozzle.nama_nozzle')->join('stand_meter', 'stand_meter.stand_meter_id', 'detail_penjualan.stand_meter_id')->join('nozzle', 'nozzle.nozzle_id', 'stand_meter.nozzle_id')->orderBy('nozzle.nama_nozzle', 'asc')->get()->toArray(), 'nama_nozzle');
                    return count($actionBtn) == 0 ? '-' : (count($actionBtn) > 1 ? implode(', ', $actionBtn) : $actionBtn['0']);
                })
                ->addColumn('total_uang_penjualan', function ($row) {
                    $actionBtn = 'Rp. ' . number_format($row->total_penjualan, 6, ".", ",");
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('setoran.closing.show', $row->penjualan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'status_top', 'status_admin'])
                ->make(true);
        }
        return view('setoran/closing_penjualan/index', compact('waktuMulai', 'waktuSelesai'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Penjualan $penjualan)
    {
        return view('setoran/closing_penjualan/show', compact('penjualan'));
    }

    public function confirmed_to(Penjualan $penjualan, DetailPenjualan $detail)
    {
        $detail->update([
            'is_confirmed' => 1
        ]);

        return redirect('/setoran/closing-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Penjualan Stand Meter sudah dikonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function confirmed_from(Penjualan $penjualan, DetailPenjualan $detail)
    {
        $detail->update([
            'is_confirmed' => 0
        ]);

        return redirect('/setoran/closing-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Penjualan Stand Meter sudah dibatalkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function confirmed_to_setoran(Penjualan $penjualan)
    {
        $status_setoran = StatusSetoran::where('nama_status', 'Setoran Ke Bank')->first();
        $penjualan->update([
            'status_setoran_id' => $status_setoran->status_setoran_id
        ]);
        return redirect('/setoran/setoran-bank/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Status sudah dipindahkan ke Setoran Bank.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function confirmed_from_setoran(Penjualan $penjualan)
    {
        $status_setoran = StatusSetoran::where('nama_status', 'Closing Penjualan')->first();
        $penjualan->update([
            'status_setoran_id' => $status_setoran->status_setoran_id
        ]);
        return redirect('/setoran/closing-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Status Setoran Bank sudah dipindahkan ke Closing.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
