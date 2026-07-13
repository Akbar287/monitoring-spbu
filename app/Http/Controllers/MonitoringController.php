<?php

namespace App\Http\Controllers;

use App\Models\DokumenStok;
use App\Models\Dombak;
use App\Models\JamKerja;
use App\Models\Produk;
use App\Models\StokInventory;
use App\Models\TypeDokumenStok;
use Carbon\Carbon;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
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
        if ($request->ajax() && ! $request->inertia()) {
            $data = StokInventory::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_produk', function ($row) {
                    $actionBtn = $row->produk()->first()->nama_produk;
                    return $actionBtn;
                })
                ->addColumn('stok', function ($row) {
                    $actionBtn = number_format($row->stok, 6, ".", ",");
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('monitoring.show', $row->stok_inventory_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Monitoring Stok',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')]],
            'createUrl' => route('monitoring.create'),
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Produk', 'data' => 'nama_produk'],
                    ['label' => 'Stok', 'data' => 'stok'],
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
        $produk = Produk::all();
        $dombak = Dombak::all();
        return Inertia::render('Monitoring/Form', [
            'title' => 'Tambah Monitoring Stok',
            'breadcrumbs' => [
                ['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')],
                ['label' => 'Monitoring Stok', 'url' => route('monitoring.index')],
            ],
            'action' => route('monitoring.store'),
            'method' => 'post',
            'backUrl' => route('monitoring.index'),
            'products' => $produk->map(fn ($p) => ['value' => $p->produk_id, 'label' => $p->nama_produk])->values()->all(),
            'dombaks' => $dombak->map(fn ($d) => ['id' => $d->dombak_id, 'name' => $d->nama_dombak, 'stok' => '0.00'])->values()->all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'stok' => ['required'],
            'produk_id' => ['required']
        ]);

        $produk = Produk::find($request->produk_id);
        if ($produk->stok_inventory()->count() > 0) {
            return redirect('/stok/monitoring/create')->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Gagal!</strong> Data Stok Inventory sebelumnya sudah pernah ditambahkan.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }

        $totalStok = 0;
        for ($i = 0; $i < count($request->stok); $i++) {
            if ($request->stok[$i] > 0) {
                $totalStok = $totalStok + $request->stok[$i];
            }
        }

        $stok = $produk->stok_inventory()->where('stok', $totalStok)->first();
        if (is_null($stok)) {
            $stok = $produk->stok_inventory()->create($this->stokInventoryData($totalStok));
        }

        for ($i = 0; $i < count($request->stok); $i++) {
            if ($request->stok[$i] > 0) {
                if (is_null($stok->stok_inventory_dombak()->where("dombak_id", $request->dombak_id[$i])->where("stok", $request->stok[$i])->first())) {
                    $stok->stok_inventory_dombak()->create([
                        "dombak_id" => $request->dombak_id[$i],
                        "stok" => $request->stok[$i],
                    ]);
                }
            }
        }

        return redirect('/stok/monitoring/' . $stok->stok_inventory_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Stok Inventory sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Display the specified resource.
     */
    public function show(StokInventory $stok)
    {
        $level = optional(optional(optional(Auth::user()?->member()->first())->jabatan()->first())->level()->first())->nama_level;

        return Inertia::render('Show/Index', [
            'title' => 'Detail Monitoring Stok',
            'breadcrumbs' => [
                ['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')],
                ['label' => 'Monitoring Stok', 'url' => route('monitoring.index')],
            ],
            'backUrl' => route('monitoring.index'),
            'fields' => [
                ['label' => 'Produk', 'value' => optional($stok->produk()->first())->nama_produk],
                ['label' => 'Total Stok', 'value' => number_format($stok->stok, 6, '.', ',')],
            ],
            'tables' => [[
                'title' => 'Dombak',
                'columns' => ['Id Dombak', 'Nama Dombak', 'Initial Stok'],
                'rows' => $stok->stok_inventory_dombak()->get()->map(fn ($d) => [
                    optional($d->dombak()->first())->dombak_id,
                    optional($d->dombak()->first())->nama_dombak,
                    number_format($d->stok ?? 0, 6, '.', ','),
                ])->values()->all(),
            ]],
            'actions' => array_values(array_filter([
                ['label' => 'Riwayat Stok', 'url' => route('monitoring.show_riwayat', $stok->stok_inventory_id), 'color' => 'primary', 'icon' => 'history'],
                $level === 'Penanggung Jawab' ? ['label' => 'Ubah', 'url' => route('monitoring.edit', $stok->stok_inventory_id), 'color' => 'success', 'icon' => 'pen'] : null,
            ])),
            'deleteUrl' => $level === 'Penanggung Jawab' ? route('monitoring.delete', $stok->stok_inventory_id) : null,
            'deleteConfirm' => "Stok Inventory akan dihapus!\n Lanjutkan?",
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StokInventory $stok)
    {
        $produk = Produk::all();
        $dombak = Dombak::all();
        return Inertia::render('Monitoring/Form', [
            'title' => 'Ubah Monitoring Stok',
            'breadcrumbs' => [
                ['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')],
                ['label' => 'Monitoring Stok', 'url' => route('monitoring.index')],
                ['label' => 'Detail', 'url' => route('monitoring.show', $stok->stok_inventory_id)],
            ],
            'action' => route('monitoring.update', $stok->stok_inventory_id),
            'method' => 'put',
            'backUrl' => route('monitoring.show', $stok->stok_inventory_id),
            'product' => [
                'id' => optional($stok->produk()->first())->produk_id,
                'label' => optional($stok->produk()->first())->nama_produk,
            ],
            'dombaks' => $dombak->map(function ($d) use ($stok) {
                $rel = $d->stok_inventory_dombak()->where('stok_inventory_id', $stok->stok_inventory_id)->first();
                return [
                    'id' => $d->dombak_id,
                    'name' => $d->nama_dombak,
                    'stok' => $rel ? $rel->stok : '0.00',
                ];
            })->values()->all(),
            'warning' => 'Pastikan anda mengetahui apa yang anda perbuat. Merubah initial stok bisa mengubah perhitungan laporan stok ataupun keuangan.',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StokInventory $stok)
    {
        $request->validate([
            'stok' => ['required'],
            'produk_id' => ['required']
        ]);

        for ($i = 0; $i < count($request->stok); $i++) {
            if ($request->stok[$i] == 0) {
                if ($stok->stok_inventory_dombak()->where('dombak_id', $request->dombak_id[$i])->count() > 0) {
                    $stok->stok_inventory_dombak()->where('dombak_id', $request->dombak_id[$i])->delete();
                }
            }
        }

        $totalStok = 0;
        for ($i = 0; $i < count($request->stok); $i++) {
            if ($request->stok[$i] > 0) {
                $totalStok = $totalStok + $request->stok[$i];
            }
        }
        $awal = $stok->stok;
        $type = TypeDokumenStok::where('type_movement', 'Commit Change From User')->first();
        $jamKerja = JamKerja::first();

        $stok->update($this->stokInventoryData($totalStok));

        for ($i = 0; $i < count($request->stok); $i++) {
            if ($request->stok[$i] > 0) {
                if ($stok->stok_inventory_dombak()->where('dombak_id', $request->dombak_id[$i])->count() > 0) {
                    $stok->stok_inventory_dombak()->where('dombak_id', $request->dombak_id[$i])->update(['stok' => $request->stok[$i]]);
                } else {
                    if (is_null($stok->stok_inventory_dombak()->where('dombak_id', $request->dombak_id[$i])->where('stok', $request->stok[$i])->first())) {
                        $stok->stok_inventory_dombak()->create([
                            'dombak_id' => $request->dombak_id[$i],
                            'stok' => $request->stok[$i]
                        ]);
                    }
                }

                $tgl = date('Y-m-d H:i:s');
                if (is_null($stok->dokumen_stok()->where('member_id', Auth::user()->member_id)->where('jam_kerja_id', $jamKerja->jam_kerja_id)->where('type_dokumen_stok_id', $type->type_dokumen_stok_id)->where('dombak_id', $request->dombak_id[$i])->where('tanggal', $tgl)->where('stok_awal', $awal)->where('stok_akhir', $request->stok[$i])->where('is_confirmation', 1)->first()));
                $stok->dokumen_stok()->create([
                    'member_id' => Auth::user()->member_id,
                    'jam_kerja_id' => $jamKerja->jam_kerja_id,
                    'type_dokumen_stok_id' => $type->type_dokumen_stok_id,
                    'dombak_id' => $request->dombak_id[$i],
                    'tanggal' => $tgl,
                    'stok_awal' => $awal,
                    'stok_akhir' => $request->stok[$i],
                    'is_confirmation' => 1
                ]);
            }
        }

        return redirect('/stok/monitoring/' . $stok->stok_inventory_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Stok Inventory sudah diubah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StokInventory $stok)
    {
        if($stok->stok_inventory_dombak()->count() > 0) {
            return redirect('/stok/monitoring')->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Gagal!</strong> Data Stok Inventory masih digunakan oleh Dombak.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
        $stok->stok_inventory_dombak()->delete();
        $stok->delete();

        return redirect('/stok/monitoring')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Dombak sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function stokInventoryData($stok = 0)
    {
        return [
            'stok' => $stok
        ];
    }

    public function dombak_index(Request $request, StokInventory $stok)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $stok->stok_inventory_dombak()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('dombak_id', function ($row) {
                    $actionBtn = $row->dombak()->first()->dombak_id;
                    return $actionBtn;
                })
                ->addColumn('nama_dombak', function ($row) {
                    $actionBtn = $row->dombak()->first()->nama_dombak;
                    return $actionBtn;
                })
                ->addColumn('stok', function ($row) {
                    $actionBtn = number_format($row->stok != null ? $row->stok : 0, 6, ".", ",");
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<form action="' . route('monitoring.dombak.delete', [$row->stok_inventory()->first()->stok_inventory_id, $row->dombak()->first()->dombak_id]) . '" method="POST"><input type="hidden" name="_method" value="delete">' . csrf_field() . '<button class="edit btn btn-danger btn-sm">Hapus</button></form>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Dombak Monitoring Stok',
            'breadcrumbs' => [
                ['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')],
                ['label' => 'Monitoring Stok', 'url' => route('monitoring.index')],
                ['label' => 'Detail', 'url' => route('monitoring.show', $stok->stok_inventory_id)],
            ],
            'createUrl' => route('monitoring.dombak.create', $stok->stok_inventory_id),
            'headerActions' => [
                ['label' => 'Kembali', 'url' => route('monitoring.show', $stok->stok_inventory_id), 'color' => 'light', 'icon' => 'arrow-left'],
            ],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Id Dombak', 'data' => 'dombak_id'],
                    ['label' => 'Nama Dombak', 'data' => 'nama_dombak'],
                    ['label' => 'Initial Stok', 'data' => 'stok'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function dombak_create(Request $request, StokInventory $stok)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Dombak::select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($stok) {
                    $actionBtn = $row->stok_inventory_dombak()->where('stok_inventory_dombak.stok_inventory_id', $stok->stok_inventory_id)->count() > 0 ? 'Sudah'  : '<form action="' . route('monitoring.dombak.store', [$stok->stok_inventory_id, $row->dombak_id]) . '" method="POST">' . csrf_field() . '<button class="edit btn btn-success btn-sm" title="Pilih" type="submit">Pilih</button></form>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Tambah Dombak Monitoring Stok',
            'breadcrumbs' => [
                ['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')],
                ['label' => 'Monitoring Stok', 'url' => route('monitoring.index')],
                ['label' => 'Detail', 'url' => route('monitoring.show', $stok->stok_inventory_id)],
                ['label' => 'Dombak', 'url' => route('monitoring.dombak.index', $stok->stok_inventory_id)],
            ],
            'headerActions' => [
                ['label' => 'Kembali', 'url' => route('monitoring.dombak.index', $stok->stok_inventory_id), 'color' => 'light', 'icon' => 'arrow-left'],
            ],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Nama Dombak', 'data' => 'nama_dombak'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function dombak_store(StokInventory $stok, Dombak $dombak)
    {
        $cek = DB::table('stok_inventory_dombak')->where(
            'dombak_id',
            $dombak->dombak_id
        )->where('stok_inventory_id', $stok->stok_inventory_id)->first();

        if (is_null($cek)) {
            DB::table('stok_inventory_dombak')->insert([
                'dombak_id' => $dombak->dombak_id,
                'stok_inventory_id' => $stok->stok_inventory_id
            ]);
            return redirect('/stok/monitoring/' . $stok->stok_inventory_id . '/dombak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Relasi Dombak sudah ditambahkan ke Produk.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
        return redirect('/stok/monitoring/' . $stok->stok_inventory_id . '/dombak')->with('msg', '<div class="alert alert-info alert-dismissible fade show" role="alert"><strong>Tidak ada Perubahan!</strong> Relasi Dombak sebelumnya sudah ditambahkan ke Produk.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function dombak_destroy(StokInventory $stok, Dombak $dombak)
    {
        DB::table('stok_inventory_dombak')->where(
            'dombak_id',
            $dombak->dombak_id
        )->where('stok_inventory_id', $stok->stok_inventory_id)->delete();

        return redirect('/stok/monitoring/' . $stok->stok_inventory_id . '/dombak')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Relasi Dombak sudah dihapus dari Produk.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function show_riwayat(Request $request, StokInventory $stok)
    {
        $dombak = $stok->stok_inventory_dombak()->select('dombak.dombak_id')->addSelect('dombak.nama_dombak')->join('dombak', 'dombak.dombak_id', 'stok_inventory_dombak.dombak_id')->get();
        $typeDokumenStok = TypeDokumenStok::all();
        $waktuMulai = $request->has('waktu_mulai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_mulai) : Carbon::now()->firstOfMonth();
        $waktuSelesai = $request->has('waktu_selesai') ? Carbon::createFromFormat('Y-m-d', $request->waktu_selesai) : Carbon::now()->endOfMonth();
        $dombakSelected = $request->has('dombak') ? $request->dombak : 'all';
        $typeDokumenStokSelected = $request->has('type_dokumen_stok') ? $request->type_dokumen_stok : 'all';
        if ($waktuMulai->gt($waktuSelesai)) {
            $temp = $waktuMulai;
            $waktuMulai = $waktuSelesai;
            $waktuSelesai = $temp;
        }
        $waktuMulai = $waktuMulai->format('Y-m-d');
        $waktuSelesai = $waktuSelesai->format('Y-m-d');

        if ($request->ajax() && ! $request->inertia()) {
            $data = $stok->dokumen_stok()
                ->select([
                    'stok_inventory.stok_inventory_id',
                    'dokumen_stok.dokumen_stok_id',
                    'dokumen_stok.tanggal',
                    'dokumen_stok.stok_awal',
                    'dokumen_stok.stok_akhir',
                    'type_dokumen_stok.type_movement',
                    'dombak.nama_dombak',
                    'jam_kerja.nama_jam_kerja',
                    'jam_kerja.jam_datang_in',
                    DB::raw("STRING_AGG(losses.simbol, ',') as loss_symbols"),
                    DB::raw('SUM(losses.jumlah) as total_loss')
                ])
                ->join('stok_inventory', 'stok_inventory.stok_inventory_id', 'dokumen_stok.stok_inventory_id')
                ->join('type_dokumen_stok', 'type_dokumen_stok.type_dokumen_stok_id', 'dokumen_stok.type_dokumen_stok_id')
                ->join('dombak', 'dombak.dombak_id', 'dokumen_stok.dombak_id')
                ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'dokumen_stok.jam_kerja_id')
                ->leftJoin('losses', 'losses.dokumen_stok_id', 'dokumen_stok.dokumen_stok_id')
                ->where('dokumen_stok.is_confirmation', 1)
                ->whereBetween('dokumen_stok.tanggal', [$waktuMulai, $waktuSelesai])
                ->when($dombakSelected !== 'all', function ($query) use ($dombakSelected) {
                    return $query->where('dokumen_stok.dombak_id', $dombakSelected);
                })
                ->when($typeDokumenStokSelected !== 'all', function ($query) use ($typeDokumenStokSelected) {
                    return $query->where('dokumen_stok.type_dokumen_stok_id', $typeDokumenStokSelected);
                })
                ->groupBy([
                    'dokumen_stok.dokumen_stok_id',
                    'stok_inventory.stok_inventory_id',
                    'dokumen_stok.tanggal',
                    'dokumen_stok.stok_awal',
                    'dokumen_stok.stok_akhir',
                    'type_dokumen_stok.type_movement',
                    'dombak.nama_dombak',
                    'jam_kerja.nama_jam_kerja',
                    'jam_kerja.jam_datang_in'
                ])
                ->orderBy('tanggal', 'desc')
                ->orderBy('jam_datang_in', 'desc')
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    $actionBtn = Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                    return $actionBtn . ' ' . $row->nama_jam_kerja;
                })
                ->addColumn('type_movement', function ($row) {
                    return $row->type_movement;
                })
                ->addColumn('nama_dombak', function ($row) {
                    return $row->nama_dombak;
                })
                ->addColumn('stok_awal', function ($row) {
                    return number_format($row->stok_awal, 6, ".", ",");
                })
                ->addColumn('stok_akhir', function ($row) {
                    return number_format($row->stok_akhir, 6, ".", ',');
                })
                ->addColumn('total_loss', function ($row) {
                    $symbols = array_filter(explode(',', $row->loss_symbols ?? ''));
                    $totalLoss = (float) ($row->total_loss ?? 0);
                    if ($totalLoss === 0.0) {
                        return number_format(0, 6, ".", ',');
                    }
                    return (in_array('lebih', $symbols, true) ? '+' : '-') . number_format(abs($totalLoss), 6, ".", ',');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('monitoring.show_detail_riwayat', [$row->stok_inventory_id, $row->dokumen_stok_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Riwayat Monitoring Stok',
            'breadcrumbs' => [
                ['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')],
                ['label' => 'Monitoring Stok', 'url' => route('monitoring.index')],
                ['label' => 'Detail', 'url' => route('monitoring.show', $stok->stok_inventory_id)],
            ],
            'headerActions' => [
                ['label' => 'Kembali', 'url' => route('monitoring.show', $stok->stok_inventory_id), 'color' => 'light', 'icon' => 'arrow-left'],
            ],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'selectFilters' => [
                [
                    'name' => 'type_dokumen_stok',
                    'label' => 'Tipe Dokumen',
                    'value' => $typeDokumenStokSelected,
                    'options' => array_merge(
                        [['value' => 'all', 'label' => 'Semua']],
                        $typeDokumenStok->map(fn ($type) => ['value' => $type->type_dokumen_stok_id, 'label' => $type->type_movement])->values()->all()
                    ),
                ],
                [
                    'name' => 'dombak',
                    'label' => 'Dombak',
                    'value' => $dombakSelected,
                    'options' => array_merge(
                        [['value' => 'all', 'label' => 'Semua']],
                        $dombak->map(fn ($item) => ['value' => $item->dombak_id, 'label' => $item->nama_dombak])->values()->all()
                    ),
                ],
            ],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Tipe Dokumen', 'data' => 'type_movement'],
                    ['label' => 'Nama Dombak', 'data' => 'nama_dombak'],
                    ['label' => 'Stok Awal', 'data' => 'stok_awal'],
                    ['label' => 'Stok Akhir', 'data' => 'stok_akhir'],
                    ['label' => 'Loss/Profit', 'data' => 'total_loss'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function show_detail_riwayat(StokInventory $stok, DokumenStok $dokumenStok)
    {
        $stok_akhir_teori = $dokumenStok->stok_akhir ?? 0;
        $stok_akhir_pl = 0;
        if (!is_null($dokumenStok->losses()->first())) {
            $stok_akhir_teori = ($dokumenStok->losses()->first()->simbol == 'lebih') ? $stok_akhir_teori + $dokumenStok->losses()->first()->jumlah : $stok_akhir_teori - $dokumenStok->losses()->first()->jumlah;

            $stok_akhir_pl = ($dokumenStok->losses()->first()->simbol == 'lebih') ? $dokumenStok->losses()->first()->jumlah : $dokumenStok->losses()->first()->jumlah * -1;
        }
        $stokHitung = [
            'stok_awal' => number_format($dokumenStok->stok_awal, 6, ".", ","),
            'stok_akhir' => number_format($stok_akhir_teori, 6, ".", ","),
            'stok_akhir_teoritis' => number_format($dokumenStok->stok_akhir, 6, ".", ","),
            'stok_akhir_pl' => number_format($stok_akhir_pl, 6, ".", ","),
        ];
        return Inertia::render('Show/Index', [
            'title' => 'Detail Riwayat Monitoring Stok',
            'breadcrumbs' => [
                ['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')],
                ['label' => 'Monitoring Stok', 'url' => route('monitoring.index')],
                ['label' => 'Detail', 'url' => route('monitoring.show', $stok->stok_inventory_id)],
                ['label' => 'Riwayat', 'url' => route('monitoring.show_riwayat', $stok->stok_inventory_id)],
            ],
            'backUrl' => route('monitoring.show_riwayat', $stok->stok_inventory_id),
            'fields' => [
                ['label' => 'Tipe Dokumen', 'value' => optional($dokumenStok->type_dokumen_stok()->first())->type_movement],
                ['label' => 'Produk', 'value' => optional($stok->produk()->first())->nama_produk],
                ['label' => 'Dombak', 'value' => optional($dokumenStok->dombak()->first())->nama_dombak],
                ['label' => 'Tanggal', 'value' => Carbon::createFromFormat('Y-m-d', $dokumenStok->tanggal)->format('d F Y')],
                ['label' => 'Jam Kerja', 'value' => optional($dokumenStok->jam_kerja()->first())->nama_jam_kerja],
                ['label' => 'Member', 'value' => optional(optional($dokumenStok->member()->first())->ktp()->first())->nama],
            ],
            'tables' => [[
                'title' => 'Stok Hitung',
                'columns' => ['Informasi', 'Besaran'],
                'rows' => [
                    ['Stok Awal', $stokHitung['stok_awal']],
                    ['Stok Akhir', $stokHitung['stok_akhir']],
                    ['Stok Akhir Teoritis', $stokHitung['stok_akhir_teoritis']],
                    ['Stok Akhir P/L', $stokHitung['stok_akhir_pl']],
                ],
            ]],
        ]);
    }
}
