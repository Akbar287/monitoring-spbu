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
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class PenerimaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = FileLo::select('file_lo.*', 'pengiriman.tanggal')->join('pengiriman', 'pengiriman.pengiriman_id', 'file_lo.pengiriman_id')->where('pengiriman.is_confirmation_top', 1)->where('pengiriman.is_confirmation_admin', 1)->where('pengiriman.is_done', 1)->leftJoin('penerimaan', 'penerimaan.file_lo_id', 'file_lo.file_lo_id')->whereNull('penerimaan.file_lo_id')->get();
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
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('penerimaan.show', $row->file_lo_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Penerimaan BBM',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')]],
            'headerActions' => [['label' => 'Verifikasi', 'url' => route('penerimaan.verifikasi'), 'color' => 'warning', 'icon' => 'check-double'], ['label' => 'Riwayat', 'url' => route('penerimaan.riwayat'), 'color' => 'info', 'icon' => 'history']],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'No Faktur', 'data' => 'no_faktur'],
                    ['label' => 'No LO', 'data' => 'no_lo'],
                    ['label' => 'Nama Produk', 'data' => 'nama_produk'],
                    ['label' => 'Jumlah', 'data' => 'jumlah'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function verifikasi(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Penerimaan::join('dokumen_stok', 'dokumen_stok.dokumen_stok_id', 'penerimaan.dokumen_stok_id')->where('dokumen_stok.is_confirmation', '0')->get();
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
        return Inertia::render('Table/Index', [
            'title' => 'Verifikasi Penerimaan BBM',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Penerimaan BBM', 'url' => route('penerimaan.index')]],
            'headerActions' => [
                ['label' => 'Penerimaan', 'url' => route('penerimaan.index'), 'color' => 'primary', 'icon' => 'arrow-down'],
                ['label' => 'Riwayat', 'url' => route('penerimaan.riwayat'), 'color' => 'info', 'icon' => 'history'],
            ],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Dombak', 'data' => 'dombak'],
                    ['label' => 'No Faktur', 'data' => 'no_faktur'],
                    ['label' => 'No LO', 'data' => 'no_lo'],
                    ['label' => 'Nama Produk', 'data' => 'nama_produk'],
                    ['label' => 'Jumlah', 'data' => 'jumlah'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
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

        if ($request->ajax() && ! $request->inertia()) {
            $data = Penerimaan::join('dokumen_stok', 'dokumen_stok.dokumen_stok_id', 'penerimaan.dokumen_stok_id')->where('dokumen_stok.is_confirmation', '1')->whereBetween('dokumen_stok.tanggal', [$waktuMulai, $waktuSelesai])->get();
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
        return Inertia::render('Table/Index', [
            'title' => 'Riwayat Penerimaan BBM',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')]],
            'headerActions' => [['label' => 'Penerimaan', 'url' => route('penerimaan.index'), 'color' => 'primary', 'icon' => 'arrow-down'], ['label' => 'Verifikasi', 'url' => route('penerimaan.verifikasi'), 'color' => 'warning', 'icon' => 'check-double']],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Dombak', 'data' => 'dombak'],
                    ['label' => 'No Faktur', 'data' => 'no_faktur'],
                    ['label' => 'No LO', 'data' => 'no_lo'],
                    ['label' => 'Nama Produk', 'data' => 'nama_produk'],
                    ['label' => 'Jumlah', 'data' => 'jumlah'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function verifikasi_show(Penerimaan $penerimaan)
    {
        $aktual = 0;
        foreach ($penerimaan->file_lo()->first()->penerimaan()->get() as $penerimaanLoop) {
            $aktual += $penerimaanLoop->dokumen_stok()->first()->stok_akhir - $penerimaanLoop->dokumen_stok()->first()->stok_awal;
        }
        $satuan_tinggi = $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->konversi()->first()->satuan_ukur_tinggi()->first()->nama_satuan;
        $satuan_volume = $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->konversi()->first()->satuan_ukur_volume()->first()->nama_satuan;
        $actions = $this->penerimaanShowActions($penerimaan, 'penerimaan.file.index');
        $ds = $penerimaan->dokumen_stok()->first();

        return Inertia::render('Show/Index', [
            'title' => 'Verifikasi Penerimaan BBM',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Penerimaan BBM', 'url' => route('penerimaan.index')], ['label' => 'Verifikasi', 'url' => route('penerimaan.verifikasi')]],
            'backUrl' => route('penerimaan.verifikasi'),
            'fields' => $this->penerimaanShowFields($penerimaan, $aktual),
            'actions' => $actions,
            'deleteUrl' => $ds && ! $ds->is_confirmation ? route('penerimaan.delete', $penerimaan->penerimaan_id) : null,
            'deleteConfirm' => "Penerimaan akan dihapus!\n Lanjutkan?",
        ]);
    }

    public function riwayat_show(Penerimaan $penerimaan)
    {
        $satuan_tinggi = $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->konversi()->first()->satuan_ukur_tinggi()->first()->nama_satuan;
        $satuan_volume = $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->konversi()->first()->satuan_ukur_volume()->first()->nama_satuan;
        return Inertia::render('Show/Index', [
            'title' => 'Riwayat Penerimaan BBM',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Penerimaan BBM', 'url' => route('penerimaan.index')], ['label' => 'Riwayat', 'url' => route('penerimaan.riwayat')]],
            'backUrl' => route('penerimaan.riwayat'),
            'fields' => $this->penerimaanShowFields($penerimaan),
            'actions' => [
                ['label' => 'Dokumen', 'url' => route('penerimaan.file.riwayat.index', $penerimaan->penerimaan_id), 'color' => 'info', 'icon' => 'folder-open'],
            ],
        ]);
    }

    public function show(FileLo $fileLo)
    {
        $dombak = $fileLo->produk()->first()->stok_inventory()->first()->stok_inventory_dombak()->get();
        $jamKerja = JamKerja::get();

        $drp = $fileLo->detail_rencana_pembelian()->first();
        $rp = $drp ? $drp->rencana_pembelian()->first() : null;
        $peng = $fileLo->pengiriman()->first();

        $dombakRows = $dombak->map(function ($d) {
            $master = $d->dombak()->first();
            $min = $master ? optional($master->konversi()->orderBy('tinggi', 'asc')->first())->tinggi : null;
            $max = $master ? optional($master->konversi()->orderBy('tinggi', 'desc')->first())->tinggi : null;

            return [
                'id' => $d->dombak_id,
                'nama' => optional($master)->nama_dombak,
                'min' => $min ?? 0,
                'max' => $max ?? 0,
            ];
        })->values()->all();

        return Inertia::render('Penerimaan/Process', [
            'title' => 'Proses Penerimaan BBM',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Penerimaan BBM', 'url' => route('penerimaan.index')]],
            'backUrl' => route('penerimaan.index'),
            'action' => route('penerimaan.store', $fileLo->file_lo_id),
            'info' => [
                ['label' => 'Id Rencana Pembelian', 'value' => optional($rp)->rencana_pembelian_id],
                ['label' => 'SPBU', 'value' => $rp ? optional($rp->spbu()->first())->nama_spbu : null],
                ['label' => 'Produk', 'value' => optional($fileLo->produk()->first())->nama_produk],
                ['label' => 'Jumlah / Quantity', 'value' => number_format($fileLo->jumlah, 6, '.', ',') . ' ' . $fileLo->satuan_jumlah],
                ['label' => 'No. Faktur', 'value' => $fileLo->no_faktur],
                ['label' => 'No. LO', 'value' => $fileLo->no_lo],
                ['label' => 'No DO', 'value' => optional($peng)->no_do],
                ['label' => 'No Pol', 'value' => optional($peng)->no_pol],
                ['label' => 'Tanggal Pengiriman', 'value' => optional($peng)->tanggal],
            ],
            'dombakRows' => $dombakRows,
            'jamKerjaOptions' => $jamKerja->map(fn ($s) => ['value' => $s->jam_kerja_id, 'label' => $s->nama_jam_kerja])->values()->all(),
            'volumeDibeli' => number_format($fileLo->jumlah, 6, '.', ','),
            'api' => ['konversi' => url('/master/dombak/konversi/api')],
        ]);
    }

    public function store(Request $request, FileLo $fileLo)
    {
        // $request->validate([
        //     'dombak_id' => ['required'],
        //     'tanggal' => ['required'],
        //     'jam_kerja_id' => ['required'],
        //     'tinggi_dombak_sebelumnya' => ['required'],
        //     'tinggi_dombak_setelahnya' => ['required'],
        // ]);

        $typeDokumenGRP = TypeDokumenStok::where('type_movement', 'Good Receive Penerimaan')->first();
        $typeDokumenST = TypeDokumenStok::where('type_movement', 'Stok Taking Good Receive Penerimaan')->first();
        $typeReveivePenyesuaian = TypeDokumenStok::select('type_dokumen_stok_id')->where('type_movement', 'Stok Taking Good Receive Penyesuaian')->first();
        $jenisLosses = JenisLosses::where('jenis_loss', 'Good Issue Penerimaan')->first();
        $selisihStok = 0;
        $dokumenStokForGRP = null;

        for ($i = 0; $i < count($request->volume_sebelumnya); $i++) {
            if (!is_null($request->tinggi_dombak_sebelumnya[$i])) {
                $dombak = Dombak::where('dombak_id', $request->dombak_id[$i])->first();
                $stokSebelumnya = $dombak->konversi()->where('tinggi', $request->tinggi_dombak_sebelumnya[$i])->first()->volume;
                $stokPenerimaan = $dombak->konversi()->where('tinggi', $request->tinggi_dombak_setelahnya[$i])->first()->volume;
                $selisihStok = $stokPenerimaan - $stokSebelumnya;
                $selisihAwal = $dombak->stok_inventory_dombak()->first()->stok - $stokSebelumnya;
                $jumlahAktual = $request->alokasi_dombak[$i];
                $selisihPnLGR = $selisihStok - $jumlahAktual;
                $stokPenerimaan = $stokPenerimaan - $selisihPnLGR;
                $stokPenyesuaian = ($stokPenerimaan + $selisihAwal);

                // Sesuaikan Stok Taking Ke Penerimaan
                $dokumenStokForStokTaking = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->where('stok_inventory_id', $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id)->where('member_id', Auth::user()->member()->first()->member_id)->where('jam_kerja_id', $request->jam_kerja_id)->where('type_dokumen_stok_id', $typeDokumenST->type_dokumen_stok_id)->where('dombak_id', $request->dombak_id[$i])->where('tanggal', $request->tanggal)->where('stok_awal', $dombak->stok_inventory_dombak()->first()->stok)->where('stok_akhir', $stokSebelumnya)->where('is_confirmation', 0)->first();
                if (is_null($dokumenStokForStokTaking)) {
                    $dokumenStokForStokTaking = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->create([
                        'stok_inventory_id' => $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id,
                        'member_id' => Auth::user()->member()->first()->member_id,
                        'jam_kerja_id' => $request->jam_kerja_id,
                        'type_dokumen_stok_id' => $typeDokumenST->type_dokumen_stok_id,
                        'dombak_id' => $request->dombak_id[$i],
                        'tanggal' => $request->tanggal,
                        'stok_awal' => $dombak->stok_inventory_dombak()->first()->stok,
                        'stok_akhir' => $stokSebelumnya,
                        'is_confirmation' => 0,
                        'waktu' => $request->jam_penerimaan
                    ]);
                }
                // End Penyesuaian Stok Taking

                // Penerimaan
                $dokumenStokForGRP = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->where('stok_inventory_id', $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id)->where('member_id', Auth::user()->member()->first()->member_id)->where('jam_kerja_id', $request->jam_kerja_id)->where('type_dokumen_stok_id', $typeDokumenGRP->type_dokumen_stok_id)->where('dombak_id', $request->dombak_id[$i])->where('tanggal', $request->tanggal)->where('stok_awal', $stokSebelumnya)->where('stok_akhir', $stokPenerimaan)->where('is_confirmation', 0)->first();
                if (is_null($dokumenStokForGRP)) {
                    $dokumenStokForGRP = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->create([
                        'stok_inventory_id' => $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id,
                        'member_id' => Auth::user()->member()->first()->member_id,
                        'jam_kerja_id' => $request->jam_kerja_id,
                        'type_dokumen_stok_id' => $typeDokumenGRP->type_dokumen_stok_id,
                        'dombak_id' => $request->dombak_id[$i],
                        'tanggal' => $request->tanggal,
                        'stok_awal' => $stokSebelumnya,
                        'stok_akhir' => $stokPenerimaan,
                        'is_confirmation' => 0,
                        'waktu' => $request->jam_penerimaan
                    ]);
                }

                // Profit / Loss
                $dokumenStokForGRP->losses()->create([
                    'jenis_losses_id' => $jenisLosses->jenis_losses_id,
                    'tanggal' => $request->tanggal,
                    'simbol' => $selisihPnLGR > 0 ? 'lebih' : 'kurang',
                    'jumlah' => abs($selisihPnLGR)
                ]);

                if ($dokumenStokForGRP->losses()->count() > 1) {
                    $dokumenStokForGRP->losses()->first()->delete();
                }
                // End Penerimaan

                // Penyesuaian Penerimaan
                $dokumenStokForPenyesuaian = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->where('stok_inventory_id', $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id)->where('member_id', Auth::user()->member()->first()->member_id)->where('jam_kerja_id', $request->jam_kerja_id)->where('type_dokumen_stok_id', $typeReveivePenyesuaian->type_dokumen_stok_id)->where('dombak_id', $request->dombak_id[$i])->where('tanggal', $request->tanggal)->where('stok_awal', $stokPenerimaan)->where('stok_akhir', $stokPenyesuaian)->where('is_confirmation', 0)->first();
                if (is_null($dokumenStokForPenyesuaian)) {
                    $dokumenStokForPenyesuaian = $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->dokumen_stok()->create([
                        'stok_inventory_id' => $dombak->stok_inventory_dombak()->first()->stok_inventory()->first()->stok_inventory_id,
                        'member_id' => Auth::user()->member()->first()->member_id,
                        'jam_kerja_id' => $request->jam_kerja_id,
                        'type_dokumen_stok_id' => $typeReveivePenyesuaian->type_dokumen_stok_id,
                        'dombak_id' => $request->dombak_id[$i],
                        'tanggal' => $request->tanggal,
                        'stok_awal' => $stokPenerimaan,
                        'stok_akhir' => $stokPenyesuaian,
                        'is_confirmation' => 0,
                        'waktu' => $request->jam_penerimaan
                    ]);
                }
                // End Penyesuaian

                // Relasi ke Konversi
                if (is_null(DB::table('konversi_dokumen_stok')->where('konversi_id',  $dombak->konversi()->where('tinggi', $request->tinggi_dombak_sebelumnya[$i])->first()->konversi_id)->where('dokumen_stok_id', $dokumenStokForStokTaking->dokumen_stok_id)->first())) {
                    DB::table('konversi_dokumen_stok')->insert([
                        'konversi_id' =>  $dombak->konversi()->where('tinggi', $request->tinggi_dombak_sebelumnya[$i])->first()->konversi_id,
                        'dokumen_stok_id' => $dokumenStokForStokTaking->dokumen_stok_id
                    ]);
                }

                if (is_null(DB::table('konversi_dokumen_stok')->where('konversi_id',  $dombak->konversi()->where('tinggi', $request->tinggi_dombak_setelahnya[$i])->first()->konversi_id)->where('dokumen_stok_id', $dokumenStokForGRP->dokumen_stok_id)->first())) {
                    DB::table('konversi_dokumen_stok')->insert([
                        'konversi_id' => $dombak->konversi()->where('tinggi', $request->tinggi_dombak_setelahnya[$i])->first()->konversi_id,
                        'dokumen_stok_id' => $dokumenStokForGRP->dokumen_stok_id
                    ]);
                }
                // End Relasi

                // Penerimaan
                $penerimaan = $fileLo->penerimaan()->where('dokumen_stok_id', $dokumenStokForGRP->dokumen_stok_id)->where('tanggal', $request->tanggal)->first();
                if (is_null($penerimaan)) {
                    $penerimaan = $fileLo->penerimaan()->create([
                        'dokumen_stok_id' => $dokumenStokForGRP->dokumen_stok_id,
                        'tanggal' => $request->tanggal,
                    ]);

                    if (is_null($penerimaan->penerimaan_stok_taking()->where('dokumen_stok_id', $dokumenStokForStokTaking->dokumen_stok_id)->first())) {
                        $penerimaan->penerimaan_stok_taking()->create([
                            'dokumen_stok_id' => $dokumenStokForStokTaking->dokumen_stok_id,
                        ]);
                    }

                    if (is_null($penerimaan->penerimaan_stok_taking_penyesuaian()->where('dokumen_stok_id', $dokumenStokForPenyesuaian->dokumen_stok_id)->first())) {
                        $penerimaan->penerimaan_stok_taking_penyesuaian()->create([
                            'dokumen_stok_id' => $dokumenStokForPenyesuaian->dokumen_stok_id,
                        ]);
                    }
                }
                // End Penerimaan
            }
        }

        return redirect('/stok/penerimaan/verifikasi/' . $penerimaan->penerimaan_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Penerimaan sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function show_penerimaan(Penerimaan $penerimaan)
    {
        $actions = $this->penerimaanShowActions($penerimaan, 'penerimaan.file.index');
        $ds = $penerimaan->dokumen_stok()->first();

        return Inertia::render('Show/Index', [
            'title' => 'Detail Penerimaan BBM',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Penerimaan BBM', 'url' => route('penerimaan.index')]],
            'backUrl' => route('penerimaan.index'),
            'fields' => $this->penerimaanShowFields($penerimaan),
            'actions' => $actions,
            'deleteUrl' => $ds && ! $ds->is_confirmation ? route('penerimaan.delete', $penerimaan->penerimaan_id) : null,
            'deleteConfirm' => "Penerimaan akan dihapus!\n Lanjutkan?",
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penerimaan $penerimaan)
    {
        return redirect()->route('penerimaan.show_penerimaan', $penerimaan->penerimaan_id);
    }

    public function confirmation(Penerimaan $penerimaan)
    {
        // Penerimaan Stok Taking // Sebelum Stok Masuk
        $penerimaan->penerimaan_stok_taking()->first()->dokumen_stok()->first()->update([
            'is_confirmation' => 1
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
            'is_confirmation' => 1
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

        // Penyesuaian Stok Taking Penerimaan // Setelah Stok Masuk
        $penerimaan->penerimaan_stok_taking_penyesuaian()->first()->dokumen_stok()->first()->update([
            'is_confirmation' => 1
        ]);

        $temp = 0;
        $jumlahDombak = $penerimaan->penerimaan_stok_taking_penyesuaian()->first()->dokumen_stok()->first()->stok_inventory()->first()->stok_inventory_dombak()->count();

        foreach ($penerimaan->penerimaan_stok_taking_penyesuaian()->first()->dokumen_stok()->first()->stok_inventory()->first()->stok_inventory_dombak()->whereNot('dombak_id', $penerimaan->penerimaan_stok_taking_penyesuaian()->first()->dokumen_stok()->first()->dombak()->first()->dombak_id)->get() as $sid) {
            $temp += $sid->stok;
        }

        $penerimaan->penerimaan_stok_taking_penyesuaian()->first()->dokumen_stok()->first()->stok_inventory()->first()->stok_inventory_dombak()->where('dombak_id', $penerimaan->penerimaan_stok_taking_penyesuaian()->first()->dokumen_stok()->first()->dombak()->first()->dombak_id)->first()->update([
            'stok' => $penerimaan->penerimaan_stok_taking_penyesuaian()->first()->dokumen_stok()->first()->stok_akhir
        ]);

        $penerimaan->penerimaan_stok_taking_penyesuaian()->first()->dokumen_stok()->first()->stok_inventory()->first()->update([
            'stok' => ($jumlahDombak === 1) ? $penerimaan->penerimaan_stok_taking_penyesuaian()->first()->dokumen_stok()->first()->stok_akhir : $penerimaan->dokumen_stok()->first()->stok_akhir + $temp
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

    /**
     * Field detail penerimaan utk halaman show (verifikasi/riwayat/biasa).
     * Rantai relasi diguard optional() — paritas label blade lama.
     */
    private function penerimaanShowFields(Penerimaan $penerimaan, $aktual = null): array
    {
        $fl = $penerimaan->file_lo()->first();
        $drp = $fl ? $fl->detail_rencana_pembelian()->first() : null;
        $rp = $drp ? $drp->rencana_pembelian()->first() : null;
        $peng = $fl ? $fl->pengiriman()->first() : null;
        $ds = $penerimaan->dokumen_stok()->first();
        $pst = $penerimaan->penerimaan_stok_taking()->first();

        $tinggiSebelum = $pst ? optional(optional($pst->dokumen_stok()->first())->konversi()->first())->tinggi : null;
        $tinggiSetelah = $ds ? optional($ds->konversi()->first())->tinggi : null;

        return [
            ['label' => 'Id Rencana Pembelian', 'value' => optional($rp)->rencana_pembelian_id],
            ['label' => 'SPBU', 'value' => $rp ? optional($rp->spbu()->first())->nama_spbu : null],
            ['label' => 'Produk', 'value' => $drp ? optional($drp->produk()->first())->nama_produk : null],
            ['label' => 'RIT', 'value' => $drp ? number_format($drp->rit, 2, '.', ',') : null],
            ['label' => 'Jumlah / Quantity', 'value' => $drp ? number_format($drp->jumlah, 6, '.', ',') . ' ' . $drp->satuan_jumlah : null],
            ['label' => 'Harga', 'value' => $drp ? 'Rp ' . number_format($drp->harga, 6, '.', ',') : null],
            ['label' => 'Total Harga', 'value' => $drp ? 'Rp ' . number_format($drp->total_harga, 6, '.', ',') : null],
            ['label' => 'Pengiriman — Yang Menerima', 'value' => $peng ? optional(optional($peng->member()->first())->ktp()->first())->nama : null],
            ['label' => 'No DO', 'value' => optional($peng)->no_do],
            ['label' => 'No Pol', 'value' => optional($peng)->no_pol],
            ['label' => 'Tanggal Pengiriman', 'value' => optional($peng)->tanggal],
            ['label' => 'File LO', 'value' => $fl ? optional($fl->produk()->first())->nama_produk . ' (' . number_format($fl->jumlah, 6, ',', '.') . ' ' . $fl->satuan_jumlah . ')' : null],
            ['label' => 'No. Faktur', 'value' => optional($fl)->no_faktur],
            ['label' => 'No. LO', 'value' => optional($fl)->no_lo],
            ['label' => 'Dombak', 'value' => $ds ? optional($ds->dombak()->first())->nama_dombak : null],
            ['label' => 'Tanggal Penerimaan', 'value' => optional($ds)->tanggal],
            ['label' => 'Jam Kerja', 'value' => $ds ? optional($ds->jam_kerja()->first())->nama_jam_kerja : null],
            ['label' => 'Tinggi Dombak (cm) Sebelum Penerimaan', 'value' => $tinggiSebelum ?? 0],
            ['label' => 'Tinggi Dombak (cm) Setelah Penerimaan', 'value' => $tinggiSetelah ?? 0],
            ['label' => 'Volume (liter) Sebelum', 'value' => number_format(optional($ds)->stok_awal ?? 0, 6, '.', ',')],
            ['label' => 'Volume (liter) Setelah', 'value' => number_format(optional($ds)->stok_akhir ?? 0, 6, '.', ',')],
            ['label' => 'Volume (liter) Diterima', 'value' => number_format((optional($ds)->stok_akhir ?? 0) - (optional($ds)->stok_awal ?? 0), 6, '.', ',')],
            ['label' => 'Volume (liter) (Dibeli)', 'value' => $fl ? number_format($fl->jumlah, 6, '.', ',') : null],
            ['label' => 'Volume (liter) (Loss/Profit)', 'value' => $aktual !== null ? number_format($aktual, 6, '.', ',') : null],
            ['label' => 'Status Konfirmasi', 'value' => $ds && $ds->is_confirmation ? 'Terkonfirmasi' : 'Belum Terkonfirmasi'],
        ];
    }

    private function penerimaanShowActions(Penerimaan $penerimaan, string $dokumenRoute): array
    {
        $ds = $penerimaan->dokumen_stok()->first();
        $isPJ = optional(optional(optional(auth()->user()->member()->first())->jabatan()->first())->level()->first())->nama_level === 'Penanggung Jawab';

        $actions = [
            ['label' => 'Dokumen', 'url' => route($dokumenRoute, $penerimaan->penerimaan_id), 'color' => 'info', 'icon' => 'folder-open'],
        ];
        if ($ds && ! $ds->is_confirmation && $isPJ) {
            $actions[] = ['label' => 'Konfirmasi', 'method' => 'put', 'url' => route('penerimaan.show_confirmation', $penerimaan->penerimaan_id),
                'color' => 'success', 'icon' => 'check', 'confirm' => "Penerimaan ini akan dikonfirmasi!\n Lanjutkan?"];
        }

        return $actions;
    }
}