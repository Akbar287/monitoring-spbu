<?php

namespace App\Http\Controllers;

use App\Models\FileLo;
use App\Models\Pengiriman;
use App\Models\RencanaPembelian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PengirimanController extends Controller
{

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
            $data = Pengiriman::where('ms2', false)->where('is_done', false)->where('is_confirmation_admin', true)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    $actionBtn = Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('no_pol', function ($row) {
                    $actionBtn = $row->no_pol;
                    return $actionBtn;
                })
                ->addColumn('no_lo', function ($row) {
                    $actionBtn = $row->no_lo;
                    return $actionBtn;
                })
                ->addColumn('is_done', function ($row) {
                    return $row->is_done ? 'Selesai' : 'Belum Selesai';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pengiriman.show', $row->pengiriman_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation', 'status_selesai'])
                ->make(true);
        }
        return view('procurement/pengiriman/index', compact('waktuMulai', 'waktuSelesai'));
    }

    public function show(Request $request, RencanaPembelian $rencana, Pengiriman $pengiriman)
    {
        if ($request->ajax()) {
            $data = $pengiriman->file_lo()->select('file_lo.file_lo_id')->addSelect('file_lo.pengiriman_id')->addSelect('file_lo.jumlah')->addSelect('file_lo.satuan_jumlah')->addSelect('produk.nama_produk')->addSelect('file_lo.no_lo')->addSelect('file_lo.no_faktur')->join('produk', 'produk.produk_id', 'file_lo.produk_id')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn = $row->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('jumlah', function ($row) {
                    $actionBtn = number_format($row->jumlah, 2, ".", ",") . ' ' . $row->satuan_jumlah;
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pengiriman.file_lo.show', [$row->pengiriman_id, $row->file_lo_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation', 'status_selesai'])
                ->make(true);
        }
        return view('procurement/pengiriman/show', compact('pengiriman', 'rencana'));
    }

    public function confirmation(RencanaPembelian $rencana, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_admin' => true
        ]);

        return redirect('/procurement/delivery')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pengiriman sudah dikonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function done(RencanaPembelian $rencana, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_done' => true,
        ]);

        foreach ($pengiriman->file_lo()->get() as $p) {
            $p->detail_rencana_pembelian()->first()->update([
                'delivery' => false
            ]);
        }

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pengiriman sudah dikonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function show_file_lo(Pengiriman $pengiriman, FileLo $file_lo)
    {
        $detail = $file_lo->detail_rencana_pembelian()->first();
        return view('procurement/pengiriman/show_file_lo', compact('pengiriman', 'file_lo', 'detail'));
    }
}
