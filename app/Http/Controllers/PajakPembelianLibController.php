<?php

namespace App\Http\Controllers;

use App\Http\Requests\PajakPembelianLibRequest;
use App\Models\PajakPembelianLib;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PajakPembelianLibController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PajakPembelianLib::orderBy('created_at', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->format('d F Y');
                })
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == true ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pajak_pembelian_lib.show', $row->pajak_pembelian_lib_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('konfigurasi/pajak/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('konfigurasi/pajak/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PajakPembelianLibRequest $pajakPembelianLibRequest)
    {
        $pajakPembelianLib = PajakPembelianLib::create($this->pajakPembelianLibData());

        return redirect('/konfigurasi/pajak/' . $pajakPembelianLib->pajak_pembelian_lib_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pajak Pembelian Library sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(PajakPembelianLib $pajak)
    {
        return view('konfigurasi/pajak/show', compact('pajak'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PajakPembelianLib $pajak)
    {
        return view('konfigurasi/pajak/edit', compact('pajak'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PajakPembelianLibRequest $pajakPembelianLibRequest, PajakPembelianLib $pajak)
    {
        $pajak->update($this->pajakPembelianLibData());

        return redirect('/konfigurasi/pajak/' . $pajak->pajak_pembelian_lib_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pajak Pembelian Library sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PajakPembelianLib $pajak)
    {
        $pajak->delete();

        return redirect('/konfigurasi/pajak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pajak Pembelian Library sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function pajakPembelianLibData()
    {
        return [
            'ppn' => request('ppn'),
            'ppbkb' => request('ppbkb'),
            'pph' => request('pph'),
            'is_aktif' => request('is_aktif'),
        ];
    }
}
