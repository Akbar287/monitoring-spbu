<?php

namespace App\Http\Controllers;

use App\Models\Dombak;
use App\Models\JamKerja;
use App\Models\Nozzle;
use App\Models\Produk;
use App\Models\StandMeter;
use App\Models\TypeDokumenStok;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $data = null;
            $data = $produkSelected === 'all' ?  StandMeter::where('stand_meter_akhir', null)->orWhere('is_confirmation', 0)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->join('nozzle', 'nozzle.nozzle_id', 'stand_meter.nozzle_id')->get() : StandMeter::where('stand_meter_akhir', null)->orWhere('is_confirmation', 0)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->where('nozzle.produk_id', $produkSelected)->join('nozzle', 'nozzle.nozzle_id', 'stand_meter.nozzle_id')->get();

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
                    return number_format($row->stand_meter_awal, 6, ".", ",");
                })
                ->addColumn('stand_meter_akhir', function ($row) {
                    return number_format($row->stand_meter_akhir, 6, ".", ",");
                })
                ->addColumn('selisih', function ($row) {
                    return number_format($row->stand_meter_akhir - $row->stand_meter_awal, 6, ".", ",");
                })
                ->addColumn('status', function ($row) {
                    return is_null($row->stand_meter_akhir) ? '<div class="badge badge-warning">Berjalan</div>' : ($row->is_confirmation == 1 ? '<div class="badge badge-success">Terkonfirmasi</div>' : '<div class="badge badge-danger">Belum Dikonfirmasi</div>');
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
            $data = $produkSelected === 'all' ? StandMeter::where('stand_meter_akhir', '!=', null)->where('is_confirmation', 1)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('tanggal', 'desc')->get() : StandMeter::where('stand_meter_akhir', '!=', null)->where('is_confirmation', 1)->where('nozzle.produk_id', $produkSelected)->join('nozzle', 'nozzle.nozzle_id', 'stand_meter.nozzle_id')->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('tanggal', 'desc')->get();
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
                    return number_format($row->stand_meter_awal, 6, ".", ",");
                })
                ->addColumn('stand_meter_akhir', function ($row) {
                    return number_format($row->stand_meter_akhir, 6, ".", ",");
                })
                ->addColumn('selisih', function ($row) {
                    return number_format($row->stand_meter_akhir - $row->stand_meter_awal, 6, ".", ",");
                })
                ->addColumn('status', function ($row) {
                    return $row->is_confirmation == 1 ? '<div class="badge badge-success">Terkonfirmasi</div>' : '<div class="badge badge-danger">Belum Dikonfirmasi</div>';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('totalisator.riwayat_show', $row->stand_meter_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('stok/totalisator/riwayat', compact('waktuMulai', 'waktuSelesai', 'produk', 'produkSelected'));
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
        $dombak = Dombak::where('is_aktif', 1)->get();
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
            $jam_kerja = JamKerja::select('nama_jam_kerja', 'jam_kerja_id')->orderBy('nama_jam_kerja', 'asc')->get();

            if (is_null($nozzle)) {
                return response()->json([
                    'data' => [],
                    'message' => "dombak not found",
                    'status' => 'failed'
                ], 404);
                exit;
            } else {
                $standMeter = $nozzle->stand_meter()->select('stand_meter.stand_meter_id')->addSelect('stand_meter.dombak_id')->addSelect('stand_meter.jam_kerja_id')->addSelect('stand_meter.tanggal')->addSelect('stand_meter.stand_meter_akhir')->addSelect('jam_kerja.nama_jam_kerja')->addSelect('stand_meter.tanggal')->addSelect('jam_kerja.urutan')->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'stand_meter.jam_kerja_id')->orderBy('stand_meter.tanggal', 'desc')->orderBy('jam_kerja.urutan', 'desc')->first();

                $jamKerjaCount = JamKerja::count();
                $dokStok = null;
                if (!is_null($standMeter)) {
                    $dokStok = TypeDokumenStok::where('type_movement', 'Good Receive Penerimaan')->first()->dokumen_stok()->where('tanggal', $standMeter->tanggal)->where('jam_kerja_id', $standMeter->jam_kerja_id)->where('dombak_id', $standMeter->dombak_id)->where('is_confirmation', 1)->first();
                }

                $tgl = is_null($standMeter) ? null : Carbon::createFromFormat('Y-m-d', $standMeter->tanggal);
                $shift = is_null($standMeter) ? null : ($standMeter->urutan == $jamKerjaCount ? 1 : $standMeter->urutan + 1);
                $tanggal = is_null($tgl) ? null : ($standMeter->urutan == $jamKerjaCount ? $tgl->addDay()->format('Y-m-d') : $tgl->format('Y-m-d'));

                return response()->json([
                    'data' => [
                        'tanggal' => $tanggal ??  date('Y-m-d'),
                        'jam_kerja' => $shift,
                        'stand_meter_akhir' => is_null($standMeter) ? 0 : number_format($standMeter->stand_meter_akhir, 6, ".", ","),
                        'receive' => false, //!is_null($dokStok),
                        'jam_kerja_id' => $jam_kerja
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

        // dd($request->all());

        // Ambil nozzle berdasarkan nozzle_id
        $nozzle = Nozzle::where('nozzle_id', $request->nozzle_id)->first();

        // Hitung jumlah jam kerja
        $jamKerjaCount = JamKerja::count();

        // Ambil data stand_meter terbaru
        $latestStandMeter = $nozzle->stand_meter()
            ->select('stand_meter.stand_meter_id')->addSelect('stand_meter.dombak_id')->addSelect('stand_meter.jam_kerja_id')->addSelect('stand_meter.tanggal')->addSelect('stand_meter.stand_meter_akhir')->addSelect('jam_kerja.nama_jam_kerja')->addSelect('stand_meter.tanggal')->addSelect('jam_kerja.urutan')
            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'stand_meter.jam_kerja_id')
            ->orderBy('stand_meter.tanggal', 'desc')
            ->orderBy('jam_kerja.urutan', 'desc')
            ->first();

        $standMeter = $nozzle->stand_meter()
            ->select('stand_meter.stand_meter_id')->addSelect('stand_meter.dombak_id')->addSelect('stand_meter.jam_kerja_id')->addSelect('stand_meter.tanggal')->addSelect('stand_meter.stand_meter_akhir')->addSelect('jam_kerja.nama_jam_kerja')->addSelect('stand_meter.tanggal')->addSelect('jam_kerja.urutan')
            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'stand_meter.jam_kerja_id')
            ->orderBy('stand_meter.tanggal', 'desc')
            ->orderBy('jam_kerja.urutan', 'desc')
            ->first();

        $dokStok = null;
        if (!is_null($latestStandMeter)) {
            $dokStok = TypeDokumenStok::where('type_movement', 'Good Receive Penerimaan')->first()->dokumen_stok()->where('tanggal', $latestStandMeter->tanggal)->where('jam_kerja_id', $latestStandMeter->jam_kerja_id)->where('dombak_id', $latestStandMeter->dombak_id)->where('is_confirmation', 1)->first();
        }

        if (is_null($dokStok)) {
            // Tentukan tanggal dan jam kerja berdasarkan stand_meter terbaru
            $tgl = is_null($latestStandMeter) ? null : Carbon::createFromFormat('Y-m-d', $latestStandMeter->tanggal);
            $nextJamKerja = is_null($latestStandMeter) ? 1 : ($latestStandMeter->urutan == $jamKerjaCount ? 1 : $latestStandMeter->urutan + 1);
            $jamKerja = JamKerja::where('urutan', $nextJamKerja)->first();
            $tanggal = is_null($tgl) ? null : ($latestStandMeter->urutan == $jamKerjaCount ? $tgl->addDay()->format('Y-m-d') : $tgl->format('Y-m-d'));
        } else {
            $jk = JamKerja::select('jam_kerja_id')->where('nama_jam_kerja', $request->jam_kerja_id)->first();
            $jamKerja = JamKerja::where('jam_kerja_id', $jk->jam_kerja_id)->first();
            $tanggal = $request->tanggal;
        }

        // Masukan data ke DB
        $urutan = 1;
        $cek = true;
        do {
            $cek = StandMeter::where('nozzle_id', $nozzle->nozzle_id)->where('dombak_id', $request->dombak_id)->where('jam_kerja_id', $jamKerja->jam_kerja_id)->where('tanggal', $tanggal)->where('urutan', 1)->first();

            if (is_null($cek)) {
                $cek = false;
            } else {
                $urutan++;
            }
        } while ($cek);

        if (is_null($latestStandMeter->stand_meter_akhir)) {
            $standMeter =  str_replace(',', '', request('stand_meter_awal'));
        } else {
            if ($latestStandMeter->stand_meter_akhir == '0') {
                $standMeter =  str_replace(',', '', request('stand_meter_awal'));
            } else {
                $standMeter =  $latestStandMeter->stand_meter_akhir;
            }
        }

        if (is_null(StandMeter::where('nozzle_id', $nozzle->nozzle_id)->where('dombak_id', $request->dombak_id)->where('tanggal', is_null($tanggal) ? date('Y-m-d') : $tanggal)->where('stand_meter_awal', $standMeter)->where('stand_meter_akhir', 0)->where('urutan', $urutan)->where('is_confirmation', 0)->first())) {
            StandMeter::create([
                'nozzle_id' => $nozzle->nozzle_id,
                'dombak_id' => $request->dombak_id,
                'jam_kerja_id' => $jamKerja->jam_kerja_id,
                'tanggal' => is_null($tanggal) ? date('Y-m-d') : $tanggal,
                'stand_meter_awal' => $standMeter,
                'stand_meter_akhir' => 0,
                'urutan' => $urutan,
                'is_confirmation' => 0
            ]);
        }

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
        $dombak = Dombak::where('is_aktif', 1)->get();
        $nozzle = Nozzle::where('is_aktif', 1)->get();
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
        $dombak = Dombak::where('is_aktif', 1)->get();
        $nozzle = Nozzle::where('is_aktif', 1)->get();
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
            'is_confirmation' => 1
        ]);

        if (DB::table('stand_meter_dokumen_stok')->where('stand_meter_id', $standMeter->stand_meter_id)->count() == 0) {

            // instansiasi untuk receive
            $type_dokumen_stock_penjualan = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Good Issue Sales')->first();
            $typeReveive = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Stok Taking Good Receive Penerimaan')->first();
            $typeGoodReveive = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Good Receive Penerimaan')->first();
            $receive = $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->where('tanggal', Carbon::createFromFormat('Y-m-d', $standMeter->tanggal)->toDateTime())->where('type_dokumen_stok_id', $typeReveive->type_dokumen_stok_id)->where('jam_kerja_id', $standMeter->jam_kerja_id)->get();
            $goodReceive = $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->where('tanggal', Carbon::createFromFormat('Y-m-d', $standMeter->tanggal)->toDateTime())->where('type_dokumen_stok_id', $typeGoodReveive->type_dokumen_stok_id)->where('jam_kerja_id', $standMeter->jam_kerja_id)->get();
            $stokTakingFirstInSameShift = $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->where('tanggal', Carbon::createFromFormat('Y-m-d', $standMeter->tanggal)->toDateTime())->where('type_dokumen_stok_id', $type_dokumen_stock_penjualan->type_dokumen_stok_id)->where('jam_kerja_id', $standMeter->jam_kerja_id)->get();
            // End Instansiasi

            $stokInventory = $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok_inventory()->first()->stok;
            $stok = $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok;
            $selisih = $standMeter->stand_meter_akhir - $standMeter->stand_meter_awal;

            if (count($goodReceive) > 0 && count($stokTakingFirstInSameShift) == 0) {
                $stokAwal = 0;
                if (count($receive) > 0) {
                    foreach ($receive as $r) {
                        $stokAwal = ($stokAwal == 0) ? $r->stok_awal : (($stokAwal > $r->stok_awal) ? $r->stok_awal : $stokAwal);
                    }
                }

                $selisihGReceive = 0;
                foreach ($goodReceive as $r) {
                    if (!is_null($r->losses()->first())) {
                        if ($r->losses()->first()->simbol == 'lebih') {
                            $selisihGReceive += ($r->stok_akhir - $r->stok_awal) - $r->losses()->first()->jumlah;
                        } else {
                            $selisihGReceive += ($r->stok_akhir - $r->stok_awal) + $r->losses()->first()->jumlah;
                        }
                    } else {
                        $selisihGReceive += ($r->stok_akhir - $r->stok_awal);
                    }
                }
                $selisih = ($stok - ($stokAwal + $selisihGReceive - $selisih));
            }

            $stok_dombak = $standMeter->dombak()->first()->stok_inventory_dombak()->first();

            $standMeter->dombak()->first()->stok_inventory_dombak()->first()->update([
                'stok' => $stok - $selisih
            ]);

            $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok_inventory()->first()->update([
                'stok' => $stokInventory - $selisih
            ]);

            $jamKerja = JamKerja::where('jam_kerja_id', $standMeter->jam_kerja_id)->first();

            if ($type_dokumen_stock_penjualan->dokumen_stok()->where('stok_inventory_id', $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id)->where('jam_kerja_id', $standMeter->jam_kerja_id)->where('member_id', Auth::user()->member_id)->where('dombak_id', $standMeter->dombak_id)->where('tanggal', date($standMeter->tanggal))->where('stok_awal', !is_null($stok_dombak) ? $stok_dombak->stok : 0)->where('stok_akhir', (!is_null($stok_dombak) ? $stok_dombak->stok : 0) - $selisih)->where('is_confirmation', 1)->where('waktu', is_null($jamKerja) ? '00:00:00' : $jamKerja->jam_pulang_out)->count() == 0) {
                $dokumenStokPenjualan = $type_dokumen_stock_penjualan->dokumen_stok()->create([
                    'stok_inventory_id' => $standMeter->dombak()->first()->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id,
                    'jam_kerja_id' => $standMeter->jam_kerja_id,
                    'member_id' => Auth::user()->member_id,
                    'dombak_id' => $standMeter->dombak_id,
                    'tanggal' => date($standMeter->tanggal),
                    'stok_awal' => !is_null($stok_dombak) ? $stok_dombak->stok : 0,
                    'stok_akhir' => (!is_null($stok_dombak) ? $stok_dombak->stok : 0) - $selisih,
                    'is_confirmation' => 1,
                    'waktu' => is_null($jamKerja) ? '00:00:00' : $jamKerja->jam_pulang_out
                ]);

                DB::table('stand_meter_dokumen_stok')->insert([
                    'stand_meter_id' => $standMeter->stand_meter_id,
                    'dokumen_stok_id' => $dokumenStokPenjualan->dokumen_stok_id
                ]);

                $dombak = Dombak::where('dombak_id', $standMeter->dombak_id)->first();
                if (!is_null($dombak)) {
                    $konversi = $dombak->konversi()->where('volume', ($stokInventory - $selisih))->first();

                    if (!is_null($konversi)) {
                        DB::table('konversi_dokumen_stok')->insert([
                            'konversi_id' => $konversi->konversi_id,
                            'dokumen_stok_id' => $dokumenStokPenjualan->dokumen_stok_id
                        ]);
                    }
                }
            }
        }

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
