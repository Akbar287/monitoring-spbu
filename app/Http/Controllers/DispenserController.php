<?php

namespace App\Http\Controllers;

use App\Http\Requests\DispenserRequest;
use App\Models\Dispenser;
use Yajra\DataTables\DataTables;
use App\Models\Payung;
use Illuminate\Http\Request;

class DispenserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Dispenser::select('dispenser.*')->addSelect('payung.nama_payung')->join('payung', 'payung.payung_id', 'dispenser.payung_id');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == true ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('dispenser.show', $row->dispenser_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/dispenser/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $payung = Payung::all();
        return view('master/dispenser/create', compact('payung'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DispenserRequest $dispenserRequest)
    {
        $payung = Payung::find($dispenserRequest->payung_id);
        $dispenser = $payung->dispenser()->create($this->dispenserData());

        return redirect('/master/dispenser/' . $dispenser->dispenser_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dispenser sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Dispenser $dispenser)
    {
        return view('master/dispenser/show', compact('dispenser'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dispenser $dispenser)
    {
        $payung = Payung::all();
        return view('master/dispenser/edit', compact('payung', 'dispenser'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DispenserRequest $dispenserRequest, Dispenser $dispenser)
    {
        $dispenser->update($this->dispenserData());

        return redirect('/master/dispenser/' . $dispenser->dispenser_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dispenser sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dispenser $dispenser)
    {
        $dispenser->delete();

        return redirect('/master/dispenser')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dispenser sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function dispenserData()
    {
        return [
            'nama_dispenser' => request('nama_dispenser'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif'),
        ];
    }
}
