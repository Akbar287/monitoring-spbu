<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenjualanRequest;
use App\Models\JamKerja;
use App\Models\Nozzle;
use App\Models\Penjualan;
use App\Models\Spbu;
use App\Models\StatusSetoran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
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

        if ($request->ajax() && ! $request->inertia()) {
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
                    $actionBtn = array_column($row->detail_penjualan()->select('nozzle.nama_nozzle')->join('stand_meter', 'stand_meter.stand_meter_id', 'detail_penjualan.stand_meter_id')->join('nozzle', 'nozzle.nozzle_id', 'stand_meter.nozzle_id')->orderBy('nozzle.nama_nozzle', 'asc')->get()->toArray(), 'nama_nozzle');
                    return count($actionBtn) == 0 ? '-' : (count($actionBtn) > 1 ? implode(', ', $actionBtn) : $actionBtn['0']);
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('setoran.open.show', $row->penjualan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Open Penjualan',
            'breadcrumbs' => [['label' => 'Setoran', 'url' => route('menu.setoran')]],
            'createUrl' => route('setoran.open.create'),
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Jam Kerja', 'data' => 'jam_kerja'],
                    ['label' => 'Stand Meter', 'data' => 'total_stand_meter'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::select('spbu_id')->addSelect('nama_spbu')->addSelect('nomor_spbu')->orderBy('nama_spbu', 'asc')->get();
        $jamKerja = JamKerja::orderBy('nama_jam_kerja', 'asc')->get();
        $nozzle = Nozzle::orderBy('nama_nozzle', 'asc')->get();

        return Inertia::render('Setoran/OpenCreate', [
            'title' => 'Tambah Open Penjualan',
            'breadcrumbs' => [['label' => 'Setoran', 'url' => route('menu.setoran')], ['label' => 'Open Penjualan', 'url' => route('setoran.open')]],
            'backUrl' => route('setoran.open'),
            'action' => route('setoran.open.store'),
            'spbuOptions' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu . ' (' . $s->nomor_spbu . ')'])->values()->all(),
            'jamKerjaOptions' => $jamKerja->map(fn ($s) => ['value' => $s->jam_kerja_id, 'label' => $s->nama_jam_kerja])->values()->all(),
            'nozzleOptions' => $nozzle->map(fn ($s) => ['value' => $s->nozzle_id, 'label' => $s->nama_nozzle . ' (' . optional($s->produk()->first())->nama_produk . ')'])->values()->all(),
            'api' => ['standMeter' => url('/api/setoran/open-penjualan/create')],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PenjualanRequest $penjualanRequest)
    {
        $spbu = Spbu::where('spbu_id', request('spbu_id'))->first();
        $ss = StatusSetoran::where('nama_status', 'Open Penjualan')->first()->status_setoran_id;

        if (!is_null($spbu)) {
            $penjualan = $spbu->penjualan()->where('status_setoran_id', $ss)->where('jam_kerja_id', request('jam_kerja_id'))->where('tanggal', request('tanggal'))->first();
            if (is_null($penjualan)) {
                $penjualan = $spbu->penjualan()->create([
                    'status_setoran_id' => $ss,
                    'spbu_id' => request('spbu_id'),
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'tanggal' => request('tanggal'),
                    'total_penjualan' => 0,
                    'total_uang_kotak' => 0,
                    'is_accepted_admin' => 0,
                    'is_accepted_top' => 0,
                ]);
            }

            $total = 0;
            foreach ($penjualanRequest->nozzle_id as $smi) {
                $nozzle = Nozzle::where('nozzle_id', $smi)->first();
                if (!is_null($nozzle)) {
                    $sm = $nozzle->stand_meter()->where('tanggal', request('tanggal'))->where('jam_kerja_id', request('jam_kerja_id'))->first();
                    if (!is_null($sm)) {
                        $harga = $sm->nozzle()->first()->produk()->first()->harga()->where('is_default', '1')->first();

                        $totalDetailJual = ($sm->stand_meter_akhir - $sm->stand_meter_awal) * $harga->harga_jual;
                        $total += $totalDetailJual;

                        if ($penjualan->detail_penjualan()->where('stand_meter_id', $sm->stand_meter_id)->where('harga_id', $harga->harga_id)->where('liter', $sm->stand_meter_akhir - $sm->stand_meter_awal)->where('total_detail_jual', $totalDetailJual)->count() == 0) {
                            $penjualan->detail_penjualan()->create([
                                'stand_meter_id' => $sm->stand_meter_id,
                                'harga_id' => $harga->harga_id,
                                'liter' => $sm->stand_meter_akhir - $sm->stand_meter_awal,
                                'total_detail_jual' => $totalDetailJual,
                                'is_confirmed' => 0
                            ]);
                            $total += ($sm->stand_meter_akhir - $sm->stand_meter_awal) * $harga->harga_jual;
                        }
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
        $level = optional(optional(optional(auth()->user()->member()->first())->jabatan()->first())->level()->first())->nama_level;

        $pj = $penjualan->pj_operasional_setoran()
            ->distinct('pj_operasional_setoran.jabatan_id')
            ->join('jabatan', 'pj_operasional_setoran.jabatan_id', 'jabatan.jabatan_id')
            ->orderBy('nama_jabatan', 'asc')
            ->get()
            ->map(fn ($p) => [
                'jabatan' => optional($p->jabatan()->first())->nama_jabatan,
                'nama' => optional(optional(optional($p->member()->first())->ktp())->first())->nama,
            ])->values()->all();

        $standMeters = $penjualan->detail_penjualan()->get()->map(function ($dp) {
            $sm = $dp->stand_meter()->first();
            $nozzle = $sm ? $sm->nozzle()->first() : null;

            return [
                'nozzle' => $nozzle ? $nozzle->nama_nozzle . ' (' . optional($nozzle->produk()->first())->nama_produk . ')' : '-',
                'awal' => $sm ? number_format($sm->stand_meter_awal, 6, '.', ',') : '-',
                'akhir' => $sm ? number_format($sm->stand_meter_akhir, 6, '.', ',') : '-',
            ];
        })->values()->all();

        return Inertia::render('Setoran/OpenShow', [
            'title' => 'Open Penjualan',
            'breadcrumbs' => [['label' => 'Setoran', 'url' => route('menu.setoran')], ['label' => 'Open Penjualan', 'url' => route('setoran.open')]],
            'backUrl' => route('setoran.open'),
            'info' => [
                'spbu' => optional($penjualan->spbu()->first())->nama_spbu . ' (' . optional($penjualan->spbu()->first())->nomor_spbu . ')',
                'tanggal' => $penjualan->tanggal,
                'jam_kerja' => optional($penjualan->jam_kerja()->first())->nama_jam_kerja,
            ],
            'pj' => $pj,
            'standMeters' => $standMeters,
            'isPJ' => $level === 'Penanggung Jawab',
            'actions' => [
                'pj' => route('setoran.open.pj', $penjualan->penjualan_id),
                'detail' => route('setoran.open.detail', $penjualan->penjualan_id),
                'konfirmasi' => route('setoran.open.update.status_to', $penjualan->penjualan_id),
                'delete' => route('setoran.open.delete', $penjualan->penjualan_id),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function update_status_to(Penjualan $penjualan)
    {

        $penjualan->update(['status_setoran_id' => StatusSetoran::where('nama_status', 'Penitipan Setoran')->first()->status_setoran_id]);

        return redirect('/setoran/penitipan-setoran/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penjualan sudah dipindahkan ke Penitipan Setoran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        exit;
    }
    public function update_status_from(Penjualan $penjualan)
    {

        $penjualan->update(['status_setoran_id' => StatusSetoran::where('nama_status', 'Open Penjualan')->first()->status_setoran_id]);

        return redirect('/setoran/open-penjualan/' . $penjualan->penjualan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penjualan sudah dipindahkan kembali ke Open Penjualan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        exit;
    }
    public function destroy(Penjualan $penjualan)
    {
        $penjualan->delete();

        return redirect('/setoran/open-penjualan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penjualan sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function api(Request $request)
    {
        $request->validate([
            'spbu_id' => ['required'],
            'tanggal' => ['required'],
            'jam_kerja_id' => ['required'],
            'nozzle_id' => ['required'],
        ]);

        $nozzle = Nozzle::where('nozzle_id', $request->nozzle_id)->first();

        if (!is_null($nozzle)) {
            $sm = $nozzle->stand_meter()->where('tanggal', request('tanggal'))->where('jam_kerja_id', request('jam_kerja_id'))->where('is_confirmation', 1)->first();

            if (!is_null($sm)) {
                return response()->json([
                    'data' => [
                        'stand_meter_awal' => number_format($sm->stand_meter_awal, 6, ".", ","),
                        'stand_meter_akhir' => number_format($sm->stand_meter_akhir, 6, ".", ","),
                    ],
                    'message' => 'Stand Meter has been catched',
                    'status' => 'success'
                ], 200);
                exit;
            } else {
                return response()->json([
                    'data' => [],
                    'message' => 'Stand Meter not found',
                    'status' => 'error'
                ], 200);
                exit;
            }
        } else {
            return response()->json([
                'data' => [],
                'message' => 'Nozzle not found',
                'status' => 'error'
            ], 200);
            exit;
        }
    }
}
