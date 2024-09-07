<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembayaranRequest;
use App\Models\FilePembayaran;
use App\Models\Member;
use App\Models\Pembayaran;
use App\Models\RencanaPembelian;
use App\Models\StatusPurchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class PembayaranController extends Controller
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
            $data = StatusPurchase::where("nama_status", "Pembayaran")->first()->rencana_pembelian()->whereBetween('tanggal_pembelian', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    $actionBtn = Carbon::createFromFormat('Y-m-d', $row->tanggal_pembelian)->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('produk_id', function ($row) {
                    $actionBtn = $row->detail_rencana_pembelian()->count() . ' Produk';
                    return $actionBtn;
                })
                ->addColumn('grand_total', function ($row) {
                    $actionBtn = "Rp. " . number_format($row->grand_total, 6, '.', ',');
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pembayaran.show', $row->rencana_pembelian_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('procurement/pembayaran/index', compact('waktuMulai', 'waktuSelesai'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, RencanaPembelian $rencana)
    {
        if ($request->ajax()) {
            $data = $rencana->pembayaran()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_confirmation', function ($row) {
                    $actionBtn = ($row->is_confirmation_admin == 1 ? "<div class='badge badge-success'>Admin</div>" : "<div class='badge badge-danger'>Admin</div>") . ' ' . ($row->is_confirmation_top == 1 ? "<div class='badge badge-success'>Top</div>" : "<div class='badge badge-danger'>Top</div>");
                    return $actionBtn;
                })
                ->addColumn('total_setoran', function ($row) {
                    $actionBtn = "Rp. " . number_format($row->total_setoran, 6, '.', ',');
                    return $actionBtn;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pembayaran.detail', [$row->rencana_pembelian()->first()->rencana_pembelian_id, $row->pembayaran_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation'])
                ->make(true);
        }
        $continue = false;
        if ($rencana->pembayaran()->count() > 0) {
            foreach ($rencana->pembayaran()->get() as $p) {
                if ($p->is_confirmation_admin && $p->is_confirmation_top) {
                    $continue = true;
                }
            }
        }
        $pajak = $rencana->pajak_pembelian()->first();
        return view('procurement/pembayaran/show', compact('rencana', 'continue', 'pajak'));
    }

    public function konfirmasi_delivery(RencanaPembelian $rencana)
    {
        $status = StatusPurchase::where('nama_status', "MS2")->first();
        $rencana->update([
            'status_purchase_id' => $status->status_purchase_id,
        ]);

        // foreach ($rencana->detail_rencana_pembelian()->get() as $d) {
        //     $d->update(['ms2' => true]);
        // }
        return redirect('/procurement/pembayaran')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Mengubah Data Pembayaran. Data akan dikirim ke tahap MS2.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function konfirmasi_ditolak(RencanaPembelian $rencana)
    {
        $status = StatusPurchase::where('nama_status', "Rencana")->first();
        $rencana->update([
            'status_purchase_id' => $status->status_purchase_id,
            'is_confirmation' => 1,
            'is_denied' => 0
        ]);
        return redirect('/procurement/pembayaran')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Mengubah Data Pembayaran dengan konfirmasi ditolak. Data dikirim ke Rencana Pembelian tertolak.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function create(RencanaPembelian $rencana)
    {
        $member = Member::get();
        return view('procurement/pembayaran/create', compact('rencana', 'member'));
    }
    public function store(PembayaranRequest $pembayaranRequest, RencanaPembelian $rencana)
    {

        $pembayaran = $rencana->pembayaran()->where("member_id", request('member_id'))->where("no_cek_bg", request('no_cek_bg'))->where("no_rekening", request('no_rekening'))->where("nama_rekening", request('nama_rekening'))->where("total_setoran", str_replace(',', '', request('total_setoran')))->where("is_confirmation_top", 0)->where("is_confirmation_admin", 0)->where("tanggal", request('tanggal'))->first();
        if (is_null($pembayaran)) {
            $pembayaran = $rencana->pembayaran()->create($this->pembayaranData());
        }

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Menulis Data Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function detail(Request $request, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        if ($request->ajax()) {
            $data = $pembayaran->file_pembayaran()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_upload', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal_upload)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pembayaran.file.unduh', [$row->pembayaran()->first()->rencana_pembelian()->first()->rencana_pembelian_id, $row->pembayaran()->first()->pembayaran_id, $row->file_pembayaran_id]) . '" class="unduh btn btn-primary btn-sm">Unduh</a> <a href="' . route('pembayaran.file.show', [$row->pembayaran()->first()->rencana_pembelian()->first()->rencana_pembelian_id, $row->pembayaran()->first()->pembayaran_id, $row->file_pembayaran_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('procurement/pembayaran/detail', compact('rencana', 'pembayaran'));
    }
    public function edit(RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        $member = Member::get();
        return view('procurement/pembayaran/edit', compact('rencana', 'pembayaran', 'member'));
    }
    public function update(PembayaranRequest $pembayaranRequest, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        $pembayaran->update($this->pembayaranData());

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Mengubah Data Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function update_confirmation_admin_ok(Request $request, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        if (Auth::user()->member()->first()->jabatan()->first()->nama_jabatan == 'Admin') {
            $pembayaran->update(['is_confirmation_admin' => 1]);
            return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Konfirmasi Pembayaran - Admin.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }
    public function update_confirmation_admin_no(Request $request, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        if (Auth::user()->member()->first()->jabatan()->first()->nama_jabatan == 'Admin') {
            $pembayaran->update(['is_confirmation_admin' => 0]);

            return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Membatalkan Konfirmasi Pembayaran - Admin.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }
    public function update_confirmation_top_ok(Request $request, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        // if (Auth::user()->member()->first()->jabatan()->first()->level()->first()->divisi()->first()->nama_divisi == 'Top Manajemen') {
        $pembayaran->update(['is_confirmation_top' => 1]);

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Konfirmasi Pembayaran - Top Manajemen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        // }
    }
    public function update_confirmation_top_no(Request $request, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        // if (Auth::user()->member()->first()->jabatan()->first()->level()->first()->divisi()->first()->nama_divisi == 'Top Manajemen') {
        $pembayaran->update(['is_confirmation_top' => 0]);

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Membatalkan Konfirmasi Pembayaran - Top Manajemen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        // }
    }

    public function destroy(RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        $files = $pembayaran->file_pembayaran()->get();
        foreach ($files as $file) {
            if ($file->nama_file != 'default.png') {
                Storage::delete("public/pembayaran/" . $file->nama_file);
            }
        }
        $pembayaran->delete();

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Menghapus Data Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function pembayaranData()
    {
        return [
            "member_id" => request('member_id'),
            "no_cek_bg" => request('no_cek_bg'),
            "no_rekening" => request('no_rekening'),
            "nama_rekening" => request('nama_rekening'),
            "total_setoran" => str_replace(',', '', request('total_setoran')),
            "is_confirmation_top" => 0,
            "is_confirmation_admin" => 0,
            "tanggal" => request('tanggal'),
        ];
    }

    public function file_create(RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        return view('/procurement/file_pembayaran/create', compact('rencana', 'pembayaran'));
    }
    public function file_store(RencanaPembelian $rencana, Pembayaran $pembayaran, Request $request)
    {
        $foto = $request->nama_file;
        if ($request->hasFile('file')) {
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('file')->storeAs('public/pembayaran', $foto);
        }
        $file = $pembayaran->file_pembayaran()->where("nama_file", $foto)->where("nama_dokumen", $filenameWithExt)->where("tanggal_upload", date('Y-m-d'))->first();
        if (is_null($file)) {
            $file = $pembayaran->file_pembayaran()->create($this->filePembayaranData($foto, $filenameWithExt));
        }

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id . '/' . $file->file_pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil menambah Data File Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function file_show(RencanaPembelian $rencana, Pembayaran $pembayaran, FilePembayaran $file)
    {
        return view('/procurement/file_pembayaran/show', compact('rencana', 'pembayaran', 'file'));
    }
    public function file_edit(RencanaPembelian $rencana, Pembayaran $pembayaran, FilePembayaran $file)
    {
        return view('/procurement/file_pembayaran/edit', compact('rencana', 'pembayaran', 'file'));
    }
    public function file_update(RencanaPembelian $rencana, Pembayaran $pembayaran, FilePembayaran $file, Request $request)
    {
        $foto = $request->nama_file;
        if ($request->hasFile('file')) {
            if ($file->nama_file != 'default.png') {
                Storage::delete("public/pembayaran/" . $file->nama_file);
            }

            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('file')->storeAs('public/pembayaran', $foto);
        }

        $file->update($this->filePembayaranData($foto, $filenameWithExt));

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id . '/' . $file->file_pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Mengubah Data File Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function file_destroy(RencanaPembelian $rencana, Pembayaran $pembayaran, FilePembayaran $file)
    {
        if ($file->nama_file != 'default.png') {
            Storage::delete("public/pembayaran/" . $file->nama_file);
        }
        $file->delete();

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Menghapus Data File Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function filePembayaranData($namaFile, $namaDokumen)
    {
        return [
            "nama_file" => $namaFile,
            "nama_dokumen" => $namaDokumen,
            "tanggal_upload" => date('Y-m-d')
        ];
    }
    public function file_unduh(RencanaPembelian $rencana, Pembayaran $pembayaran, FilePembayaran $file)
    {
        return Storage::download("public/pembayaran/" . $file->nama_file);
    }

    public function pajak_edit(RencanaPembelian $rencana)
    {
        return view('/procurement/pembayaran/pajak_edit', compact('rencana'));
    }

    public function pajak_update(Request $request, RencanaPembelian $rencana)
    {
        $request->validate([
            'net_price' => ['required'],
            'ppn' => ['required'],
            'ppbkb' => ['required'],
            'pph' => ['required'],
        ]);

        if ($rencana->pajak_pembelian()->count() > 0) {
            $rencana->pajak_pembelian()->first()->update($this->pajakData($rencana->grand_total));
        } else {
            $rencana->pajak_pembelian()->create($this->pajakData($rencana->grand_total));
        }

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil mengubah data Pajak Pembelian.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function pajakData($grand_total)
    {
        return [
            'net_price' => str_replace(',', '', request('net_price')),
            'ppn' => str_replace(',', '', request('ppn')),
            'ppbkb' => str_replace(',', '', request('ppbkb')),
            'pph' => str_replace(',', '', request('pph')),
            'total_amount' => $grand_total
        ];
    }
}
