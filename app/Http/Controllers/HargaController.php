<?php

namespace App\Http\Controllers;

use App\Http\Requests\HargaRequest;
use App\Models\Harga;
use App\Models\JamKerja;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class HargaController extends Controller
{
    public function riwayat_index(Request $request)
    {
        if ($request->ajax()) {
            $data = Harga::where('is_default', 0)->orderBy('tanggal', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('produk', function ($row) {
                    return $row->produk()->first()->nama_produk;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->jam_kerja()->first()->nama_jam_kerja;
                })
                ->addColumn('harga_beli', function ($row) {
                    return 'Rp. ' . number_format($row->harga_beli, 6, ".", ",");
                })
                ->addColumn('harga_jual', function ($row) {
                    return 'Rp. ' . number_format($row->harga_jual, 6, ".", ",");
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('harga.riwayat.show', $row->harga_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/harga/index_riwayat');
    }
    public function riwayat_show(Harga $harga)
    {
        return view('master/harga/show_riwayat', compact('harga'));
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Harga::where('is_default', 1)->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('produk', function ($row) {
                    return $row->produk()->first()->nama_produk;
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->jam_kerja()->first()->nama_jam_kerja;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('harga_beli', function ($row) {
                    return 'Rp. ' . number_format($row->harga_beli, 6, ".", ",");
                })
                ->addColumn('harga_jual', function ($row) {
                    return 'Rp. ' . number_format($row->harga_jual, 6, ".", ",");
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('harga.show', $row->harga_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/harga/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jamKerja = JamKerja::get();
        $produk = Produk::where('is_aktif', 1)->get();
        return view('master/harga/create', compact('jamKerja', 'produk'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HargaRequest $hargaRequest)
    {
        $produk = Produk::where('produk_id', $hargaRequest->produk_id)->first();

        $check = $produk->harga()->where('is_default', 1)->first();
        if (!is_null($check)) {
            $check->update([
                'is_default' => 0
            ]);
        }


        $harga = $produk->harga()->where('jam_kerja_id', request('jam_kerja_id'))->where('tanggal', request('tanggal'))->where('harga_jual', str_replace(',', '', request('harga_jual')))->where('harga_beli', str_replace(',', '', request('harga_beli')))->where('is_default', 1)->first();
        if (is_null($harga)) {
            $harga = $produk->harga()->create($this->hargaData());
        }

        return redirect('/master/harga/' . $harga->harga_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Harga sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Harga $harga)
    {
        return view('master/harga/show', compact('harga'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Harga $harga)
    {
        $jamKerja = JamKerja::get();
        $produk = Produk::where('is_aktif', 1)->get();
        return view('master/harga/edit', compact('harga', 'jamKerja', 'produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HargaRequest $hargaRequest, Harga $harga)
    {
        $harga->update($this->hargaData());

        return redirect('/master/harga/' . $harga->harga_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Harga sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Harga $harga)
    {
        $temp = $harga->is_default;

        $harga->delete();

        if ($temp) {
            $harga->produk()->first()->harga()->orderBy('tanggal', 'desc')->first()->update([
                'is_default' => 1
            ]);
        }

        return redirect('/master/harga')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Harga sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function hargaData()
    {
        return [
            'jam_kerja_id' => request('jam_kerja_id'),
            'tanggal' => request('tanggal'),
            'harga_jual' => str_replace(',', '', request('harga_jual')),
            'harga_beli' => str_replace(',', '', request('harga_beli')),
            'is_default' => 1,
        ];
    }
}
