<?php

namespace App\Http\Controllers;

use App\Models\PajakPembelianLib;
use App\Models\RencanaPembelian;
use App\Models\StatusPurchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PembelianController extends Controller
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
            $data = StatusPurchase::where("nama_status", "Pembelian")->first()->rencana_pembelian()->whereBetween('created_at', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    $actionBtn = $row->created_at->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('produk_id', function ($row) {
                    $actionBtn = $row->detail_rencana_pembelian()->count() . ' Produk';
                    return $actionBtn;
                })
                ->addColumn('member_id', function ($row) {
                    $actionBtn = $row->member()->first()->ktp()->first()->nama;
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pembelian.show', $row->rencana_pembelian_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('procurement/pembelian/index', compact('waktuMulai', 'waktuSelesai'));
    }

    /**
     * Display the specified resource.
     */
    public function show(RencanaPembelian $pembelian)
    {
        $pajak = PajakPembelianLib::where('is_aktif', true)->first();
        return view('procurement/pembelian/show', compact('pembelian', 'pajak'));
    }

    public function konfirmasi_harga(RencanaPembelian $pembelian)
    {
        $status = StatusPurchase::where('nama_status', "Pembayaran")->first();
        $pembelian->update([
            'tanggal_pembelian' => date('Y-m-d'),
            'status_purchase_id' => $status->status_purchase_id
        ]);
        return redirect('/procurement/pembelian')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pembelian sudah dilanjutkan ke tahap pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function konfirmasi_ulang(RencanaPembelian $pembelian)
    {
        $status = StatusPurchase::where('nama_status', "Rencana")->first();
        $pembelian->update([
            'status_purchase_id' => $status->status_purchase_id,
            'is_confirmation' => true,
            'is_denied' => false
        ]);
        return redirect('/procurement/pembelian')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Mengubah Data Pembelian dengan konfirmasi ditolak.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
