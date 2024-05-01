<?php

namespace App\Http\Controllers;

use App\Http\Requests\PettyCashRequest;
use App\Models\PettyCash;
use App\Models\Spbu;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PettyCashController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PettyCash::orderBy('created_at', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('jumlah', function ($row) {
                    $actionBtn = 'Rp. ' . number_format($row->jumlah, 6, '.', ',');
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('petty-cash.show', $row->petty_cash_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('financial/petty_cash/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::get();
        return view('financial/petty_cash/create', compact('spbu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PettyCashRequest $pettyCashRequest)
    {
        $spbu = Spbu::where('spbu_id', $pettyCashRequest->spbu_id)->first();

        $pettyCash = $spbu->petty_cash()->where('no_kode', request('no_kode'))->where('no_bukti', request('no_bukti'))->where('keterangan', request('keterangan'))->where('jumlah', str_replace(',', '', request('jumlah')))->where('jenis', request('jenis'))->where('tanggal', request('tanggal'))->first();
        if (is_null($pettyCash)) {
            $pettyCash = $spbu->petty_cash()->create($this->pettyCashData());
        }

        return redirect('/financial/petty-cash/' . $pettyCash->petty_cash_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Petty Cash sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(PettyCash $pettyCash)
    {
        return view('financial/petty_cash/show', compact('pettyCash'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PettyCash $pettyCash)
    {
        $spbu = Spbu::get();
        return view('financial/petty_cash/edit', compact('pettyCash', 'spbu'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PettyCashRequest $pettyCashRequest, PettyCash $pettyCash)
    {
        $pettyCash->update($this->pettyCashData());

        return redirect('/financial/petty-cash/' . $pettyCash->petty_cash_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Petty Cash sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PettyCash $pettyCash)
    {
        $pettyCash->delete();

        return redirect('/financial/petty-cash')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Petty Cash sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function pettyCashData()
    {
        return [
            'no_kode' => request('no_kode'),
            'no_bukti' => request('no_bukti'),
            'keterangan' => request('keterangan'),
            'jumlah' => str_replace(',', '', request('jumlah')),
            'jenis' => request('jenis'),
            'tanggal' => request('tanggal'),
        ];
    }
}
