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
use Inertia\Inertia;
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
        if ($request->ajax() && ! $request->inertia()) {
            $data = $produkSelected == 'all' ? DokumenStok::where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking')->first()->type_dokumen_stok_id)->where('is_confirmation', 0)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('created_at', 'desc')->get() : DokumenStok::join('stok_inventory', 'stok_inventory.stok_inventory_id', 'dokumen_stok.stok_inventory_id')->where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking')->first()->type_dokumen_stok_id)->where('stok_inventory.produk_id', $request->produkSelected)->where('is_confirmation', 0)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('tanggal', 'desc')->get();
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
        return Inertia::render('Table/Index', [
            'title' => 'Stok Taking',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')]],
            'createUrl' => route('stok-taking.create'),
            'headerActions' => [['label' => 'Riwayat', 'url' => route('stok-taking.riwayat'), 'color' => 'info', 'icon' => 'history']],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'selectFilters' => [
                ['name' => 'produkSelected', 'label' => 'Produk', 'value' => $produkSelected, 'options' => array_merge([['value' => 'all', 'label' => 'Semua']], $produk->map(fn ($x) => ['value' => $x->produk_id, 'label' => $x->nama_produk])->all())],
            ],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Tipe Dokumen', 'data' => 'type_dokumen'],
                    ['label' => 'Nama Produk', 'data' => 'nama_produk'],
                    ['label' => 'Jam Kerja', 'data' => 'jam_kerja'],
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
            $data = DokumenStok::where('type_dokumen_stok_id', TypeDokumenStok::where('type_movement', 'Stok Taking')->first()->type_dokumen_stok_id)->where('is_confirmation', 1)->whereBetween('tanggal', [$waktuMulai, $waktuSelesai])->orderBy('created_at', 'desc')->get();
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
        return Inertia::render('Table/Index', [
            'title' => 'Riwayat Stok Taking',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')]],
            'headerActions' => [['label' => 'Stok Taking', 'url' => route('stok-taking.index'), 'color' => 'primary', 'icon' => 'thermometer-full']],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Tipe Dokumen', 'data' => 'type_dokumen'],
                    ['label' => 'Nama Produk', 'data' => 'nama_produk'],
                    ['label' => 'Jam Kerja', 'data' => 'jam_kerja'],
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
        $jamKerja = JamKerja::select('nama_jam_kerja')->addSelect('jam_kerja_id')->addSelect('batas_akhir_in')->addSelect('batas_awal_in')->get();
        $dombak = Dombak::where('is_aktif', 1)->get();
        $typeDokumen = TypeDokumenStok::where('type_movement', 'Stok Taking')->get();
        $dombakOpts = $dombak->map(function ($s) {
            $sid = $s->stok_inventory_dombak()->first();
            $si = $sid ? $sid->stok_inventory()->first() : null;
            $prod = $si ? $si->produk()->first() : null;

            return [
                'value' => $s->dombak_id,
                'label' => $s->nama_dombak . ($prod ? ' (' . $prod->nama_produk . '-' . $sid->stok . 'Liter)' : ''),
            ];
        })->values()->all();

        return Inertia::render('StokTaking/Create', [
            'title' => 'Tambah Stok Taking',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Stok Taking', 'url' => route('stok-taking.index')]],
            'backUrl' => route('stok-taking.index'),
            'action' => route('stok-taking.store'),
            'options' => [
                'typeDokumen' => $typeDokumen->map(fn ($s) => ['value' => $s->type_dokumen_stok_id, 'label' => $s->type_movement])->values()->all(),
                'dombak' => $dombakOpts,
                'jamKerja' => $jamKerja->map(fn ($s) => ['value' => $s->jam_kerja_id, 'label' => $s->nama_jam_kerja . ' (' . $s->batas_awal_in . '-' . $s->batas_akhir_in . ')'])->values()->all(),
            ],
            'api' => [
                'dombak' => url('/master/dombak/api'),
                'konversi' => url('/master/dombak/konversi/api'),
            ],
        ]);
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
        $tinggiDombak = Dombak::where('dombak_id', $request->dombak_id)->first()->konversi()->where('tinggi', $request->tinggi_dombak)->first();

        $type_dokumen_stock = TypeDokumenStok::where('type_dokumen_stok_id', $request->type_dokumen_id)->first();
        $stok_inventory = Dombak::where('dombak_id', $request->dombak_id)->first()->stok_inventory_dombak()->first()->stok_inventory()->first();

        $dokumenStok = null;

        if ($type_dokumen_stock->type_movement == 'Stok Taking') {
            $awal = Dombak::where('dombak_id', $request->dombak_id)->first()->stok_inventory_dombak()->first()->stok;

            if (is_null($tinggiDombak)) {
                return redirect('/stok/stok-taking/create')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Gagal!</strong> Tinggi Dombak tidak ditemukan di tabel konversi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
                exit;
            }
            $akhir = $tinggiDombak->volume;

            $dokumenStok = $type_dokumen_stock->dokumen_stok()->where('stok_inventory_id', $stok_inventory->stok_inventory_id)->where('jam_kerja_id', $request->jam_kerja_id)->where('member_id', Auth::user()->member_id)->where('dombak_id', $request->dombak_id)->where('tanggal', $request->tanggal)->where('stok_awal', $awal)->where('stok_akhir', $akhir)->where('is_confirmation', 0)->first();
            $jamKerja = JamKerja::where('jam_kerja_id', $request->jam_kerja_id)->first();
            if (is_null($dokumenStok)) {
                $dokumenStok = $type_dokumen_stock->dokumen_stok()->create([
                    'stok_inventory_id' => $stok_inventory->stok_inventory_id,
                    'jam_kerja_id' => $request->jam_kerja_id,
                    'member_id' => Auth::user()->member_id,
                    'dombak_id' => $request->dombak_id,
                    'tanggal' => $request->tanggal,
                    'stok_awal' => $awal,
                    'stok_akhir' => $akhir,
                    'is_confirmation' => 0,
                    'waktu' => $jamKerja->jam_datang_in
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
            'is_confirmation' => 1
        ]);

        return redirect('/stok/stok-taking/' . $stok->dokumen_stok_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Stok Taking sudah dikonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(DokumenStok $stok)
    {
        $fields = [
            ['label' => 'Tipe Dokumen', 'value' => optional($stok->type_dokumen_stok()->first())->type_movement],
            ['label' => 'Dombak', 'value' => $stok->dombak()->first()->nama_dombak . ' (' . $stok->stok_inventory()->first()->produk()->first()->nama_produk . '-' . $stok->dombak()->first()->stok_inventory_dombak()->first()->stok . 'Liter)'],
            ['label' => 'Tanggal', 'value' => $stok->tanggal],
            ['label' => 'Jam Kerja', 'value' => $stok->jam_kerja()->first()->nama_jam_kerja . ' (' . $stok->jam_kerja()->first()->batas_awal_in . '-' . $stok->jam_kerja()->first()->batas_akhir_in . ')'],
            ['label' => 'Tinggi Dombak', 'value' => $stok->dombak()->first()->konversi()->where('volume', $stok->stok_akhir)->first()->tinggi ?? 0],
            ['label' => 'Volume Sebelumnya', 'value' => $stok->stok_awal],
            ['label' => 'Volume', 'value' => $stok->stok_akhir],
            ['label' => 'Selisih (Losses)', 'value' => is_null($stok->losses()->first()) ? 0 : (($stok->losses()->first()->simbol == 'kurang' ? '-' : '+') . $stok->losses()->first()->jumlah)],
        ];

        $actions = [
            ['label' => 'File', 'url' => route('stok-taking.file.index', $stok->dokumen_stok_id), 'color' => 'info', 'icon' => 'folder-open'],
        ];
        $bolehAksi = ! $stok->is_confirmation && \Illuminate\Support\Facades\Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Penanggung Jawab';
        if ($bolehAksi) {
            $actions[] = ['label' => 'Konfirmasi', 'url' => route('stok-taking.confirmation', $stok->dokumen_stok_id), 'method' => 'put', 'confirm' => 'Stok Inventory akan dikonfirmasi! Lanjutkan?', 'color' => 'success', 'icon' => 'check'];
        }

        return Inertia::render('Show/Index', [
            'title' => 'Detail Stok Taking',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Stok Taking', 'url' => route('stok-taking.index')]],
            'backUrl' => route('stok-taking.index'),
            'fields' => $fields,
            'actions' => $actions,
            'deleteUrl' => $bolehAksi ? route('stok-taking.delete', $stok->dokumen_stok_id) : null,
            'deleteConfirm' => 'Stok Inventory akan dihapus! Lanjutkan?',
        ]);
    }

    public function riwayat_show(DokumenStok $stok)
    {
        $fields = [
            ['label' => 'Tipe Dokumen', 'value' => optional($stok->type_dokumen_stok()->first())->type_movement],
            ['label' => 'Dombak', 'value' => $stok->dombak()->first()->nama_dombak . ' (' . $stok->stok_inventory()->first()->produk()->first()->nama_produk . '-' . $stok->dombak()->first()->stok_inventory_dombak()->first()->stok . 'Liter)'],
            ['label' => 'Tanggal', 'value' => $stok->tanggal],
            ['label' => 'Jam Kerja', 'value' => $stok->jam_kerja()->first()->nama_jam_kerja . ' (' . $stok->jam_kerja()->first()->batas_awal_in . '-' . $stok->jam_kerja()->first()->batas_akhir_in . ')'],
            ['label' => 'Tinggi Dombak', 'value' => $stok->dombak()->first()->konversi()->where('volume', $stok->stok_akhir)->first()->tinggi ?? 0],
            ['label' => 'Volume Sebelumnya', 'value' => $stok->stok_awal],
            ['label' => 'Volume', 'value' => $stok->stok_akhir],
            ['label' => 'Selisih (Losses)', 'value' => is_null($stok->losses()->first()) ? 0 : (($stok->losses()->first()->simbol == 'kurang' ? '-' : '+') . $stok->losses()->first()->jumlah)],
        ];

        $actions = [
            ['label' => 'File', 'url' => route('stok-taking.file.index', $stok->dokumen_stok_id), 'color' => 'info', 'icon' => 'folder-open'],
        ];
        if (! $stok->is_confirmation) {
            $actions[] = ['label' => 'Ubah', 'url' => route('stok-taking.edit', $stok->dokumen_stok_id), 'color' => 'success', 'icon' => 'pen'];
            $actions[] = ['label' => 'Konfirmasi', 'url' => route('stok-taking.confirmation', $stok->dokumen_stok_id), 'method' => 'put', 'confirm' => 'Stok Inventory akan dikonfirmasi! Lanjutkan?', 'color' => 'info', 'icon' => 'check'];
        }

        return Inertia::render('Show/Index', [
            'title' => 'Detail Riwayat Stok Taking',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')], ['label' => 'Riwayat Stok Taking', 'url' => route('stok-taking.riwayat')]],
            'backUrl' => route('stok-taking.index'),
            'fields' => $fields,
            'actions' => $actions,
            'deleteUrl' => $stok->is_confirmation ? null : route('stok-taking.delete', $stok->dokumen_stok_id),
            'deleteConfirm' => 'Stok Inventory akan dihapus! Lanjutkan?',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DokumenStok $stok)
    {
        $typeDokumen = TypeDokumenStok::select('type_movement')->addSelect('type_dokumen_stok_id')->get();
        $dokumenStokExist = DokumenStok::orderBy('created_at', 'desc')->first();
        $dombak = Dombak::where('is_aktif', 1)->get();
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
