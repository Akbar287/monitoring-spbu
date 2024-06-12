<?php

namespace App\Http\Controllers;

use App\Models\DetailRencanaPembelian;
use App\Models\FileLo;
use App\Models\Pengiriman;
use App\Models\RencanaPembelian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            $data = Pengiriman::where('is_done', false)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->get();
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
        $available = $pengiriman->file_lo()->select('detail_rencana_pembelian_id')->join('detail_rencana_file', 'detail_rencana_file.file_lo_id', 'file_lo.file_lo_id')->get()->toArray();
        $temp = $pengiriman->ms2()->first()->detail_rencana_pembelian_ms2()->where('is_delivery', true)->whereNotIn('detail_rencana_pembelian_id', $available)->select('detail_rencana_pembelian_id')->get()->toArray();

        if ($request->ajax()) {
            $data = $pengiriman->file_lo()->select('file_lo.file_lo_id')->addSelect('file_lo.pengiriman_id')->addSelect('file_lo.jumlah')->addSelect('file_lo.satuan_jumlah')->addSelect('produk.nama_produk')->addSelect('file_lo.no_lo')->addSelect('file_lo.no_faktur')->join('produk', 'produk.produk_id', 'file_lo.produk_id')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn = $row->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('jumlah', function ($row) {
                    $actionBtn = number_format($row->jumlah, 6, ".", ",") . ' ' . $row->satuan_jumlah;
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pengiriman.file_lo.show', [$row->pengiriman_id, $row->file_lo_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation', 'status_selesai'])
                ->make(true);
        }
        return view('procurement/pengiriman/show', compact('pengiriman', 'rencana', 'temp'));
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

        // foreach ($pengiriman->file_lo()->get() as $p) {
        //     $p->detail_rencana_pembelian()->first()->update([
        //         'delivery' => false
        //     ]);
        // }

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pengiriman sudah dikonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function show_file_lo(Pengiriman $pengiriman, FileLo $file_lo)
    {
        $detail = $file_lo->detail_rencana_pembelian()->first();
        return view('procurement/pengiriman/show_file_lo', compact('pengiriman', 'file_lo', 'detail'));
    }

    public function file_lo_create(Request $request, Pengiriman $pengiriman)
    {
        $available = $pengiriman->file_lo()->select('detail_rencana_pembelian_id')->join('detail_rencana_file', 'detail_rencana_file.file_lo_id', 'file_lo.file_lo_id')->get()->toArray();
        $temp = $pengiriman->ms2()->first()->detail_rencana_pembelian_ms2()->where('is_delivery', true)->whereNotIn('detail_rencana_pembelian_id', $available)->select('detail_rencana_pembelian_id')->get()->toArray();
        $detail_rencana_pembelian = DetailRencanaPembelian::whereIn('detail_rencana_pembelian_id', $temp)->get();
        return view('procurement/pengiriman/file_lo/file_lo_create', compact('pengiriman', 'detail_rencana_pembelian'));
    }

    public function file_lo_store(Request $request, Pengiriman $pengiriman)
    {
        $request->validate([
            'detail_rencana_pembelian_id' => ['required'],
            'no_faktur' => ['required'],
            'no_lo' => ['required'],
            'gambar' => 'required|max:5120|mimes:jpg,png,jpeg,bmp|image'
        ]);

        $foto = 'default.png';
        $filenameWithExt = null;
        if ($request->hasFile('gambar')) {
            $filenameWithExt = $request->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('gambar')->storeAs('public/file_lo', $foto);
        }

        $detail_rencana_pembelian = DetailRencanaPembelian::where('detail_rencana_pembelian_id', $request->detail_rencana_pembelian_id)->first();

        $detail_rencana_pembelian->update([
            'ms2' => false
        ]);

        $file_lo = $pengiriman->file_lo()->where('produk_id', $detail_rencana_pembelian->produk_id)->where('jumlah', $detail_rencana_pembelian->jumlah)->where('satuan_jumlah', $detail_rencana_pembelian->satuan_jumlah)->where('no_faktur', request('no_faktur'))->where('no_lo', request('no_lo'))->where('nama_dokumen', $filenameWithExt)->where('nama_file', $foto)->where('tanggal_upload', date('Y-m-d'))->first();
        if (is_null($file_lo)) {
            $file_lo = $pengiriman->file_lo()->create([
                'produk_id' => $detail_rencana_pembelian->produk_id,
                'jumlah' => $detail_rencana_pembelian->jumlah,
                'satuan_jumlah' => $detail_rencana_pembelian->satuan_jumlah,
                'no_faktur' => request('no_faktur'),
                'no_lo' => request('no_lo'),
                'nama_dokumen' => $filenameWithExt,
                'nama_file' => $foto,
                'tanggal_upload' => date('Y-m-d'),
            ]);

            DB::table('detail_rencana_file')->insert([
                'detail_rencana_pembelian_id' => request('detail_rencana_pembelian_id'),
                'file_lo_id' => $file_lo->file_lo_id
            ]);
        }

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id . '/file_lo_show/' . $file_lo->file_lo_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File LO sudah Di simpan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function delete_file_lo(Pengiriman $pengiriman, FileLo $file_lo)
    {
        DB::table('detail_rencana_file')->where('file_lo_id', $file_lo->file_lo_id)->delete();

        if ($file_lo->nama_file != 'default.png') {
            Storage::delete("public/file_lo/" . $file_lo->nama_file);
        }

        $file_lo->delete();

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File LO sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function admin_top(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_admin' => true
        ]);

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Pengiriman sudah dikonfirmasi oleh Admin.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function admin_down(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_admin' => false
        ]);

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Pengiriman sudah di batalkan konfirmasi oleh Admin.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function top_top(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_top' => true
        ]);

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Pengiriman sudah dikonfirmasi oleh Top Manajemen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function top_down(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_top' => false
        ]);

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Pengiriman sudah dibatalkan konfirmasi oleh Top Manajemen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function ubah_catatan(Pengiriman $pengiriman)
    {
        return view('procurement/pengiriman/catatan', compact('pengiriman'));
    }

    public function update_catatan(Request $request, Pengiriman $pengiriman)
    {
        $request->validate([
            'catatan' => ['required']
        ]);

        $pengiriman->update([
            'catatan' => trim($request->catatan)
        ]);

        return redirect('/procurement/delivery/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Catatan Pengiriman sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
