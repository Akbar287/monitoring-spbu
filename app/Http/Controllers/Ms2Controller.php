<?php

namespace App\Http\Controllers;

use App\Models\DetailRencanaPembelian;
use App\Models\FileLo;
use App\Models\Member;
use App\Models\Pengiriman;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class Ms2Controller extends Controller
{
    public function index_produk(Request $request)
    {
        if ($request->ajax()) {
            $data = Produk::where('is_aktif', true)->orderBy('nama_produk', 'asc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('stok', function ($row) {
                    return number_format($row->detail_rencana_pembelian()->selectRaw('SUM("jumlah") as stok')->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'detail_rencana_pembelian.rencana_pembelian_id')->join('status_purchase', 'status_purchase.status_purchase_id', 'rencana_pembelian.status_purchase_id')->where('status_purchase.nama_status', 'MS2')->where('detail_rencana_pembelian.ms2', true)->first()->stok, 6, ".", ",") ?? 0;
                })
                ->addColumn('total_pembelian', function ($row) {
                    return $row->detail_rencana_pembelian()->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'detail_rencana_pembelian.rencana_pembelian_id')->join('status_purchase', 'status_purchase.status_purchase_id', 'rencana_pembelian.status_purchase_id')->where('status_purchase.nama_status', 'MS2')->where('detail_rencana_pembelian.ms2', true)->count();
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('ms2.produk.show', $row->produk_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('procurement/ms2/index_produk');
    }
    public function show_produk(Request $request, Produk $produk)
    {
        if ($request->ajax()) {
            $data = $produk->detail_rencana_pembelian()->select('rencana_pembelian.kode_pembelian')->addSelect('rencana_pembelian.tanggal_pembelian')->addSelect('detail_rencana_pembelian.jumlah')->addSelect('detail_rencana_pembelian.detail_rencana_pembelian_id')->addSelect('detail_rencana_pembelian.produk_id')->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'detail_rencana_pembelian.rencana_pembelian_id')->join('status_purchase', 'status_purchase.status_purchase_id', 'rencana_pembelian.status_purchase_id')->where('status_purchase.nama_status', 'MS2')->where('detail_rencana_pembelian.ms2', true)->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_pembelian', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal_pembelian)->format('d F Y') ?? 0;
                })
                ->addColumn('jumlah', function ($row) {
                    return number_format($row->jumlah, 6, ".", ",") ?? 0;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('ms2.detail.show', [$row->produk_id, $row->detail_rencana_pembelian_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('procurement/ms2/show_produk');
    }
    public function show_detail(Request $request, Produk $produk, DetailRencanaPembelian $detail)
    {
        return view('procurement/ms2/show_detail', compact('produk', 'detail'));
    }

    public function index_pengiriman(Request $request)
    {
        if ($request->ajax()) {
            $data = Pengiriman::where('ms2', true)->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y') ?? 0;
                })
                ->addColumn('no_do', function ($row) {
                    return $row->no_do;
                })
                ->addColumn('no_pol', function ($row) {
                    return $row->no_pol;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('ms2.pengiriman.show', $row->pengiriman_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('procurement/ms2/index_pengiriman');
    }
    public function create_pengiriman(Request $request)
    {
        $member = Member::get();
        return view('procurement/ms2/create_pengiriman', compact('member'));
    }
    public function store_pengiriman(Request $request)
    {
        $pengiriman = Pengiriman::where('member_id', request('member_id'))->where('no_do', request('no_do'))->where('no_pol', request('no_pol'))->where('tanggal', request('tanggal'))->where('is_confirmation_admin', false)->where('is_done', false)->where('ms2', true)->where('catatan', request('catatan'))->first();
        if (is_null($pengiriman)) {
            $pengiriman = Pengiriman::create($this->pengirimanData());
        }

        return redirect('/procurement/ms2/pengiriman/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pengiriman sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function show_pengiriman(Request $request, Pengiriman $pengiriman)
    {
        if ($request->ajax()) {
            $data = $pengiriman->file_lo()
                ->select('file_lo.no_lo')
                ->addSelect('rencana_pembelian.tanggal_pembelian')
                ->addSelect('rencana_pembelian.kode_pembelian')
                ->addSelect('produk.nama_produk')
                ->addSelect('file_lo.jumlah')
                ->addSelect('file_lo.file_lo_id')
                ->addSelect('pengiriman.pengiriman_id')
                ->join('pengiriman', 'pengiriman.pengiriman_id', 'file_lo.pengiriman_id')
                ->join('detail_rencana_file', 'detail_rencana_file.file_lo_id', 'file_lo.file_lo_id')
                ->join('detail_rencana_pembelian', 'detail_rencana_pembelian.detail_rencana_pembelian_id', 'detail_rencana_file.detail_rencana_pembelian_id')
                ->join('rencana_pembelian', 'detail_rencana_pembelian.rencana_pembelian_id', 'rencana_pembelian.rencana_pembelian_id')
                ->join('produk', 'produk.produk_id', 'file_lo.produk_id')
                ->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal_pembelian)->format('d F Y') ?? 0;
                })
                ->addColumn('kode', function ($row) {
                    return $row->kode_pembelian;
                })
                ->addColumn('stok', function ($row) {
                    return number_format($row->jumlah, 6, ".", ",") ?? 0;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('ms2.pengiriman.file_lo_show', [$row->pengiriman_id, $row->file_lo_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('procurement/ms2/show_pengiriman', compact('pengiriman'));
    }
    public function edit_pengiriman(Request $request, Pengiriman $pengiriman)
    {
        $member = Member::get();
        return view('procurement/ms2/edit_pengiriman', compact('pengiriman', 'member'));
    }
    public function update_pengiriman(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->update($this->pengirimanData());

        return redirect('/procurement/ms2/pengiriman/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pengiriman sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function update_pengiriman_to_delivery(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->update([
            'is_confirmation_admin' => true,
            'ms2' => false
        ]);

        $detail = $pengiriman->file_lo()->select('detail_rencana_file.detail_rencana_pembelian_id')->join('detail_rencana_file', 'detail_rencana_file.file_lo_id', 'file_lo.file_lo_id')->get();

        foreach ($detail as $d) {
            DetailRencanaPembelian::where('detail_rencana_pembelian_id', $d->detail_rencana_pembelian_id)->first()->update([
                'ms2' => false,
                'delivery' => true,
            ]);
        }

        return redirect('/procurement/ms2/pengiriman')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data MS2 sudah dikirim ke Fase Pengiriman.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function destroy_pengiriman(Request $request, Pengiriman $pengiriman)
    {
        $pengiriman->delete();

        return redirect('/procurement/ms2/pengiriman')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pengiriman sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function pengirimanData()
    {
        return [
            'member_id' => request('member_id'),
            'no_do' => request('no_do'),
            'no_pol' => request('no_pol'),
            'tanggal' => request('tanggal'),
            'is_confirmation_admin' => false,
            'is_done' => false,
            'ms2' => true,
            'catatan' => request('catatan')
        ];
    }

    public function file_lo_create(Request $request, Pengiriman $pengiriman)
    {
        $detail_rencana_pembelian = DetailRencanaPembelian::where('ms2', true)->get();
        return view('procurement/ms2/file_lo_create', compact('pengiriman', 'detail_rencana_pembelian'));
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


        return redirect('/procurement/ms2/pengiriman/' . $pengiriman->pengiriman_id . '/file_lo_show/' . $file_lo->file_lo_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File LO sudah Di simpan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function file_lo_show(Pengiriman $pengiriman, FileLo $file_lo)
    {
        return view('procurement/ms2/file_lo_show', compact('pengiriman', 'file_lo'));
    }

    public function file_lo_delete(Pengiriman $pengiriman, FileLo $file_lo)
    {
        $file_lo->detail_rencana_pembelian()->first()->update([
            'ms2' => true
        ]);

        DB::table('detail_rencana_file')->where('file_lo_id', $file_lo->file_lo_id)->delete();

        if ($file_lo->nama_file != 'default.png') {
            Storage::delete("public/file_lo/" . $file_lo->nama_file);
        }

        $file_lo->delete();

        return redirect('/procurement/ms2/pengiriman/' . $pengiriman->pengiriman_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File LO sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
