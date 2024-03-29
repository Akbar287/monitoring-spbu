<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayungRequest;
use App\Models\Dombak;
use App\Models\Payung;
use App\Models\Spbu;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PayungController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Payung::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    $actionBtn = $row->is_aktif == true ? 'Aktif' : 'Tidak Aktif';
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('payung.show', $row->payung_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/payung/index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::all();
        return view('master/payung/create', compact('spbu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PayungRequest $payungRequest)
    {
        $spbu = Spbu::find($payungRequest->spbu_id);
        $payung = $spbu->payung()->create($this->payungData());

        return redirect('/master/payung/' . $payung->payung_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Payung sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payung $payung)
    {
        return view('master/payung/show', compact('payung'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payung $payung)
    {
        $spbu = Spbu::all();
        return view('master/payung/edit', compact('spbu', 'payung'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PayungRequest $payungRequest, Payung $payung)
    {
        $payung->update($this->payungData());

        return redirect('/master/payung/' . $payung->payung_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Payung sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payung $payung)
    {
        $payung->delete();

        return redirect('/master/payung')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Payung sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function payungData()
    {
        return [
            'nama_payung' => request('nama_payung'),
            'keterangan' => request('keterangan'),
            'is_aktif' => request('is_aktif'),
        ];
    }

    public function dombak(Request $request, Payung $payung)
    {
        if ($request->ajax()) {
            $data = Payung::select('payung.nama_payung')->addSelect('payung.payung_id')->addSelect('dombak_payung.dombak_id')->addSelect('dombak.nama_dombak')->join('dombak_payung', 'dombak_payung.payung_id', 'payung.payung_id')->join('dombak', 'dombak.dombak_id', 'dombak_payung.dombak_id')->where('payung.payung_id', $payung->payung_id)->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<form action="' . route('payung.dombak.delete', [$row->payung_id, $row->dombak_id]) . '" method="POST"><input type="hidden" name="_method" value="delete">' . csrf_field() . '<button class="edit btn btn-danger btn-sm">Hapus</button></form>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $dombak = Dombak::all();
        return view('master/payung/dombak', compact('payung'));
    }
    public function dombak_create(Request $request, Payung $payung)
    {
        if ($request->ajax()) {
            $data = Dombak::select('dombak.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_aktif', function ($row) {
                    return $row->is_aktif == true ? "Aktif" : "Tidak Aktif";
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = $row->payung()->where('payung.payung_id', Route::getCurrentRoute()->originalParameters()['payung'])->count() > 0 ? 'Sudah'  : '<form action="' . url('/master/payung/' . Route::getCurrentRoute()->originalParameters()['payung'] . '/dombak/' . $row->dombak_id) . '" method="POST">' . csrf_field() . '<button class="edit btn btn-success btn-sm" title="Pilih" type="submit">Pilih</button></form>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('master/payung/dombak_create', compact('payung'));
    }

    public function dombak_store(Payung $payung, Dombak $dombak)
    {
        $cek = DB::table('dombak_payung')->where(
            'dombak_id',
            $dombak->dombak_id
        )->where('payung_id', $payung->payung_id)->first();

        if (is_null($cek)) {
            DB::table('dombak_payung')->insert([
                'dombak_id' => $dombak->dombak_id,
                'payung_id' => $payung->payung_id,
            ]);
            return redirect('/master/payung/' . $payung->payung_id . '/dombak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dombak sudah ditambahkan ke Payung.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
        return redirect('/master/payung/' . $payung->payung_id . '/dombak')->with('msg', '<div class="alert alert-info alert-dismissible fade show" role="alert"><strong>Tidak ada Perubahan!</strong> Data Dombak sebelumnya sudah ditambahkan ke Payung.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function dombak_destroy(Payung $payung, Dombak $dombak)
    {
        DB::table('dombak_payung')->where(
            'dombak_id',
            $dombak->dombak_id
        )->where('payung_id', $payung->payung_id)->delete();

        return redirect('/master/payung/' . $payung->payung_id . '/dombak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dombak sudah dihapus dari payung.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
