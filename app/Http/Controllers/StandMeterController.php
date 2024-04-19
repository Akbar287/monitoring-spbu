<?php

namespace App\Http\Controllers;

use App\Http\Requests\StandMeterRequest;
use App\Models\Dombak;
use App\Models\JamKerja;
use App\Models\Nozzle;
use App\Models\Produk;
use App\Models\StandMeter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class StandMeterController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $produk = Produk::select()->orderBy('nama_produk', 'asc')->get();
        $produkSelected = $request->produk_id ??  'all';
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
            $data = $produkSelected === 'all' ?  StandMeter::where('stand_meter_akhir', null)->orWhere('is_confirmation', false)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->join('nozzle', 'nozzle.nozzle_id', 'stand_meter.nozzle_id')->get() : StandMeter::where('stand_meter_akhir', null)->orWhere('is_confirmation', false)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->where('nozzle.produk_id', $produkSelected)->join('nozzle', 'nozzle.nozzle_id', 'stand_meter.nozzle_id')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nozzle', function ($row) {
                    return $row->nozzle()->first()->nama_nozzle;
                })
                ->addColumn('nama_produk', function ($row) {
                    return $row->nozzle()->first()->produk()->first()->nama_produk;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->jam_kerja()->first()->nama_jam_kerja;
                })
                ->addColumn('stand_meter_awal', function ($row) {
                    return number_format($row->stand_meter_awal, 2, ".", ",");
                })
                ->addColumn('stand_meter_akhir', function ($row) {
                    return number_format($row->stand_meter_akhir, 2, ".", ",");
                })
                ->addColumn('selisih', function ($row) {
                    return number_format($row->stand_meter_akhir - $row->stand_meter_awal, 2, ".", ",");
                })
                ->addColumn('status', function ($row) {
                    return is_null($row->stand_meter_akhir) ? '<div class="badge badge-warning">Berjalan</div>' : ($row->is_confirmation ? '<div class="badge badge-success">Terkonfirmasi</div>' : '<div class="badge badge-danger">Belum Dikonfirmasi</div>');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('totalisator.show', $row->stand_meter_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('stok/totalisator/index', compact('waktuMulai', 'waktuSelesai', 'produk', 'produkSelected'));
    }

    public function riwayat(Request $request)
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
            $data = StandMeter::where('stand_meter_akhir', '!=', null)->where('is_confirmation', true)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('tanggal', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nozzle', function ($row) {
                    return $row->nozzle()->first()->nama_nozzle;
                })
                ->addColumn('nama_produk', function ($row) {
                    return $row->nozzle()->first()->produk()->first()->nama_produk;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->jam_kerja()->first()->nama_jam_kerja;
                })
                ->addColumn('selisih', function ($row) {
                    return number_format($row->stand_meter_akhir - $row->stand_meter_awal, 2, ".", ",");
                })
                ->addColumn('status', function ($row) {
                    return $row->is_confirmation ? '<div class="badge badge-success">Terkonfirmasi</div>' : '<div class="badge badge-danger">Belum Dikonfirmasi</div>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('totalisator.riwayat_show', $row->stand_meter_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('stok/totalisator/riwayat', compact('waktuMulai', 'waktuSelesai'));
    }

    public function riwayat_show(StandMeter $standMeter)
    {
        return view('stok/totalisator/riwayat_show', compact('standMeter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $dombak = Dombak::where('is_aktif', true)->get();
        $nozzle = Nozzle::select('nozzle.nozzle_id')->addSelect('nozzle.nama_nozzle')->addSelect('produk.nama_produk')
            ->join('dispenser', 'dispenser.dispenser_id', 'nozzle.dispenser_id')
            ->join('payung', 'payung.payung_id', 'dispenser.payung_id')
            ->join('produk', 'produk.produk_id', 'nozzle.produk_id')
            ->whereIn('payung.payung_id', $dombak->first()->payung()->pluck('payung.payung_id')->toArray())
            ->get();
        $jamKerja = JamKerja::get();
        return view('stok/totalisator/create', compact('dombak', 'nozzle', 'jamKerja'));
    }

    public function create_api(Request $request)
    {

        if (is_null(request('dombak_id')) && !is_null(request('nozzle_id'))) {
            $request->validate([
                'nozzle_id' => ['required']
            ]);

            $nozzle = Nozzle::where('nozzle_id', $request->nozzle_id)->first();

            if (is_null($nozzle)) {
                return response()->json([
                    'data' => [],
                    'message' => "dombak not found",
                    'status' => 'failed'
                ], 404);
                exit;
            } else {
                $standMeter = $nozzle->stand_meter()->select('stand_meter.stand_meter_akhir')->addSelect('jam_kerja.nama_jam_kerja')->addSelect('stand_meter.tanggal')->addSelect('jam_kerja.urutan')->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'stand_meter.jam_kerja_id')->orderBy('stand_meter.tanggal', 'desc')->orderBy('jam_kerja.urutan', 'desc')->first();

                $jamKerjaCount = JamKerja::count();
                $tgl = is_null($standMeter) ? null : Carbon::createFromFormat('Y-m-d', $standMeter->tanggal);
                $shift = is_null($standMeter) ? null : ($standMeter->urutan == $jamKerjaCount ? 1 : $standMeter->urutan + 1);
                $tanggal = is_null($tgl) ? null : ($standMeter->urutan == $jamKerjaCount ? $tgl->addDay()->format('Y-m-d') : $tgl->format('Y-m-d'));

                return response()->json([
                    'data' => [
                        'tanggal' => $tanggal ??  date('Y-m-d'),
                        'jam_kerja' => $shift,
                        'stand_meter_akhir' => is_null($standMeter) ? 0 : number_format($standMeter->stand_meter_akhir, 2, ".", ",")
                    ],
                    'message' => "data has been served",
                    'status' => 'success'
                ], 200);
                exit;
            }
        } else if (!is_null(request('dombak_id')) && is_null(request('nozzle_id'))) {
            $request->validate([
                'dombak_id' => ['required']
            ]);

            $dombak = Dombak::where('dombak_id', $request->dombak_id)->first();

            if (is_null($dombak)) {
                return response()->json([
                    'data' => [],
                    'message' => "dombak not found",
                    'status' => 'failed'
                ], 404);
                exit;
            } else {
                $nozzle = Nozzle::select('nozzle.nozzle_id')->addSelect('nozzle.nama_nozzle')->addSelect('produk.nama_produk')
                    ->join('dispenser', 'dispenser.dispenser_id', 'nozzle.dispenser_id')
                    ->join('payung', 'payung.payung_id', 'dispenser.payung_id')
                    ->join('produk', 'produk.produk_id', 'nozzle.produk_id')
                    ->whereIn('payung.payung_id', $dombak->payung()->pluck('payung.payung_id')->toArray())
                    ->get();

                return response()->json([
                    'data' => $nozzle,
                    'message' => "data has been served",
                    'status' => 'success'
                ], 200);
                exit;
            }
        } else {
            return response()->json([
                'data' => [],
                'message' => "no request",
                'status' => 'failed'
            ], 404);
            exit;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nozzle_id' => ['required'],
            'dombak_id' => ['required'],
        ]);
        // Ambil nozzle berdasarkan nozzle_id
        $nozzle = Nozzle::where('nozzle_id', $request->nozzle_id)->first();

        // Hitung jumlah jam kerja
        $jamKerjaCount = JamKerja::count();

        // Ambil data stand_meter terbaru
        $latestStandMeter = $nozzle->stand_meter()
            ->select('stand_meter.stand_meter_akhir', 'jam_kerja.nama_jam_kerja', 'stand_meter.tanggal', 'jam_kerja.urutan')
            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'stand_meter.jam_kerja_id')
            ->orderBy('stand_meter.tanggal', 'desc')
            ->orderBy('jam_kerja.urutan', 'desc')
            ->first();

        // Tentukan tanggal dan jam kerja berdasarkan stand_meter terbaru
        $jamKerjaCount = JamKerja::count();
        $tgl = is_null($latestStandMeter) ? null : Carbon::createFromFormat('Y-m-d', $latestStandMeter->tanggal);
        $nextJamKerja = is_null($latestStandMeter) ? 1 : ($latestStandMeter->urutan == $jamKerjaCount ? 1 : $latestStandMeter->urutan + 1);
        $jamKerja = JamKerja::where('urutan', $nextJamKerja)->first();
        $tanggal = is_null($tgl) ? null : ($latestStandMeter->urutan == $jamKerjaCount ? $tgl->addDay()->format('Y-m-d') : $tgl->format('Y-m-d'));
        $standMeter = $latestStandMeter->stand_meter_akhir ?? str_replace(',', '', request('stand_meter_awal'));

        // Masukan data ke DB
        StandMeter::create([
            'nozzle_id' => $nozzle->nozzle_id,
            'dombak_id' => $request->dombak_id,
            'jam_kerja_id' => $jamKerja->jam_kerja_id,
            'tanggal' => $tanggal,
            'stand_meter_awal' => $standMeter,
            'stand_meter_akhir' => 0,
            'is_confirmation' => false
        ]);

        // Hapus Duplikat Database
        StandMeter::where('nozzle_id', $nozzle->nozzle_id)->where('dombak_id', $request->dombak_id)->where('jam_kerja_id', $jamKerja->jam_kerja_id)->where('tanggal', $tanggal)->where('stand_meter_awal', 0)->where('stand_meter_akhir', 0)->delete();

        // Redirect ke Halaman Index
        return redirect()->route('totalisator.index')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Stand Meter sudah di tambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        exit;
    }

    /**
     * Display the specified resource.
     */
    public function show(StandMeter $standMeter)
    {
        return view('stok/totalisator/show', compact('standMeter'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StandMeter $standMeter)
    {
        $dombak = Dombak::where('is_aktif', true)->get();
        $nozzle = Nozzle::where('is_aktif', true)->get();
        $jamKerja = JamKerja::get();
        return view('stok/totalisator/edit', compact('dombak', 'nozzle', 'jamKerja', 'standMeter'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StandMeter $standMeter)
    {
        $request->validate(['stand_meter_akhir' => ['required']]);
        $standMeter->update([
            'stand_meter_awal' => str_replace(',', '', $request->stand_meter_awal),
            'stand_meter_akhir' => str_replace(',', '', $request->stand_meter_akhir)
        ]);

        return redirect('/stok/totalisator/' . $standMeter->stand_meter_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Stand Meter sudah di ubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function final(StandMeter $standMeter)
    {
        $dombak = Dombak::where('is_aktif', true)->get();
        $nozzle = Nozzle::where('is_aktif', true)->get();
        $jamKerja = JamKerja::get();
        return view('stok/totalisator/final', compact('dombak', 'nozzle', 'jamKerja', 'standMeter'));
    }

    public function final_update(Request $request, StandMeter $standMeter)
    {
        $request->validate(['stand_meter_akhir' => ['required']]);

        $standMeter->update([
            'stand_meter_akhir' => str_replace(',', '', $request->stand_meter_akhir)
        ]);

        return redirect('/stok/totalisator/' . $standMeter->stand_meter_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Stand Meter sudah di ubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function confirmation(StandMeter $standMeter)
    {
        $standMeter->update([
            'is_confirmation' => true
        ]);

        $selisih = $standMeter->stand_meter_akhir - $standMeter->stand_meter_awal;

        $standMeter->dombak()->first()->stok_inventory_dombak()->first()->update([
            'stok' => $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok - $selisih
        ]);

        $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok_inventory()->first()->update([
            'stok' => $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok_inventory()->first()->stok - $selisih
        ]);

        return redirect('/stok/totalisator/riwayat/' . $standMeter->stand_meter_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Stand Meter sudah di konfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StandMeter $standMeter)
    {
        $standMeter->delete();

        return redirect('/stok/totalisator')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Stand Meter sudah di hapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
