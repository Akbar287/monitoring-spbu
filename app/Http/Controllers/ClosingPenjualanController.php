<?php

namespace App\Http\Controllers;

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
                ->addColumn('produk', function ($row) {
                    $actionBtn =  $row->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('dispenser', function ($row) {
                    $actionBtn =  $row->dispenser()->first()->nama_dispenser;
                    return $actionBtn;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    $actionBtn =  $row->jam_kerja()->first()->nama_jam_kerja;
                    return $actionBtn;
                })
                ->addColumn('total_uang', function ($row) {
                    $actionBtn = 'Rp. ' . number_format($row->total_penjualan, 0, ".", ",");
                    return $actionBtn;
                })
                ->addColumn('status_admin', function ($row) {
                    $actionBtn = $row->is_accepted_admin ? '<div class="badge badge-success">Konfirmasi</div>' : '<div class="badge badge-danger">Tidak Terkonfirmasi</div>';
                    return $actionBtn;
                })
                ->addColumn('status_top', function ($row) {
                    $actionBtn = $row->is_accepted_top ? '<div class="badge badge-success">Konfirmasi</div>' : '<div class="badge badge-danger">Tidak Terkonfirmasi</div>';
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

    public function update(Request $request, Penjualan $penjualan)
    {
        $request->validate([
            'type' => ['required']
        ]);

        if ($request->type == 'admin') {
            $penjualan->update(['is_accepted_admin' => ($penjualan->is_accepted_admin ? false : true)]);

            return redirect('/setoran/closing-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penjualan ' . (!$penjualan->is_accepted_admin ? 'Tidak Dikonfirmasi' : 'Dikonfirmasi') . '.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
        if ($request->type == 'top') {
            $penjualan->update(['is_accepted_top' => ($penjualan->is_accepted_top ? false : true)]);

            return redirect('/setoran/closing-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penjualan ' . (!$penjualan->is_accepted_top ? 'Tidak Dikonfirmasi' : 'Dikonfirmasi') . '.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }
}
