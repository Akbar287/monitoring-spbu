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
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class RencanaPembelianController extends Controller
{
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
        return Inertia::render('Table/Index', [
            'title' => 'Perencanaan',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')]],
            'createUrl' => route('perencanaan.create'),
            'headerActions' => [['label' => 'Ditolak', 'url' => route('perencanaan.ditolak'), 'color' => 'danger', 'icon' => 'ban']],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Kode', 'data' => 'kode_pembelian'],
                    ['label' => 'Tanggal', 'data' => 'created_at'],
                    ['label' => 'Diajukan Oleh', 'data' => 'member_id'],
                    ['label' => 'Total Produk', 'data' => 'produk_id'],
                    ['label' => 'Status', 'data' => 'is_confirmation', 'raw' => true],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    public function index_ditolak(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
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
        return Inertia::render('Table/Index', [
            'title' => 'Perencanaan Ditolak',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')]],
            'headerActions' => [['label' => 'Perencanaan', 'url' => route('perencanaan.index'), 'color' => 'primary', 'icon' => 'arrow-left']],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Kode', 'data' => 'kode_pembelian'],
                    ['label' => 'Tanggal', 'data' => 'created_at'],
                    ['label' => 'Diajukan Oleh', 'data' => 'member_id'],
                    ['label' => 'Total Produk', 'data' => 'produk_id'],
                    ['label' => 'Status', 'data' => 'is_confirmation', 'raw' => true],
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
        $spbu = Spbu::all();
        $kode = $this->generateKodePembelian();

        return Inertia::render('Form/Index', [
            'title' => 'Tambah Perencanaan',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Perencanaan', 'url' => route('perencanaan.index')]],
            'backUrl' => route('perencanaan.index'),
            'method' => 'post',
            'action' => route('perencanaan.store'),
            'fields' => [
                ['name' => 'kode_pembelian', 'label' => 'Kode', 'type' => 'hidden', 'value' => $kode],
                ['name' => 'spbu_id', 'label' => 'SPBU', 'type' => 'select', 'value' => null,
                    'options' => $spbu->map(fn ($s) => ['value' => $s->spbu_id, 'label' => $s->nama_spbu . ' - ' . $s->nomor_spbu])->values()->all()],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null],
            ],
        ]);
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
        $statusKonfirmasi = $rencana->is_confirmation
            ? ($rencana->is_denied ? 'Ditolak' : 'Terkonfirmasi')
            : 'Belum Terkonfirmasi';
        $fields = [
            ['label' => 'Id Rencana Pembelian', 'value' => $rencana->rencana_pembelian_id],
            ['label' => 'Kode', 'value' => $rencana->kode_pembelian],
            ['label' => 'SPBU', 'value' => optional($rencana->spbu()->first())->nama_spbu],
            ['label' => 'Keterangan', 'value' => $rencana->keterangan],
            ['label' => 'Jenis Konfirmasi', 'value' => $statusKonfirmasi],
        ];
        if ($rencana->is_confirmation && $rencana->is_denied) {
            $fields[] = ['label' => 'Catatan', 'value' => $rencana->keterangan_denied];
        }

        $actions = [
            ['label' => 'Detail Produk', 'url' => route('perencanaan.detail.index', $rencana->rencana_pembelian_id), 'color' => 'warning', 'icon' => 'box'],
            ['label' => $rencana->is_confirmation ? 'Konfirmasi Ulang' : 'Konfirmasi', 'url' => route('perencanaan.konfirmasi', $rencana->rencana_pembelian_id), 'color' => 'info', 'icon' => 'check'],
        ];
        if (! $rencana->is_confirmation) {
            $actions[] = ['label' => 'Ubah', 'url' => route('perencanaan.edit', $rencana->rencana_pembelian_id), 'color' => 'success', 'icon' => 'pen'];
        }

        return Inertia::render('Show/Index', [
            'title' => 'Detail Perencanaan',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Perencanaan', 'url' => route('perencanaan.index')]],
            'backUrl' => route('perencanaan.index'),
            'fields' => $fields,
            'actions' => $actions,
            'deleteUrl' => $rencana->is_confirmation ? null : route('perencanaan.destroy', $rencana->rencana_pembelian_id),
            'deleteConfirm' => "Rencana Pembelian akan dihapus!\n Lanjutkan?",
        ]);
    }

    public function show_ditolak(RencanaPembelian $rencana)
    {
        $statusKonfirmasi = $rencana->is_confirmation
            ? ($rencana->is_denied ? 'Ditolak' : 'Terkonfirmasi')
            : 'Belum Terkonfirmasi';
        $fields = [
            ['label' => 'Id Rencana Pembelian', 'value' => $rencana->rencana_pembelian_id],
            ['label' => 'Kode', 'value' => $rencana->kode_pembelian],
            ['label' => 'SPBU', 'value' => optional($rencana->spbu()->first())->nama_spbu],
            ['label' => 'Keterangan', 'value' => $rencana->keterangan],
            ['label' => 'Jenis Konfirmasi', 'value' => $statusKonfirmasi],
        ];
        if ($rencana->is_confirmation && $rencana->is_denied) {
            $fields[] = ['label' => 'Catatan', 'value' => $rencana->keterangan_denied];
        }

        return Inertia::render('Show/Index', [
            'title' => 'Detail Perencanaan Ditolak',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Perencanaan', 'url' => route('perencanaan.index')]],
            'backUrl' => route('perencanaan.ditolak'),
            'fields' => $fields,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaPembelian $rencana)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Perencanaan',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Perencanaan', 'url' => route('perencanaan.index')]],
            'backUrl' => route('perencanaan.show', $rencana->rencana_pembelian_id),
            'method' => 'put',
            'action' => route('perencanaan.update', $rencana->rencana_pembelian_id),
            'fields' => [
                ['name' => 'nama_spbu', 'label' => 'SPBU', 'type' => 'text', 'readonly' => true,
                    'value' => optional($rencana->spbu()->first())->nama_spbu],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => $rencana->keterangan],
            ],
        ]);
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
        return Inertia::render('Form/Index', [
            'title' => 'Konfirmasi Perencanaan',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Perencanaan', 'url' => route('perencanaan.index')], ['label' => 'Detail', 'url' => route('perencanaan.show', $rencana->rencana_pembelian_id)]],
            'backUrl' => route('perencanaan.show', $rencana->rencana_pembelian_id),
            'method' => 'put',
            'action' => route('perencanaan.konfirmasi.update', $rencana->rencana_pembelian_id),
            'fields' => [
                ['name' => 'jenis', 'label' => 'Jenis Konfirmasi', 'type' => 'select', 'value' => 'false',
                    'options' => [['value' => 'true', 'label' => 'Terima — lanjut ke Pembelian'], ['value' => 'false', 'label' => 'Tolak']]],
                ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'value' => null,
                    'hint' => 'Wajib diisi catatan bila perencanaan ditolak.'],
            ],
        ]);
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

        if ($rencana->pajak_pembelian()->count() > 1) {
            $rencana->pajak_pembelian()->first()->delete();
        }

        return redirect('/procurement/perencanaan/' . $rencana->rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Rencana Pembelian sudah terkonfirmasi.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
