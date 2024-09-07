<?php

namespace App\Http\Controllers;

use App\Models\PajakPembelianLib;
use App\Models\Produk;
use App\Models\RencanaPembelian;
use App\Models\Spbu;
use App\Models\StatusPurchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class RencanaPembelianController extends Controller
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
            $data = RencanaPembelian::where('is_confirmation', 0)->where('is_denied', 0)->whereBetween('created_at', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_confirmation', function ($row) {
                    $temp = '';
                    if ($row->is_confirmation == 1) {
                        $temp = '<span class="badge badge-success">Terkonfirmasi</span>';
                    } else {
                        if ($row->is_denied == 'true') {
                            $temp = '<span class="badge badge-danger">Ditolak</span>';
                        } else {
                            $temp = '<span class="badge badge-primary">Belum Terkonfirmasi</span>';
                        }
                    }
                    return $temp;
                })
                ->addColumn('created_at', function ($row) {
                    $actionBtn = $row->created_at->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('produk_id', function ($row) {
                    $actionBtn = $row->detail_rencana_pembelian()->count() . ' Produk';
                    return $actionBtn;
                })
                ->addColumn('member_id', function ($row) {
                    $actionBtn = $row->member()->first()->ktp()->first()->nama;
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('perencanaan.show', $row->rencana_pembelian_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation'])
                ->make(true);
        }
        return view('procurement/perencanaan/index', compact('waktuMulai', 'waktuSelesai'));
    }

    public function index_ditolak(Request $request)
    {
        if ($request->ajax()) {
            $data = StatusPurchase::where("nama_status", "Rencana")->first()->rencana_pembelian()->where('is_confirmation', 1)->where('is_denied', 1)->orderBy('created_at', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_confirmation', function ($row) {
                    $temp = '';
                    if ($row->is_denied == 1) {
                        $temp = '<span class="badge badge-danger">Ditolak</span>';
                    } else {
                        $temp = '<span class="badge badge-primary">Disetujui</span>';
                    }

                    return $temp;
                })
                ->addColumn('created_at', function ($row) {
                    $actionBtn = $row->created_at->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('produk_id', function ($row) {
                    $actionBtn = $row->detail_rencana_pembelian()->count() . ' Produk';
                    return $actionBtn;
                })
                ->addColumn('member_id', function ($row) {
                    $actionBtn = $row->member()->first()->ktp()->first()->nama;
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('perencanaan.ditolak.show', $row->rencana_pembelian_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation'])
                ->make(true);
        }
        return view('procurement/perencanaan/ditolak');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $spbu = Spbu::all();
        $kode = $this->generateKodePembelian();
        $produk = Produk::all();
        return view('procurement/perencanaan/create', compact('spbu', 'produk', 'kode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "spbu_id" => ["required"],
            "kode_pembelian" => ["required"]
        ]);

        $spbu = Spbu::find($request->spbu_id);
        $statusPurchase = StatusPurchase::where("nama_status", "Rencana")->first();
        $cek = true;
        do {
            $rencana = RencanaPembelian::where("status_purchase_id", $statusPurchase->status_purchase_id)->where("spbu_id", $spbu->spbu_id)->where("grand_total", 0)->where('kode_pembelian', $request->kode_pembelian)->where("pembelian_id", null)->where("tanggal_pembelian", null)->where("keterangan", request('keterangan'))->where("keterangan_denied", null)->where("is_confirmation", 0)->where("is_denied", 0)->first();

            if (is_null($rencana)) {
                $rencana = RencanaPembelian::firstOrCreate([
                    "status_purchase_id" => $statusPurchase->status_purchase_id,
                    "spbu_id" => $spbu->spbu_id,
                    'member_id' => Auth::user()->member_id,
                    "grand_total" => 0,
                    'kode_pembelian' => $request->kode_pembelian,
                    "pembelian_id" => null,
                    "tanggal_pembelian" => null,
                    "keterangan" => request('keterangan'),
                    "keterangan_denied" => null,
                    "is_confirmation" => 0,
                    "is_denied" => 0,
                ]);
            } else {
                $cek = false;
            }
        } while ($cek);

        return redirect()->route('perencanaan.show', $rencana->rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Rencana Pembelian sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(RencanaPembelian $rencana)
    {
        return view('procurement/perencanaan/show', compact('rencana'));
    }

    public function show_ditolak(RencanaPembelian $rencana)
    {
        return view('procurement/perencanaan/ditolak_detail', compact('rencana'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaPembelian $rencana)
    {
        $spbu = Spbu::all();
        $produk = Produk::all();
        return view('procurement/perencanaan/edit', compact('rencana', 'spbu', 'produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RencanaPembelian $rencana)
    {
        $rencana->update($this->rencanaData(null, null, $rencana));

        return redirect('/procurement/perencanaan/' . $rencana->rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Rencana Pembelian sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RencanaPembelian $rencana)
    {
        $rencana->delete();

        return redirect('/procurement/perencanaan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Rencana Pembelian sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function rencanaData($spbu = null, $statusPurchase = null, $rencana = null)
    {
        if (is_null($rencana)) {
            return [
                "status_purchase_id" => $statusPurchase->status_purchase_id,
                "spbu_id" => $spbu->spbu_id,
                "grand_total" => 0,
                'kode_pembelian' => $this->generateKodePembelian(),
                "pembelian_id" => null,
                "tanggal_pembelian" => null,
                "keterangan" => request('keterangan'),
                "keterangan_denied" => null,
                "is_confirmation" => 0,
                "is_denied" => 0,
            ];
        } else {
            return [
                "keterangan" => request('keterangan'),
            ];
        }
    }

    public function generateKodePembelian()
    {
        $kode = 'A-' . date('Ymd') . '-';
        $i = 1;
        $check = true;
        do {
            if (RencanaPembelian::where('kode_pembelian', $kode . $i)->count() == 0) {
                $check = false;
                $kode .= $i;
            } else {
                $i++;
            }
        } while ($check);
        return $kode;
    }

    public function konfirmasi(RencanaPembelian $rencana)
    {
        return view('procurement/perencanaan/konfirmasi', compact('rencana'));
    }
    public function konfirmasi_update(Request $request, RencanaPembelian $rencana)
    {
        $temp = [
            "is_confirmation" => 1,
            "is_denied" => request('jenis') == "false" ? 1 : 0,
            "keterangan_denied" => request('keterangan')
        ];

        if ($request->jenis == "true") {
            $statusPurchase = StatusPurchase::where("nama_status", "Pembelian")->first();
            $temp['status_purchase_id'] = $statusPurchase->status_purchase_id;
        }
        $rencana->update($temp);

        $lib = PajakPembelianLib::where('is_aktif', 1)->first();

        $total = $rencana->grand_total + $rencana->grand_total * ($lib->ppn / 100) + $rencana->grand_total * ($lib->ppbkb / 100) + $rencana->grand_total * ($lib->pph / 100);

        if (is_null($rencana->pajak_pembelian()->where('pajak_pembelian_lib_id', $lib->pajak_pembelian_lib_i)->where('net_price', $rencana->grand_total)->where('ppn', $rencana->grand_total * ($lib->ppn / 100))->where('ppbkb', $rencana->grand_total * ($lib->ppbkb / 100))->where('pph', $rencana->grand_total * ($lib->pph / 100))->where('total_amount', $total)->first())) {
            $rencana->pajak_pembelian()->create([
                'pajak_pembelian_lib_id' => $lib->pajak_pembelian_lib_id,
                'net_price' => $rencana->grand_total,
                'ppn' => $rencana->grand_total * ($lib->ppn / 100),
                'ppbkb' => $rencana->grand_total * ($lib->ppbkb / 100),
                'pph' => $rencana->grand_total * ($lib->pph / 100),
                'total_amount' => $total
            ]);
        }

        return redirect('/procurement/perencanaan/' . $rencana->rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Rencana Pembelian sudah terkonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
