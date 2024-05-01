<?php

namespace App\Http\Controllers;

use App\Models\Dombak;
use App\Models\FileLo;
use App\Models\JamKerja;
use App\Models\JenisLosses;
use App\Models\Penerimaan;
use App\Models\TypeDokumenStok;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PenerimaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = FileLo::select('file_lo.*')->leftJoin('penerimaan', 'penerimaan.file_lo_id', 'file_lo.file_lo_id')->whereNull('penerimaan.file_lo_id')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn = $row->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('jumlah', function ($row) {
                    $actionBtn = number_format($row->jumlah, 6, ".", ",") . ' (' . $row->satuan_jumlah . ')';
                    return $actionBtn;
                })
                ->addColumn('tanggal_upload', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal_upload)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('penerimaan.show', $row->file_lo_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation'])
                ->make(true);
        }
        return view('stok/penerimaan/index');
    }

    public function verifikasi(Request $request)
    {
        if ($request->ajax()) {
            $data = Penerimaan::join('dokumen_stok', 'dokumen_stok.dokumen_stok_id', 'penerimaan.dokumen_stok_id')->where('dokumen_stok.is_confirmation', 'false')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('dombak', function ($row) {
                    $actionBtn = $row->dokumen_stok()->first()->dombak()->first()->nama_dombak;
                    return $actionBtn;
                })
                ->addColumn('no_faktur', function ($row) {
                    $actionBtn = $row->file_lo()->first()->no_faktur;
                    return $actionBtn;
                })
                ->addColumn('no_lo', function ($row) {
                    $actionBtn = $row->file_lo()->first()->no_lo;
                    return $actionBtn;
                })
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn = $row->file_lo()->first()->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('jumlah', function ($row) {
                    $actionBtn = number_format($row->file_lo()->first()->jumlah, 6, ".", ",") . ' (' . $row->file_lo()->first()->satuan_jumlah . ')';
                    return $actionBtn;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('penerimaan.verifikasi.show', $row->penerimaan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation'])
                ->make(true);
        }
        return view('stok/penerimaan/verifikasi_index');
    }

    public function riwayat(Request $request)
    {
        if ($request->ajax()) {
            $data = Penerimaan::join('dokumen_stok', 'dokumen_stok.dokumen_stok_id', 'penerimaan.dokumen_stok_id')->where('dokumen_stok.is_confirmation', 'true')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('dombak', function ($row) {
                    $actionBtn = $row->dokumen_stok()->first()->dombak()->first()->nama_dombak;
                    return $actionBtn;
                })
                ->addColumn('no_faktur', function ($row) {
                    $actionBtn = $row->file_lo()->first()->no_faktur;
                    return $actionBtn;
                })
                ->addColumn('no_lo', function ($row) {
                    $actionBtn = $row->file_lo()->first()->no_lo;
                    return $actionBtn;
                })
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn = $row->file_lo()->first()->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('jumlah', function ($row) {
                    $actionBtn = number_format($row->file_lo()->first()->jumlah, 6, ".", ",") . ' (' . $row->file_lo()->first()->satuan_jumlah . ')';
                    return $actionBtn;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('penerimaan.riwayat.show', $row->penerimaan_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation'])
                ->make(true);
        }
        return view('stok/penerimaan/riwayat_index');
    }

    /**
     * Display the specified resource.
     */
    public function verifikasi_show(Penerimaan $penerimaan)
    {
        $satuan_tinggi = $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->konversi()->first()->satuan_ukur_tinggi()->first()->nama_satuan;
        $satuan_volume = $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->konversi()->first()->satuan_ukur_volume()->first()->nama_satuan;
        return view('stok/penerimaan/verifikasi_show', compact('penerimaan', 'satuan_tinggi', 'satuan_volume'));
    }

    public function riwayat_show(Penerimaan $penerimaan)
    {
        $satuan_tinggi = $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->konversi()->first()->satuan_ukur_tinggi()->first()->nama_satuan;
        $satuan_volume = $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->konversi()->first()->satuan_ukur_volume()->first()->nama_satuan;
        return view('stok/penerimaan/riwayat_show', compact('penerimaan', 'satuan_tinggi', 'satuan_volume'));
    }

    public function show(FileLo $fileLo)
    {
        $dombak = $fileLo->produk()->first()->stok_inventory()->first()->stok_inventory_dombak()->get();
        $jamKerja = JamKerja::get();
        return view('stok/penerimaan/show', compact('fileLo', 'dombak', 'jamKerja'));
    }

    public function store(Request $request, FileLo $fileLo)
    {
        $request->validate([
            'dombak_id' => ['required'],
            'tanggal' => ['required'],
            'jam_kerja_id' => ['required'],
            'tinggi_dombak_sebelumnya' => ['required'],
            'tinggi_dombak_setelahnya' => ['required'],
        ]);

        $typeDokumenGRP = TypeDokumenStok::where('type_movement', 'Good Receive Penerimaan')->first();
        $typeDokumenST = TypeDokumenStok::where('type_movement', 'Stok Taking Good Receive Penerimaan')->first();

        $dombak = Dombak::where('dombak_id', $request->dombak_id)->first();

        $stokSebelumnya = $dombak->konversi()->where('tinggi', $request->tinggi_dombak_sebelumnya)->first()->volume;
        $stokPenerimaan = $dombak->konversi()->where('tinggi', $request->tinggi_dombak_setelahnya)->first()->volume;
        $stokAktual = $stokPenerimaan - $stokSebelumnya;
        $stokPL = $stokAktual - $fileLo->jumlah;

        $dokumenStokForStokTaking = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->where('stok_inventory_id', $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id)->where('member_id', Auth::user()->member()->first()->member_id)->where('jam_kerja_id', $request->jam_kerja_id)->where('type_dokumen_stok_id', $typeDokumenST->type_dokumen_stok_id)->where('dombak_id', $request->dombak_id)->where('tanggal', $request->tanggal)->where('stok_awal', $dombak->stok_inventory_dombak()->first()->stok)->where('stok_akhir', $stokSebelumnya)->where('is_confirmation', false)->first();
        if (is_null($dokumenStokForStokTaking)) {
            $dokumenStokForStokTaking = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->create([
                'stok_inventory_id' => $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id,
                'member_id' => Auth::user()->member()->first()->member_id,
                'jam_kerja_id' => $request->jam_kerja_id,
                'type_dokumen_stok_id' => $typeDokumenST->type_dokumen_stok_id,
                'dombak_id' => $request->dombak_id,
                'tanggal' => $request->tanggal,
                'stok_awal' => $dombak->stok_inventory_dombak()->first()->stok,
                'stok_akhir' => $stokSebelumnya,
                'is_confirmation' => false
            ]);
        }

        $dokumenStokForGRP = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->where('stok_inventory_id', $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id)->where('member_id', Auth::user()->member()->first()->member_id)->where('jam_kerja_id', $request->jam_kerja_id)->where('type_dokumen_stok_id', $typeDokumenGRP->type_dokumen_stok_id)->where('dombak_id', $request->dombak_id)->where('tanggal', $request->tanggal)->where('stok_awal', $stokSebelumnya)->where('stok_akhir', $stokPenerimaan)->where('is_confirmation', false)->first();
        if (is_null($dokumenStokForGRP)) {
            $dokumenStokForGRP = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->create([
                'stok_inventory_id' => $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id,
                'member_id' => Auth::user()->member()->first()->member_id,
                'jam_kerja_id' => $request->jam_kerja_id,
                'type_dokumen_stok_id' => $typeDokumenGRP->type_dokumen_stok_id,
                'dombak_id' => $request->dombak_id,
                'tanggal' => $request->tanggal,
                'stok_awal' => $stokSebelumnya,
                'stok_akhir' => $stokPenerimaan,
                'is_confirmation' => false
            ]);
        }

        $jenisLosses = JenisLosses::where('jenis_loss', 'Good Issue Penerimaan')->first();

        $losses = $dokumenStokForGRP->losses()->where('jenis_losses_id', $jenisLosses->jenis_losses_id)->where('tanggal', $request->tanggal)->where('simbol', $stokPL > 0 ? 'lebih' : 'kurang')->where('jumlah', abs($stokPL))->first();
        if (is_null($losses)) {
            $losses = $dokumenStokForGRP->losses()->create([
                'jenis_losses_id' => $jenisLosses->jenis_losses_id,
                'tanggal' => $request->tanggal,
                'simbol' => $stokPL > 0 ? 'lebih' : 'kurang',
                'jumlah' => abs($stokPL)
            ]);
        }

        if (is_null(DB::table('konversi_dokumen_stok')->where('konversi_id',  $dombak->konversi()->where('tinggi', $request->tinggi_dombak_sebelumnya)->first()->konversi_id)->where('dokumen_stok_id', $dokumenStokForStokTaking->dokumen_stok_id)->first())) {
            DB::table('konversi_dokumen_stok')->insert([
                'konversi_id' =>  $dombak->konversi()->where('tinggi', $request->tinggi_dombak_sebelumnya)->first()->konversi_id,
                'dokumen_stok_id' => $dokumenStokForStokTaking->dokumen_stok_id
            ]);
        }

        if (is_null(DB::table('konversi_dokumen_stok')->where('konversi_id',  $dombak->konversi()->where('tinggi', $request->tinggi_dombak_setelahnya)->first()->konversi_id)->where('dokumen_stok_id', $dokumenStokForGRP->dokumen_stok_id)->first())) {
            DB::table('konversi_dokumen_stok')->insert([
                'konversi_id' => $dombak->konversi()->where('tinggi', $request->tinggi_dombak_setelahnya)->first()->konversi_id,
                'dokumen_stok_id' => $dokumenStokForGRP->dokumen_stok_id
            ]);
        }

        $penerimaan = $fileLo->penerimaan()->where('dokumen_stok_id', $dokumenStokForGRP->dokumen_stok_id)->where('tanggal', $request->tanggal)->first();
        if (is_null($penerimaan)) {
            $penerimaan = $fileLo->penerimaan()->create([
                'dokumen_stok_id' => $dokumenStokForGRP->dokumen_stok_id,
                'tanggal' => $request->tanggal,
            ]);

            if (is_null($penerimaan->penerimaan_stok_taking()->where('dokumen_stok_id', $dokumenStokForStokTaking->dokumen_stok_id)->first())) {
                $penerimaan->penerimaan_stok_taking()->create([
                    'dokumen_stok_id' => $dokumenStokForStokTaking->dokumen_stok_id
                ]);
            }
        }

        return redirect('/stok/penerimaan/verifikasi/' . $penerimaan->penerimaan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penerimaan sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function show_penerimaan(Penerimaan $penerimaan)
    {
        return view('stok/penerimaan/show_penerimaan', compact('penerimaan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penerimaan $penerimaan)
    {
        return view('stok/penerimaan/edit');
    }

    public function confirmation(Penerimaan $penerimaan)
    {
        // Penerimaan Stok Taking // Sebelum Stok Masuk
        $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->update([
            'is_confirmation' => true
        ]);

        $temp = 0;
        foreach ($penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->stok_inventory()->first()->stok_inventory_dombak()->whereNot('dombak_id', $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->dombak()->first()->dombak_id)->get() as $sid) {
            $temp += $sid->stok;
        }

        $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->stok_inventory()->first()->stok_inventory_dombak()->where('dombak_id', $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->dombak()->first()->dombak_id)->first()->update([
            'stok' => $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->stok_akhir
        ]);

        $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->stok_inventory()->first()->update([
            'stok' => $penerimaan->dokumen_stok()->first()->stok_akhir + $temp
        ]);

        // Penerimaan Stok
        $penerimaan->dokumen_stok()->first()->update([
            'is_confirmation' => true
        ]);

        $temp = 0;
        foreach ($penerimaan->dokumen_stok()->first()->stok_inventory()->first()->stok_inventory_dombak()->whereNot('dombak_id', $penerimaan->dokumen_stok()->first()->dombak()->first()->dombak_id)->get() as $sid) {
            $temp += $sid->stok;
        }

        $penerimaan->dokumen_stok()->first()->stok_inventory()->first()->stok_inventory_dombak()->where('dombak_id', $penerimaan->dokumen_stok()->first()->dombak()->first()->dombak_id)->first()->update([
            'stok' => $penerimaan->dokumen_stok()->first()->stok_akhir
        ]);

        $penerimaan->dokumen_stok()->first()->stok_inventory()->first()->update([
            'stok' => $penerimaan->dokumen_stok()->first()->stok_akhir + $temp
        ]);

        return redirect('/stok/penerimaan/riwayat/' . $penerimaan->penerimaan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penerimaan sudah dikonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penerimaan $penerimaan)
    {
        DB::table('konversi_dokumen_stok')->where('dokumen_stok_id', $penerimaan->dokumen_stok_id)->delete();

        $penerimaan->dokumen_stok()->first()->losses()->delete();
        $penerimaan->dokumen_stok()->delete();
        $penerimaan->delete();

        return redirect('/stok/penerimaan')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penerimaan sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
