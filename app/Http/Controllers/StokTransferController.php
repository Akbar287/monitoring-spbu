<?php

namespace App\Http\Controllers;

use App\Models\Dombak;
use App\Models\DombakTransfer;
use App\Models\JamKerja;
use App\Models\JenisLosses;
use App\Models\TypeDokumenStok;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class StokTransferController extends Controller
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
            $data = DombakTransfer::where('is_confirmation', 0)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('created_at', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->jam_kerja()->first()->nama_jam_kerja;
                })
                ->addColumn('nama_produk', function ($row) {
                    return $row->dokumen_stok()->first()->stok_inventory()->first()->produk()->first()->nama_produk;
                })
                ->addColumn('dombak_sebelum', function ($row) {
                    $d1 = $row->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Send')->first()->type_dokumen_stok_id)->first();

                    return $d1->dombak()->first()->nama_dombak;
                })
                ->addColumn('dombak_setelah', function ($row) {
                    $d2 = $row->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Receive')->first()->type_dokumen_stok_id)->first();

                    return $d2->dombak()->first()->nama_dombak;
                })
                ->addColumn('stok_berpindah', function ($row) {
                    $d1 = $row->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Send')->first()->type_dokumen_stok_id)->first();
                    $d2 = $row->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Receive')->first()->type_dokumen_stok_id)->first();

                    $p = ($d1->stok_akhir - $d1->stok_awal) - ($d2->stok_awal - $d2->stok_akhir);
                    return number_format($d1->stok_awal - $d1->stok_akhir, 6, ',', '.') . ' (' . number_format($p > 0 ? $p * -1 : $p, 6, ".", ',') . ')';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('stok-transfer.show', $row->dombak_transfer_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('stok/stok-transfer/index', compact('waktuMulai', 'waktuSelesai'));
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
            $data = DombakTransfer::where('is_confirmation', 1)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('jam_kerja', function ($row) {
                    return $row->jam_kerja()->first()->nama_jam_kerja;
                })
                ->addColumn('nama_produk', function ($row) {
                    return $row->dokumen_stok()->first()->stok_inventory()->first()->produk()->first()->nama_produk;
                })
                ->addColumn('dombak_sebelum', function ($row) {
                    $d1 = $row->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Send')->first()->type_dokumen_stok_id)->first();

                    return $d1->dombak()->first()->nama_dombak;
                })
                ->addColumn('dombak_setelah', function ($row) {
                    $d2 = $row->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Receive')->first()->type_dokumen_stok_id)->first();

                    return $d2->dombak()->first()->nama_dombak;
                })
                ->addColumn('stok_berpindah', function ($row) {
                    $d1 = $row->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Send')->first()->type_dokumen_stok_id)->first();
                    $d2 = $row->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Receive')->first()->type_dokumen_stok_id)->first();

                    $p = ($d1->stok_akhir - $d1->stok_awal) - ($d2->stok_awal - $d2->stok_akhir);
                    return number_format($d1->stok_awal - $d1->stok_akhir, 6, ',', '.') . ' (' . number_format($p > 0 ? $p * -1 : $p, 6, ".", ',') . ')';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('stok-transfer.show', $row->dombak_transfer_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('stok/stok-transfer/riwayat', compact('waktuMulai', 'waktuSelesai'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jamKerja = JamKerja::get();
        $dombak = Dombak::get();
        return view('stok/stok-transfer/create', compact('jamKerja', 'dombak'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => ['required'],
            'waktu' => ['required'],
            'jam_kerja_id' => ['required'],
            'dombak_id' => ['required'],
            'tinggi_dombak' => ['required'],
            'tinggi_dombak_setelahnya' => ['required'],
            'dombak_kedua_id' => ['required'],
            'tinggi_dombak_kedua' => ['required'],
            'tinggi_dombak_kedua_setelah' => ['required'],
        ]);

        $dombakPertama = Dombak::where('dombak_id', $request->dombak_id)->first();
        $dombakKedua = Dombak::where('dombak_id', $request->dombak_kedua_id)->first();

        if ($dombakPertama->stok_inventory_dombak()->first()->stok_inventory_id == $dombakKedua->stok_inventory_dombak()->first()->stok_inventory_id) {
            // Instansiasi
            $typeDokumenStokSend = TypeDokumenStok::where('type_movement', 'Good Transfer Send')->first();
            $typeDokumenStokReceive = TypeDokumenStok::where('type_movement', 'Good Transfer Receive')->first();
            $typeDokumenStokTaking = TypeDokumenStok::where('type_movement', 'Stok Taking Good Transfer')->first();
            $typeDokumenStokTakingPenyesuaian = TypeDokumenStok::where('type_movement', 'Stok Taking Good Transfer Penyesuaian')->first();

            $stokInventory = $dombakPertama->stok_inventory_dombak()->first()->stok_inventory()->first();
            $produk = $stokInventory->produk()->first();

            // Dombak Transfer
            $dt = $produk->dombak_transfer()->where('jam_kerja_id', request('jam_kerja_id'))->where('tanggal', request('tanggal'))->where('is_confirmation', 0)->first();
            if (is_null($dt)) {
                $dt = $produk->dombak_transfer()->create([
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'tanggal' => request('tanggal'),
                    'is_confirmation' => 0,
                    'waktu' => request('waktu')
                ]);
            }

            // Dombak Pertama
            $dombakPertamaLiterPertama = $dombakPertama->konversi()->where('tinggi', str_replace(',', '', request('tinggi_dombak')))->first();
            $dombakPertamaLiterKedua = $dombakPertama->konversi()->where('tinggi', str_replace(',', '', request('tinggi_dombak_setelahnya')))->first();

            // Stok Taking agar selisih stok terakhir sama dengan stok yang diinput sebelumnya
            $selisihDombakPertama = $dombakPertama->stok_inventory_dombak()->first()->stok - $dombakPertamaLiterPertama->volume;
            $jenisSelisihDombakPertama = ($dombakPertamaLiterKedua->volume - $dombakPertamaLiterPertama->volume) > 0; // positif maka tambah, negatif maka kurang
            $stokSend = $typeDokumenStokTaking->dokumen_stok()
                ->where('stok_inventory_id', $stokInventory->stok_inventory_id)->where('member_id', Auth::user()->member_id)->where('jam_kerja_id', request('jam_kerja_id'))->where('dombak_id', request('dombak_id'))->where('tanggal', request('tanggal'))->where('stok_awal', $dombakPertama->stok_inventory_dombak()->first()->stok ?? 0)->where('stok_akhir', $dombakPertamaLiterPertama->volume ?? 0)->where('is_confirmation', 0)->first();

            if (is_null($stokSend)) {
                $stokSend = $typeDokumenStokTaking->dokumen_stok()->create([
                    'stok_inventory_id' => $stokInventory->stok_inventory_id,
                    'member_id' => Auth::user()->member_id,
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'dombak_id' => request('dombak_id'),
                    'tanggal' => request('tanggal'),
                    'stok_awal' => $dombakPertama->stok_inventory_dombak()->first()->stok ?? 0,
                    'stok_akhir' => $dombakPertamaLiterPertama->volume ?? 0,
                    'is_confirmation' => 0,
                    'waktu' => request('waktu')
                ]);

                if (is_null(DB::table('dombak_transfer_dokumen_stok')->where('dombak_transfer_id', $dt->dombak_transfer_id)->where('dokumen_stok_id', $stokSend->dokumen_stok_id)->first())) {
                    DB::table('dombak_transfer_dokumen_stok')->insert([
                        'dombak_transfer_id' => $dt->dombak_transfer_id,
                        'dokumen_stok_id' => $stokSend->dokumen_stok_id
                    ]);
                }
            }

            // Insert Pengurangan Stok Dombak Pertama
            $send = $typeDokumenStokSend->dokumen_stok()->where('stok_inventory_id', $stokInventory->stok_inventory_id)->where('member_id', Auth::user()->member_id)->where('jam_kerja_id', request('jam_kerja_id'))->where('dombak_id', request('dombak_id'))->where('tanggal', request('tanggal'))->where('stok_awal', $dombakPertamaLiterPertama->volume ?? 0)->where('stok_akhir', $dombakPertamaLiterKedua->volume ?? 0)->where('is_confirmation', 0)->first();
            if (is_null($send)) {
                $send = $typeDokumenStokSend->dokumen_stok()->create([
                    'stok_inventory_id' => $stokInventory->stok_inventory_id,
                    'member_id' => Auth::user()->member_id,
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'dombak_id' => request('dombak_id'),
                    'tanggal' => request('tanggal'),
                    'stok_awal' => $dombakPertamaLiterPertama->volume ?? 0,
                    'stok_akhir' => $dombakPertamaLiterKedua->volume ?? 0,
                    'is_confirmation' => 0,
                    'waktu' => request('waktu')
                ]);

                if (is_null(DB::table('dombak_transfer_dokumen_stok')->where('dombak_transfer_id', $dt->dombak_transfer_id)->where('dokumen_stok_id', $send->dokumen_stok_id)->first())) {
                    DB::table('dombak_transfer_dokumen_stok')->insert([
                        'dombak_transfer_id' => $dt->dombak_transfer_id,
                        'dokumen_stok_id' => $send->dokumen_stok_id
                    ]);
                }
            }
            unset($typeDokumenStokSend);

            // Penyesuaian Pasca Transfer
            $stokTakingPenyesuaian = $typeDokumenStokTakingPenyesuaian->dokumen_stok()->where('stok_inventory_id', $stokInventory->stok_inventory_id)->where('member_id', Auth::user()->member_id)->where('jam_kerja_id', request('jam_kerja_id'))->where('dombak_id', request('dombak_id'))->where('tanggal', request('tanggal'))->where('stok_awal', $dombakPertamaLiterKedua->volume ?? 0)->where('stok_akhir', $dombakPertamaLiterKedua->volume + $jenisSelisihDombakPertama > 0 ? $dombakPertamaLiterKedua->volume + $selisihDombakPertama : $dombakPertamaLiterKedua->volume - $selisihDombakPertama)->where('is_confirmation', 0)->first();
            if (is_null($stokTakingPenyesuaian)) {
                $stokTakingPenyesuaian = $typeDokumenStokTakingPenyesuaian->dokumen_stok()->create([
                    'stok_inventory_id' => $stokInventory->stok_inventory_id,
                    'member_id' => Auth::user()->member_id,
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'dombak_id' => request('dombak_id'),
                    'tanggal' => request('tanggal'),
                    'stok_awal' => $dombakPertamaLiterKedua->volume ?? 0,
                    'stok_akhir' => $jenisSelisihDombakPertama > 0 ? $dombakPertamaLiterKedua->volume + $selisihDombakPertama : $dombakPertamaLiterKedua->volume - $selisihDombakPertama,
                    'is_confirmation' => 0,
                    'waktu' => request('waktu')
                ]);

                if (is_null(DB::table('dombak_transfer_dokumen_stok')->where('dombak_transfer_id', $dt->dombak_transfer_id)->where('dokumen_stok_id', $stokTakingPenyesuaian->dokumen_stok_id)->first())) {
                    DB::table('dombak_transfer_dokumen_stok')->insert([
                        'dombak_transfer_id' => $dt->dombak_transfer_id,
                        'dokumen_stok_id' => $stokTakingPenyesuaian->dokumen_stok_id
                    ]);
                }
            }
            // End Dombak Satu

            // Dombak Kedua
            $dombakKeduaLiterPertama = $dombakKedua->konversi()->where('tinggi', str_replace(',', '', request('tinggi_dombak_kedua')))->first();
            $dombakKeduaLiterKedua = $dombakKedua->konversi()->where('tinggi', str_replace(',', '', request('tinggi_dombak_kedua_setelah')))->first();

            $selisihDombakKedua = $dombakKedua->stok_inventory_dombak()->first()->stok - $dombakKeduaLiterPertama->volume;
            $jenisSelisihDombakKedua = ($dombakKeduaLiterKedua->volume - $dombakKeduaLiterPertama->volume) > 0; // positif maka tambah, negatif maka kurang
            // Tambahkan Stok Taking agar selisih stok terakhir sama dengan stok yang diinput sebelumnya
            $stokReceive = $typeDokumenStokTaking->dokumen_stok()->where('stok_inventory_id', $stokInventory->stok_inventory_id)->where('member_id', Auth::user()->member_id)->where('jam_kerja_id', request('jam_kerja_id'))->where('dombak_id', request('dombak_kedua_id'))->where('tanggal', request('tanggal'))->where('stok_awal', $dombakKedua->stok_inventory_dombak()->first()->stok ?? 0)->where('stok_akhir', $dombakKeduaLiterPertama->volume ?? 0)->where('is_confirmation', 0)->first();
            if (is_null($stokReceive)) {
                $stokReceive = $typeDokumenStokTaking->dokumen_stok()->create([
                    'stok_inventory_id' => $stokInventory->stok_inventory_id,
                    'member_id' => Auth::user()->member_id,
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'dombak_id' => request('dombak_kedua_id'),
                    'tanggal' => request('tanggal'),
                    'stok_awal' => $dombakKedua->stok_inventory_dombak()->first()->stok ?? 0,
                    'stok_akhir' => $dombakKeduaLiterPertama->volume ?? 0,
                    'is_confirmation' => 0,
                    'waktu' => request('waktu')
                ]);

                if (is_null(DB::table('dombak_transfer_dokumen_stok')->where('dombak_transfer_id', $dt->dombak_transfer_id)->where('dokumen_stok_id', $stokReceive->dokumen_stok_id)->first())) {
                    DB::table('dombak_transfer_dokumen_stok')->insert([
                        'dombak_transfer_id' => $dt->dombak_transfer_id,
                        'dokumen_stok_id' => $stokReceive->dokumen_stok_id
                    ]);
                }
            }

            // Insert Penambahan Stok Dombak Kedua
            $receive = $typeDokumenStokReceive->dokumen_stok()->where('stok_inventory_id', $stokInventory->stok_inventory_id)->where('member_id', Auth::user()->member_id)->where('jam_kerja_id', request('jam_kerja_id'))->where('dombak_id', request('dombak_kedua_id'))->where('tanggal', request('tanggal'))->where('stok_awal', $dombakKeduaLiterPertama->volume ?? 0)->where('stok_akhir', $dombakKeduaLiterKedua->volume ?? 0)->where('is_confirmation', 0)->first();
            if (is_null($receive)) {
                $receive = $typeDokumenStokReceive->dokumen_stok()->create([
                    'stok_inventory_id' => $stokInventory->stok_inventory_id,
                    'member_id' => Auth::user()->member_id,
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'dombak_id' => request('dombak_kedua_id'),
                    'tanggal' => request('tanggal'),
                    'stok_awal' => $dombakKeduaLiterPertama->volume ?? 0,
                    'stok_akhir' => $dombakKeduaLiterKedua->volume ?? 0,
                    'is_confirmation' => 0,
                    'waktu' => request('waktu')
                ]);

                if (is_null(DB::table('dombak_transfer_dokumen_stok')->where('dombak_transfer_id', $dt->dombak_transfer_id)->where('dokumen_stok_id', $receive->dokumen_stok_id)->first())) {
                    DB::table('dombak_transfer_dokumen_stok')->insert([
                        'dombak_transfer_id' => $dt->dombak_transfer_id,
                        'dokumen_stok_id' => $receive->dokumen_stok_id
                    ]);
                }
            }

            // Penyesuaian Pasca Transfer
            $stokTakingPenyesuaian = $typeDokumenStokTakingPenyesuaian->dokumen_stok()->where('stok_inventory_id', $stokInventory->stok_inventory_id)->where('member_id', Auth::user()->member_id)->where('jam_kerja_id', request('jam_kerja_id'))->where('dombak_id', request('dombak_kedua_id'))->where('tanggal', request('tanggal'))->where('stok_awal', $dombakKeduaLiterKedua->volume ?? 0)->where('stok_akhir', $dombakKeduaLiterKedua->volume + $jenisSelisihDombakPertama > 0 ? $dombakKeduaLiterKedua->volume + $selisihDombakKedua : $dombakKeduaLiterKedua->volume - $selisihDombakKedua)->where('is_confirmation', 0)->first();
            if (is_null($stokTakingPenyesuaian)) {
                $stokTakingPenyesuaian = $typeDokumenStokTakingPenyesuaian->dokumen_stok()->create([
                    'stok_inventory_id' => $stokInventory->stok_inventory_id,
                    'member_id' => Auth::user()->member_id,
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'dombak_id' => request('dombak_id'),
                    'tanggal' => request('tanggal'),
                    'stok_awal' => $dombakKeduaLiterKedua->volume ?? 0,
                    'stok_akhir' => $jenisSelisihDombakKedua > 0 ? $dombakKeduaLiterKedua->volume + $selisihDombakKedua : $dombakKeduaLiterKedua->volume - $selisihDombakKedua,
                    'is_confirmation' => 0,
                    'waktu' => request('waktu')
                ]);

                if (is_null(DB::table('dombak_transfer_dokumen_stok')->where('dombak_transfer_id', $dt->dombak_transfer_id)->where('dokumen_stok_id', $stokTakingPenyesuaian->dokumen_stok_id)->first())) {
                    DB::table('dombak_transfer_dokumen_stok')->insert([
                        'dombak_transfer_id' => $dt->dombak_transfer_id,
                        'dokumen_stok_id' => $stokTakingPenyesuaian->dokumen_stok_id
                    ]);
                }
            }

            // End Dombak dua

            // Cek Loss Rumus: selisih stok setelah dan sebelum di dombak pertama dikurangi selisih stok sebelum dan setelah dombak kedua (Dombak2Stlh - Dombak2Sblm) - (Dombak1Stlh - Dombak1Sblm);
            $loss = ($dombakKeduaLiterKedua->volume - $dombakKeduaLiterPertama->volume) - ($dombakPertamaLiterKedua->volume - $dombakPertamaLiterPertama->volume);
            $jenisLoss = JenisLosses::where('jenis_loss', 'Good Transfer')->first();

            if (is_null($receive->losses()->where('jenis_losses_id', $jenisLoss->jenis_losses_id)->where('tanggal', request('tanggal'))->where('simbol', ($loss < 0 ? 'kurang' : 'lebih'))->where('jumlah',  $loss < 0 ? $loss * -1 : $loss)->first())) {
                $receive->losses()->create([
                    'jenis_losses_id' => $jenisLoss->jenis_losses_id,
                    'tanggal' => request('tanggal'),
                    'simbol' => ($loss < 0 ? 'kurang' : 'lebih'),
                    'jumlah' =>  $loss < 0 ? $loss * -1 : $loss
                ]);
            }

            // Free Memory
            unset($selisihDombakPertama);
            unset($jenisSelisihDombakPertama);
            unset($selisihDombakKedua);
            unset($jenisSelisihDombakKedua);
            unset($dombakPertamaLiterPertama);
            unset($dombakPertamaLiterKedua);
            unset($typeDokumenStokReceive);
            unset($dombakKeduaLiterPertama);
            unset($dombakKeduaLiterKedua);

            return redirect('/stok/stok-transfer/' . $dt->dokumen_transfer_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Stok Transfer sudah dicatat.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        } else {
            // Beda Produk
            return redirect('/stok/stok-transfer/create')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Produk tidak boleh berbeda.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }

    public function confirmation_stok_transfer(DombakTransfer $dombakTransfer)
    {
        // Inisiasi Dombak
        $dombakPertama = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Send')->first()->type_dokumen_stok_id)->first();
        $dombakPertamaStokTaking = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking Good Transfer')->first()->type_dokumen_stok_id)->where('dombak_id', $dombakPertama->dombak_id)->first();
        $dombakPertamaStokTakingPenyesuaian = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking Good Transfer Penyesuaian')->first()->type_dokumen_stok_id)->where('dombak_id', $dombakPertama->dombak_id)->first();
        $dombakKedua = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Receive')->first()->type_dokumen_stok_id)->first();
        $dombakKeduaStokTaking = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking Good Transfer')->first()->type_dokumen_stok_id)->where('dombak_id', $dombakKedua->dombak_id)->first();
        $dombakKeduaStokTakingPenyesuaian = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking Good Transfer Penyesuaian')->first()->type_dokumen_stok_id)->where('dombak_id', $dombakKedua->dombak_id)->first();

        // Update Stok Before Send
        $dombakPertamaStokTaking->stok_inventory()->update([
            'stok' => $dombakPertamaStokTaking->stok_inventory()->first()->stok + ($dombakPertamaStokTaking->stok_akhir - $dombakPertamaStokTaking->stok_awal)
        ]);
        $dombakPertamaStokTaking->dombak()->first()->stok_inventory_dombak()->update(['stok' => $dombakPertamaStokTaking->dombak()->first()->stok_inventory_dombak()->first()->stok + ($dombakPertamaStokTaking->stok_akhir - $dombakPertamaStokTaking->stok_awal)]);

        // Update Stok Dombak Pertama
        $dombakPertama->stok_inventory()->update([
            'stok' => $dombakPertama->stok_inventory()->first()->stok + ($dombakPertama->stok_akhir - $dombakPertama->stok_awal)
        ]);
        $dombakPertama->dombak()->first()->stok_inventory_dombak()->update(['stok' => $dombakPertama->dombak()->first()->stok_inventory_dombak()->first()->stok + ($dombakPertama->stok_akhir - $dombakPertama->stok_awal)]);

        // Penyesuaian Dombak Pertama
        $dombakPertamaStokTakingPenyesuaian->stok_inventory()->update([
            'stok' => $dombakPertamaStokTakingPenyesuaian->stok_akhir + ($dombakPertamaStokTakingPenyesuaian->stok_akhir - $dombakPertamaStokTakingPenyesuaian->stok_awal)
        ]);
        $dombakPertamaStokTakingPenyesuaian->dombak()->first()->stok_inventory_dombak()->update(['stok' => $dombakPertamaStokTakingPenyesuaian->dombak()->first()->stok_inventory_dombak()->first()->stok + ($dombakPertamaStokTakingPenyesuaian->stok_akhir - $dombakPertamaStokTakingPenyesuaian->stok_awal)]);

        // Update Stok Before Receive
        $dombakKeduaStokTaking->stok_inventory()->update([
            'stok' => $dombakKeduaStokTaking->stok_inventory()->first()->stok + ($dombakKeduaStokTaking->stok_akhir - $dombakKeduaStokTaking->stok_awal)
        ]);
        $dombakKeduaStokTaking->dombak()->first()->stok_inventory_dombak()->update(['stok' => $dombakKeduaStokTaking->dombak()->first()->stok_inventory_dombak()->first()->stok + ($dombakKeduaStokTaking->stok_akhir - $dombakKeduaStokTaking->stok_awal)]);

        // Update Stok Dombak Kedua
        $dombakKedua->stok_inventory()->update([
            'stok' => $dombakKedua->stok_inventory()->first()->stok + ($dombakKedua->stok_akhir - $dombakKedua->stok_awal)
        ]);
        $dombakKedua->dombak()->first()->stok_inventory_dombak()->update(['stok' => $dombakKedua->dombak()->first()->stok_inventory_dombak()->first()->stok + ($dombakKedua->stok_akhir - $dombakKedua->stok_awal)]);

        // Penyesuaian Dombak Kedua
        $dombakKeduaStokTakingPenyesuaian->stok_inventory()->update([
            'stok' => $dombakKeduaStokTakingPenyesuaian->stok_akhir + ($dombakKeduaStokTakingPenyesuaian->stok_akhir - $dombakKeduaStokTakingPenyesuaian->stok_awal)
        ]);
        $dombakKeduaStokTakingPenyesuaian->dombak()->first()->stok_inventory_dombak()->update(['stok' => $dombakKeduaStokTakingPenyesuaian->dombak()->first()->stok_inventory_dombak()->first()->stok + ($dombakKeduaStokTakingPenyesuaian->stok_akhir - $dombakKeduaStokTakingPenyesuaian->stok_awal)]);

        // Update Status Konfirmasi
        $dombakPertama->update(['is_confirmation' => 1]);
        $dombakKedua->update(['is_confirmation' => 1]);
        $dombakPertamaStokTaking->update(['is_confirmation' => 1]);
        $dombakKeduaStokTaking->update(['is_confirmation' => 1]);
        $dombakTransfer->update(['is_confirmation' => 1]);
        $dombakPertamaStokTakingPenyesuaian->update(['is_confirmation' => 1]);
        $dombakKeduaStokTakingPenyesuaian->update(['is_confirmation' => 1]);

        return redirect('/stok/stok-transfer/' . $dombakTransfer->dokumen_transfer_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Stok Transfer sudah dikonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(DombakTransfer $dombakTransfer)
    {
        $d1 = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Send')->first()->type_dokumen_stok_id)->first();
        $d2 = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Receive')->first()->type_dokumen_stok_id)->first();

        $p = ($d1->stok_akhir - $d1->stok_awal) - ($d2->stok_awal - $d2->stok_akhir);
        return view('stok/stok-transfer/show', compact('dombakTransfer', 'd1', 'd2', 'p'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DombakTransfer $dombakTransfer)
    {
        if ($dombakTransfer->is_confirmation) {
            return redirect('/stok/stok-transfer/' . $dombakTransfer->dokumen_transfer_id)->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Gagal!</strong> Data Stok Transfer tidak dapat dihapus. Karena sudah di konfirmasi dan sudah mengubah stok utama<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        } else {
            foreach ($dombakTransfer->dokumen_stok()->get() as $ds) {
                $ds->delete();
            }

            $dombakTransfer->delete();

            return redirect('/stok/stok-transfer/' . $dombakTransfer->dokumen_transfer_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Stok Transfer sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }
}
