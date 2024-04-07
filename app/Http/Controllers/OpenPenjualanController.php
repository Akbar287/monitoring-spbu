<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenjualanRequest;
use App\Models\JamKerja;
use App\Models\Nozzle;
use App\Models\Penjualan;
use App\Models\Spbu;
use App\Models\StandMeter;
use App\Models\StatusSetoran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class OpenPenjualanController extends Controller
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
            $data = StatusSetoran::where('nama_status', 'Open Penjualan')->first()->penjualan()->whereBetween('penjualan.tanggal', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    $actionBtn =  $row->jam_kerja()->first()->nama_jam_kerja;
                    return $actionBtn;
                })
                ->addColumn('total_stand_meter', function ($row) {
                    $actionBtn = $row->detail_penjualan()->count();
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('setoran.open.show', $row->penjualan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('setoran/open_penjualan/index', compact('waktuMulai', 'waktuSelesai'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::select('spbu_id')->addSelect('nama_spbu')->addSelect('nomor_spbu')->orderBy('nama_spbu', 'asc')->get();
        $jamKerja = JamKerja::orderBy('nama_jam_kerja', 'asc')->get();
        $nozzle = Nozzle::orderBy('nama_nozzle', 'asc')->get();
        return view('setoran/open_penjualan/create', compact('spbu', 'nozzle', 'jamKerja'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PenjualanRequest $penjualanRequest)
    {
        $spbu = Spbu::where('spbu_id', request('spbu_id'))->first();
        $ss = StatusSetoran::where('nama_status', 'Open Penjualan')->first()->status_setoran_id;

        if (!is_null($spbu)) {
            if (is_null($spbu->penjualan()->where('status_setoran_id', $ss)->where('jam_kerja_id', request('jam_kerja_id'))->where('tanggal', request('tanggal'))->first())) {
                $penjualan = $spbu->penjualan()->create([
                    'status_setoran_id' => $ss,
                    'spbu_id' => request('spbu_id'),
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'tanggal' => request('tanggal'),
                    'total_penjualan' => 0,
                    'total_uang_kotak' => 0,
                    'is_accepted_admin' => false,
                    'is_accepted_top' => false,
                ]);
            }
            $total = 0;

            foreach ($penjualanRequest->stand_meter_id as $smi) {
                $sm = StandMeter::where('stand_meter_id', $smi)->first();
                if (!is_null($sm)) {
                    $harga = $sm->nozzle()->first()->produk()->first()->harga()->where('is_default', 'true')->first();

                    if (is_null($penjualan->detail_penjualan()->where('stand_meter_id', $smi)->where('harga_id', $harga->harga_id)->where('liter', $sm->stand_meter_akhir - $sm->stand_meter_awal)->where('total_detail_jual', ($sm->stand_meter_akhir - $sm->stand_meter_awal) * $harga->harga_jual)->first())) {
                        $penjualan->detail_penjualan()->create([
                            'stand_meter_id' => $smi,
                            'harga_id' => $harga->harga_id,
                            'liter' => $sm->stand_meter_akhir - $sm->stand_meter_awal,
                            'total_detail_jual' => ($sm->stand_meter_akhir - $sm->stand_meter_awal) * $harga->harga_jual
                        ]);
                        $total += ($sm->stand_meter_akhir - $sm->stand_meter_awal) * $harga->harga_jual;
                    }
                }
            }
            $penjualan->update(['total_penjualan' => $total]);
            unset($spbu);
            unset($ss);
            unset($total);
            unset($sm);
            unset($harga);

            return redirect('/setoran/open-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penjualan sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        } else {
            return redirect('/setoran/open-penjualan/create')->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data SPBU tidak Valid.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Penjualan $penjualan)
    {
        return view('setoran/open_penjualan/show', compact('penjualan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penjualan $penjualan)
    {
        $spbu = Spbu::select('spbu_id')->addSelect('nama_spbu')->addSelect('nomor_spbu')->get();
        $jamKerja = JamKerja::get();
        return view('setoran/open_penjualan/edit', compact('penjualan', 'spbu', 'produk', 'dispenser', 'jamKerja'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PenjualanRequest $penjualanRequest, Penjualan $penjualan)
    {
        $penjualan->update([
            'jam_kerja_id' => request('jam_kerja_id'),
            'tanggal' => request('tanggal'),
        ]);

        return redirect('/setoran/open-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penjualan sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function update_status(Penjualan $penjualan)
    {
        if ($penjualan->status_setoran()->first()->nama_status == 'Open Penjualan') {
            $penjualan->update(['status_setoran_id' => StatusSetoran::where('nama_status', 'Penitipan Setoran')->first()->status_setoran_id]);
            return redirect('/setoran/open-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penjualan sudah dipindahkan ke Penitipan Setoran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        } else {
            $penjualan->update(['status_setoran_id' => StatusSetoran::where('nama_status', 'Open Penjualan')->first()->status_setoran_id]);
            return redirect('/setoran/open-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penjualan sudah dipindahkan kembali ke Open Penjualan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }
    public function destroy(Penjualan $penjualan)
    {
        $penjualan->delete();

        return redirect('/setoran/open-penjualan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penjualan sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
