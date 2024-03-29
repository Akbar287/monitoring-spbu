<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenjualanRequest;
use App\Models\Dispenser;
use App\Models\JamKerja;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Spbu;
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
                ->addColumn('produk', function ($row) {
                    $actionBtn =  $row->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('dispenser', function ($row) {
                    $actionBtn =  $row->dispenser()->first()->nama_dispenser;
                    return $actionBtn;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    $actionBtn =  $row->jam_kerja()->first()->nama_jam_kerja;
                    return $actionBtn;
                })
                ->addColumn('total_penjualan', function ($row) {
                    $actionBtn = 'Rp. ' . number_format($row->total_penjualan, 0, ".", ",");
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
        $spbu = Spbu::select('spbu_id')->addSelect('nama_spbu')->addSelect('nomor_spbu')->get();
        $produk = Produk::where('is_aktif', true)->get();
        $dispenser = Dispenser::where('is_aktif', true)->get();
        $jamKerja = JamKerja::get();
        return view('setoran/open_penjualan/create', compact('spbu', 'produk', 'dispenser', 'jamKerja'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PenjualanRequest $penjualanRequest)
    {
        $spbu = Spbu::where('spbu_id', request('spbu_id'))->first();
        $dispenser = Dispenser::where('dispenser_id', request('dispenser_id'))->first();
        $harga = Produk::where('produk_id', request('produk_id'))->first()->harga()->where('is_default', true)->first();

        if (!is_null($spbu)) {
            $penjualan = $spbu->penjualan()->create($this->openPenjualanData());
            $total = str_replace(',', '', request('total_uang_kotak'));

            if ($dispenser->nozzle()->select('nozzle.*')->addSelect('stand_meter.*')->leftJoin('stand_meter', 'stand_meter.nozzle_id', 'nozzle.nozzle_id')->where('nozzle.produk_id', request('produk_id'))->where('stand_meter.jam_kerja_id', request('jam_kerja_id'))->where('stand_meter.is_confirmation', true)->where('stand_meter.tanggal', request('tanggal'))->count() > 0) {
                foreach ($dispenser->nozzle()->select('nozzle.*')->addSelect('stand_meter.*')->leftJoin('stand_meter', 'stand_meter.nozzle_id', 'nozzle.nozzle_id')->where('nozzle.produk_id', request('produk_id'))->where('stand_meter.jam_kerja_id', request('jam_kerja_id'))->where('stand_meter.is_confirmation', true)->where('stand_meter.tanggal', request('tanggal'))->get() as $detail) {
                    $penjualan->detail_penjualan()->create($this->openPenjualanDetailData([$detail->noozlie_id, $harga->harga_id, $detail->stand_meter_id, $detail->stand_meter_akhir - $detail->stand_meter_awal, ($detail->stand_meter_akhir - $detail->stand_meter_awal) * $harga->harga_jual]));
                    $total += ($detail->stand_meter_akhir - $detail->stand_meter_awal) * $harga->harga_jual;
                }

                $penjualan->update([
                    'total_penjualan' => $total
                ]);
            }

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
        $produk = Produk::where('is_aktif', true)->get();
        $dispenser = Dispenser::where('is_aktif', true)->get();
        $jamKerja = JamKerja::get();
        return view('setoran/open_penjualan/edit', compact('penjualan', 'spbu', 'produk', 'dispenser', 'jamKerja'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PenjualanRequest $penjualanRequest, Penjualan $penjualan)
    {
        $penjualan->update($this->openPenjualanData());

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

    public function openPenjualanData()
    {
        return [
            'status_setoran_id' => StatusSetoran::where('nama_status', 'Open Penjualan')->first()->status_setoran_id,
            'spbu_id' => request('spbu_id'),
            'produk_id' => request('produk_id'),
            'dispenser_id' => request('dispenser_id'),
            'jam_kerja_id' => request('jam_kerja_id'),
            'tanggal' => request('tanggal'),
            'total_penjualan' => str_replace(',', '', request('total_penjualan')),
            'total_uang_kotak' => str_replace(',', '', request('total_uang_kotak')),
            'is_accepted_admin' => false,
            'is_accepted_top' => false,
        ];
    }

    public function openPenjualanDetailData($data)
    {
        return [
            'nozzle_id' => $data['0'],
            'harga_id' => $data['1'],
            'stand_meter_id' => $data['2'],
            'liter' => $data['3'],
            'total_detail_jual' => $data['4'],
        ];
    }
}
