<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileLoRequest;
use App\Models\DetailRencanaPembelian;
use App\Models\FileLo;
use App\Models\Pengiriman;
use App\Models\RencanaPembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class FileLoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, RencanaPembelian $rencana, Pengiriman $pengiriman)
    {
        if ($request->ajax()) {
            $data = $pengiriman->file_lo()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('produk_id', function ($row) {
                    $actionBtn = $row->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('jumlah', function ($row) {
                    $actionBtn = number_format($row->jumlah, 0, ".", ",") . " (" . $row->satuan_jumlah . ")";
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('file_lo.show', [$row->pengiriman()->first()->rencana_pembelian()->first()->rencana_pembelian_id, $row->pengiriman()->first()->pengiriman_id, $row->file_lo_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('procurement/file_lo/index', compact('rencana', 'pengiriman'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(RencanaPembelian $rencana, Pengiriman $pengiriman)
    {
        $detailRencana = $rencana->detail_rencana_pembelian()->get();
        return view('procurement/file_lo/create', compact('rencana', 'pengiriman', 'detailRencana'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FileLoRequest $fileLoRequest, RencanaPembelian $rencana, Pengiriman $pengiriman)
    {
        $fileLoRequest->validate([
            'gambar' => 'required|max:5120|mimes:jpg,png,jpeg,bmp|image'
        ]);

        $foto = 'default.png';
        $filenameWithExt = null;
        if ($fileLoRequest->hasFile('gambar')) {
            $filenameWithExt = $fileLoRequest->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $fileLoRequest->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $fileLoRequest->file('gambar')->storeAs('public/file_lo', $foto);
        }

        $detailRencana = DetailRencanaPembelian::where('detail_rencana_pembelian_id', $fileLoRequest->detail_rencana_pembelian_id)->first();

        $file_lo = $pengiriman->file_lo()->create($this->fileLoData($detailRencana, $filenameWithExt, $foto));

        DB::table('detail_rencana_file')->insert([
            'detail_rencana_pembelian_id' => request('detail_rencana_pembelian_id'),
            'file_lo_id' => $file_lo->file_lo_id
        ]);

        return redirect('/procurement/delivery/' . $rencana->rencana_pembelian_id . '/' . $pengiriman->pengiriman_id . '/' . $file_lo->file_lo_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File LO sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(RencanaPembelian $rencana, Pengiriman $pengiriman, FileLo $file_lo)
    {
        return view('procurement/file_lo/show', compact('rencana', 'pengiriman', 'file_lo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaPembelian $rencana, Pengiriman $pengiriman, FileLo $file_lo)
    {
        return view('procurement/file_lo/edit', compact('rencana', 'pengiriman', 'file_lo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FileLoRequest $fileLoRequest, RencanaPembelian $rencana, Pengiriman $pengiriman, FileLo $file_lo)
    {
        $foto = $file_lo->nama_file;
        $filenameWithExt = $file_lo->nama_dokumen;

        if ($fileLoRequest->hasFile('gambar')) {
            if ($file_lo->nama_file != 'default.png') {
                Storage::delete("public/file_lo/" . $file_lo->nama_file);
            }

            $filenameWithExt = $fileLoRequest->file('gambar')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $fileLoRequest->file('gambar')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $fileLoRequest->file('gambar')->storeAs('public/file_lo', $foto);
        }

        $file_lo->update($this->fileLoData(null, $filenameWithExt, $foto));

        return redirect('/procurement/delivery/' . $rencana->rencana_pembelian_id . '/' . $pengiriman->pengiriman_id . '/' . $file_lo->file_lo_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File LO sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RencanaPembelian $rencana, Pengiriman $pengiriman, FileLo $file_lo)
    {
        if ($file_lo->nama_file != 'default.png') {
            Storage::delete("public/file_lo/" . $file_lo->nama_file);
        }

        DB::table('detail_rencana_file')->where('detail_rencana_pembelian_id', $rencana->detail_rencana_pembelian()->where('produk_id', $file_lo->produk_id)->first()->detail_rencana_pembelian_id)->delete();

        $file_lo->delete();

        return redirect('/procurement/delivery/' . $rencana->rencana_pembelian_id . '/' . $pengiriman->pengiriman_id . '/index')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data File LO sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function fileLoData($detailRencana = null, $namaDokumen = null, $namaFile = null)
    {
        if (is_null($detailRencana)) {
            return [
                'no_faktur' => request('no_faktur'),
                'no_lo' => request('no_lo'),
                'tanggal_upload' => date('Y-m-d'),
                'nama_dokumen' => $namaDokumen,
                'nama_file' => $namaFile
            ];
        } else {
            return [
                'produk_id' => $detailRencana->produk_id,
                'jumlah' => str_replace(',', '', request('jumlah')),
                'satuan_jumlah' => $detailRencana->satuan_jumlah,
                'no_faktur' => request('no_faktur'),
                'no_lo' => request('no_lo'),
                'tanggal_upload' => date('Y-m-d'),
                'nama_dokumen' => $namaDokumen,
                'nama_file' => $namaFile
            ];
        }
    }

    public function download(RencanaPembelian $rencana, Pengiriman $pengiriman, FileLo $file_lo)
    {
        return Storage::download('public/file_lo/' . $file_lo->nama_file);
    }
}
