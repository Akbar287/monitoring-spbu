<?php

namespace App\Http\Controllers;

use App\Models\DokumenStok;
use App\Models\Dombak;
use App\Models\JamKerja;
use App\Models\JenisLosses;
use App\Models\Produk;
use App\Models\StokInventory;
use App\Models\TypeDokumenStok;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class StokTakingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $produk = Produk::select('produk_id', 'nama_produk')->get();
        $produkSelected = $request->produkSelected ?? 'all';
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
            $data = $produkSelected == 'all' ? DokumenStok::where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking')->first()->type_dokumen_stok_id)->where('is_confirmation', false)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('created_at', 'desc')->get() : DokumenStok::join('stok_inventory', 'stok_inventory.stok_inventory_id', 'dokumen_stok.stok_inventory_id')->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking')->first()->type_dokumen_stok_id)->where('stok_inventory.produk_id', $request->produkSelected)->where('is_confirmation', false)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('tanggal', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->jam_kerja()->first()->nama_jam_kerja;
                })
                ->addColumn('nama_produk', function ($row) {
                    return $row->stok_inventory()->first()->produk()->first()->nama_produk;
                })
                ->addColumn('type_dokumen', function ($row) {
                    return $row->type_dokumen_stok()->first()->type_movement;
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('stok-taking.show', $row->dokumen_stok_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('stok/stok-taking/index', compact('waktuMulai', 'waktuSelesai', 'produk', 'produkSelected'));
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
            $data = DokumenStok::where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking')->first()->type_dokumen_stok_id)->where('is_confirmation', true)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('created_at', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->jam_kerja()->first()->nama_jam_kerja;
                })
                ->addColumn('nama_produk', function ($row) {
                    return $row->stok_inventory()->first()->produk()->first()->nama_produk;
                })
                ->addColumn('type_dokumen', function ($row) {
                    return $row->type_dokumen_stok()->first()->type_movement;
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('stok-taking.riwayat.show', $row->dokumen_stok_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('stok/stok-taking/riwayat', compact('waktuMulai', 'waktuSelesai'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jamKerja = JamKerja::select('nama_jam_kerja')->addSelect('jam_kerja_id')->addSelect('batas_akhir_in')->addSelect('batas_awal_in')->get();
        $dombak = Dombak::where('is_aktif', true)->get();
        $typeDokumen = TypeDokumenStok::where('type_movement', 'Stok Taking')->get();
        return view('stok/stok-taking/create', compact('jamKerja', 'dombak', 'typeDokumen'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dombak_id' => ['required'],
            'type_dokumen_id' => ['required'],
            'jam_kerja_id' => ['required'],
            'tanggal' => ['required'],
            'tinggi_dombak' => ['required'],
        ]);

        $type_dokumen_stock = TypeDokumenStok::where('type_dokumen_stok_id', $request->type_dokumen_id)->first();
        $stok_inventory = Dombak::where('dombak_id', $request->dombak_id)->first()->stok_inventory_dombak()->first()->stok_inventory()->first();
        $tinggiDombak = Dombak::where('dombak_id', $request->dombak_id)->first()->konversi()->where('tinggi', $request->tinggi_dombak)->first();
        $dokumenStok = null;

        if ($type_dokumen_stock->type_movement == 'Stok Taking') {
            $awal = Dombak::where('dombak_id', $request->dombak_id)->first()->stok_inventory_dombak()->first()->stok;
            $akhir = $tinggiDombak->volume;

            $dokumenStok = $type_dokumen_stock->dokumen_stok()->where('stok_inventory_id', $stok_inventory->stok_inventory_id)->where('jam_kerja_id', $request->jam_kerja_id)->where('member_id', Auth::user()->member_id)->where('dombak_id', $request->dombak_id)->where('tanggal', $request->tanggal)->where('stok_awal', $awal)->where('stok_akhir', $akhir)->where('is_confirmation', false)->first();
            if (is_null($dokumenStok)) {
                $dokumenStok = $type_dokumen_stock->dokumen_stok()->create([
                    'stok_inventory_id' => $stok_inventory->stok_inventory_id,
                    'jam_kerja_id' => $request->jam_kerja_id,
                    'member_id' => Auth::user()->member_id,
                    'dombak_id' => $request->dombak_id,
                    'tanggal' => $request->tanggal,
                    'stok_awal' => $awal,
                    'stok_akhir' => $akhir,
                    'is_confirmation' => false
                ]);
            }

            if (is_null(DB::table('konversi_dokumen_stok')->where(
                'konversi_id',
                $tinggiDombak->konversi_id
            )->where('dokumen_stok_id', $dokumenStok->dokumen_stok_id)->first())) {
                DB::table('konversi_dokumen_stok')->insert([
                    'konversi_id' => $tinggiDombak->konversi_id,
                    'dokumen_stok_id' => $dokumenStok->dokumen_stok_id
                ]);
            }

            $type_losses = JenisLosses::where('jenis_loss', 'Stok Taking')->first();
            if ($akhir - $awal < 0) {
                // Loss
                if (is_null(
                    $dokumenStok->losses()->where('jenis_losses_id', $type_losses->jenis_losses_id)->where('tanggal', $request->tanggal)->where('simbol', 'kurang')->where('jumlah', ($akhir - $awal) * -1)->first()
                )) {
                    $dokumenStok->losses()->create([
                        'jenis_losses_id' => $type_losses->jenis_losses_id,
                        'tanggal' => $request->tanggal,
                        'simbol' => 'kurang',
                        'jumlah' => ($akhir - $awal) * -1
                    ]);
                }
            } else {
                // Profit
                if (is_null(
                    $dokumenStok->losses()->where('jenis_losses_id', $type_losses->jenis_losses_id)->where('tanggal', $request->tanggal)->where('simbol', 'lebih')->where('jumlah', $akhir - $awal)->first()
                )) {
                    $dokumenStok->losses()->create([
                        'jenis_losses_id' => $type_losses->jenis_losses_id,
                        'tanggal' => $request->tanggal,
                        'simbol' => 'lebih',
                        'jumlah' => $akhir - $awal
                    ]);
                }
            }
        }

        return redirect('/stok/stok-taking/' . $dokumenStok->dokumen_stok_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Stok Taking sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function confirmation_stok_taking(DokumenStok $stok)
    {
        $temp = 0;
        foreach ($stok->stok_inventory()->first()->stok_inventory_dombak()->whereNot('dombak_id', $stok->dombak_id)->get() as $sid) {
            $temp += $sid->stok;
        }

        $stok->dombak()->first()->stok_inventory_dombak()->first()->update([
            'stok' => $stok->stok_akhir
        ]);

        $stok->stok_inventory()->first()->update([
            'stok' => $temp + $stok->stok_akhir
        ]);

        $stok->update([
            'is_confirmation' => true
        ]);

        return redirect('/stok/stok-taking/' . $stok->dokumen_stok_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Stok Taking sudah dikonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(DokumenStok $stok)
    {
        return view('stok/stok-taking/show', compact('stok'));
    }

    public function riwayat_show(DokumenStok $stok)
    {
        return view('stok/stok-taking/riwayat_show', compact('stok'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DokumenStok $stok)
    {
        $typeDokumen = TypeDokumenStok::select('type_movement')->addSelect('type_dokumen_stok_id')->get();
        $dokumenStokExist = DokumenStok::orderBy('created_at', 'desc')->first();
        $dombak = Dombak::where('is_aktif', true)->get();
        $jamKerja = JamKerja::select('nama_jam_kerja')->addSelect('jam_kerja_id')->addSelect('batas_akhir_in')->addSelect('batas_awal_in')->get();
        $stokInventory = StokInventory::get();
        return view('stok/stok-taking/edit', compact('typeDokumen', 'dokumenStokExist', 'jamKerja', 'stokInventory', 'stok', 'dombak'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DokumenStok $stok)
    {
        $request->validate([
            'stok_inventory_id' => ['required'],
            'jam_kerja_id' => ['required'],
            'type_dokumen_stok_id' => ['required'],
            'tanggal' => ['required'],
            'stok_awal' => ['required'],
            'stok_akhir' => ['required'],
            'loss' => ['required'],
        ]);

        return redirect('/stok/stok-taking/' . $stok->dokumen_stok_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Stok Taking sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DokumenStok $stok)
    {
        $stok->delete();

        return redirect('/stok/stok-taking')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Stok Taking sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
