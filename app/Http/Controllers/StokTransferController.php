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
            $data = DombakTransfer::where('is_confirmation', false)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('created_at', 'desc')->get();
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
                    return number_format($d1->stok_awal - $d1->stok_akhir, 2, ',', '.') . ' (' . number_format($p > 0 ? $p * -1 : $p, 2, ".", ',') . ')';
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
            $data = DombakTransfer::where('is_confirmation', true)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->get();
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
                    return number_format($d1->stok_awal - $d1->stok_akhir, 2, ',', '.') . ' (' . number_format($p > 0 ? $p * -1 : $p, 2, ".", ',') . ')';
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
            $typeDokumenStokTaking = TypeDokumenStok::where('type_movement', 'Stok Taking')->first();

            $stokInventory = $dombakPertama->stok_inventory_dombak()->first()->stok_inventory()->first();
            $produk = $stokInventory->produk()->first();

            // Dombak Transfer
            $dt = $produk->dombak_transfer()->create([
                'jam_kerja_id' => request('jam_kerja_id'),
                'tanggal' => request('tanggal'),
                'is_confirmation' => false
            ]);

            // Dombak Pertama
            $dombakPertamaLiterPertama = $dombakPertama->konversi()->where('tinggi', str_replace(',', '', request('tinggi_dombak')))->first();
            $dombakPertamaLiterKedua = $dombakPertama->konversi()->where('tinggi', str_replace(',', '', request('tinggi_dombak_setelahnya')))->first();

            // Cek stok dombak pertama terakhir kali apakah sama dengan stok yang diinput
            if ($dombakPertama->stok_inventory_dombak()->first()->stok != $dombakPertamaLiterPertama->volume) {
                // Tambahkan Stok Taking agar selisih stok terakhir sama dengan stok yang diinput sebelumnya
                $stokSend = $typeDokumenStokTaking->dokumen_stok()->create([
                    'stok_inventory_id' => $stokInventory->stok_inventory_id,
                    'member_id' => Auth::user()->member_id,
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'dombak_id' => request('dombak_id'),
                    'tanggal' => request('tanggal'),
                    'stok_awal' => $dombakPertama->stok_inventory_dombak()->first()->stok ?? 0,
                    'stok_akhir' => $dombakPertamaLiterPertama->volume ?? 0,
                    'is_confirmation' => false
                ]);
            }

            // Insert Pengurangan Stok Dombak Pertama
            $send = $typeDokumenStokSend->dokumen_stok()->create([
                'stok_inventory_id' => $stokInventory->stok_inventory_id,
                'member_id' => Auth::user()->member_id,
                'jam_kerja_id' => request('jam_kerja_id'),
                'dombak_id' => request('dombak_id'),
                'tanggal' => request('tanggal'),
                'stok_awal' => $dombakPertamaLiterPertama->volume ?? 0,
                'stok_akhir' => $dombakPertamaLiterKedua->volume ?? 0,
                'is_confirmation' => false
            ]);
            unset($typeDokumenStokSend);

            // Dombak Kedua
            $dombakKeduaLiterPertama = $dombakKedua->konversi()->where('tinggi', str_replace(',', '', request('tinggi_dombak_kedua')))->first();
            $dombakKeduaLiterKedua = $dombakKedua->konversi()->where('tinggi', str_replace(',', '', request('tinggi_dombak_kedua_setelah')))->first();

            // Cek Stok Dombak Kedua Terakhir Apakah Sama Dengan input stok dombak Kedua Sebelumnya
            if ($dombakKedua->stok_inventory_dombak()->first()->stok != $dombakKeduaLiterPertama->volume) {
                // Tambahkan Stok Taking agar selisih stok terakhir sama dengan stok yang diinput sebelumnya
                $stokReceive = $typeDokumenStokTaking->dokumen_stok()->create([
                    'stok_inventory_id' => $stokInventory->stok_inventory_id,
                    'member_id' => Auth::user()->member_id,
                    'jam_kerja_id' => request('jam_kerja_id'),
                    'dombak_id' => request('dombak_kedua_id'),
                    'tanggal' => request('tanggal'),
                    'stok_awal' => $dombakKedua->stok_inventory_dombak()->first()->stok ?? 0,
                    'stok_akhir' => $dombakKeduaLiterPertama->volume ?? 0,
                    'is_confirmation' => false
                ]);
            }

            // Insert Penambahan Stok Dombak Kedua
            $receive = $typeDokumenStokReceive->dokumen_stok()->create([
                'stok_inventory_id' => $stokInventory->stok_inventory_id,
                'member_id' => Auth::user()->member_id,
                'jam_kerja_id' => request('jam_kerja_id'),
                'dombak_id' => request('dombak_kedua_id'),
                'tanggal' => request('tanggal'),
                'stok_awal' => $dombakKeduaLiterPertama->volume ?? 0,
                'stok_akhir' => $dombakKeduaLiterKedua->volume ?? 0,
                'is_confirmation' => false
            ]);

            // Cek Loss Rumus: selisih stok setelah dan sebelum di dombak pertama dikurangi selisih stok sebelum dan setelah dombak kedua (Dombak2Stlh - Dombak2Sblm) - (Dombak1Stlh - Dombak1Sblm);
            $loss = ($dombakKeduaLiterKedua->volume - $dombakKeduaLiterPertama->volume) - ($dombakPertamaLiterKedua->volume - $dombakPertamaLiterPertama->volume);
            $jenisLoss = JenisLosses::where('jenis_loss', 'Good Transfer')->first();

            $receive->losses()->create([
                'jenis_losses_id' => $jenisLoss->jenis_losses_id,
                'tanggal' => request('tanggal'),
                'simbol' => ($loss < 0 ? 'kurang' : 'lebih'),
                'jumlah' =>  $loss < 0 ? $loss * -1 : $loss
            ]);

            // Free Memory
            unset($dombakPertamaLiterPertama);
            unset($dombakPertamaLiterKedua);
            unset($typeDokumenStokReceive);
            unset($dombakKeduaLiterPertama);
            unset($dombakKeduaLiterKedua);

            // Relasi
            DB::table('dombak_transfer_dokumen_stok')->insert([
                'dombak_transfer_id' => $dt->dombak_transfer_id,
                'dokumen_stok_id' => $stokSend->dokumen_stok_id
            ]);
            DB::table('dombak_transfer_dokumen_stok')->insert([
                'dombak_transfer_id' => $dt->dombak_transfer_id,
                'dokumen_stok_id' => $stokReceive->dokumen_stok_id
            ]);
            DB::table('dombak_transfer_dokumen_stok')->insert([
                'dombak_transfer_id' => $dt->dombak_transfer_id,
                'dokumen_stok_id' => $send->dokumen_stok_id
            ]);
            DB::table('dombak_transfer_dokumen_stok')->insert([
                'dombak_transfer_id' => $dt->dombak_transfer_id,
                'dokumen_stok_id' => $receive->dokumen_stok_id
            ]);

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
        $dombakPertamaStokTaking = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking')->first()->type_dokumen_stok_id)->where('dombak_id', $dombakPertama->dombak_id)->first();
        $dombakKedua = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Good Transfer Receive')->first()->type_dokumen_stok_id)->first();
        $dombakKeduaStokTaking = $dombakTransfer->dokumen_stok()->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking')->first()->type_dokumen_stok_id)->where('dombak_id', $dombakKedua->dombak_id)->first();

        // Update Stok Before Send
        $dombakPertamaStokTaking->stok_inventory()->update([
            'stok' => $dombakPertamaStokTaking->stok_inventory()->first()->stok - ($dombakPertamaStokTaking->stok_akhir - $dombakPertamaStokTaking->stok_awal)
        ]);
        $dombakPertamaStokTaking->dombak()->first()->stok_inventory_dombak()->update(['stok' => ($dombakPertamaStokTaking->stok_akhir - $dombakPertamaStokTaking->stok_awal)]);

        // Update Stok Dombak Pertama
        $dombakPertama->stok_inventory()->update([
            'stok' => $dombakPertama->stok_inventory()->first()->stok - ($dombakPertama->stok_akhir - $dombakPertama->stok_awal)
        ]);
        $dombakPertama->dombak()->first()->stok_inventory_dombak()->update(['stok' => ($dombakPertama->stok_akhir - $dombakPertama->stok_awal)]);

        // Update Stok Before Receive
        $dombakKeduaStokTaking->stok_inventory()->update([
            'stok' => $dombakKeduaStokTaking->stok_inventory()->first()->stok + ($dombakKeduaStokTaking->stok_akhir - $dombakKeduaStokTaking->stok_awal)
        ]);
        $dombakKeduaStokTaking->dombak()->first()->stok_inventory_dombak()->update(['stok' => ($dombakKeduaStokTaking->stok_akhir - $dombakKeduaStokTaking->stok_awal)]);

        // Update Stok Dombak Kedua
        $dombakKedua->stok_inventory()->update([
            'stok' => $dombakKedua->stok_inventory()->first()->stok + ($dombakKedua->stok_akhir - $dombakKedua->stok_awal)
        ]);
        $dombakKedua->dombak()->first()->stok_inventory_dombak()->update(['stok' => ($dombakKedua->stok_akhir - $dombakKedua->stok_awal)]);

        // Update Status Konfirmasi
        $dombakPertama->update(['is_confirmation' => true]);
        $dombakKedua->update(['is_confirmation' => true]);
        $dombakTransfer->update(['is_confirmation' => true]);

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
