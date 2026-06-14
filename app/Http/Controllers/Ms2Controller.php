<?php

namespace App\Http\Controllers;

use App\Models\DetailRencanaPembelian;
use App\Models\DetailRencanaPembelianMs2;
use App\Models\JamKerja;
use App\Models\Ms2;
use App\Models\Pengiriman;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class Ms2Controller extends Controller
{
    public function index_produk(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Produk::where('is_aktif', 1)->orderBy('nama_produk', 'asc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('stok_kl', function ($row) {
                    return number_format($row->detail_rencana_pembelian()->selectRaw('SUM("jumlah") as stok')->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'detail_rencana_pembelian.rencana_pembelian_id')->join('status_purchase', 'status_purchase.status_purchase_id', 'rencana_pembelian.status_purchase_id')->where('status_purchase.nama_status', 'MS2')->where('detail_rencana_pembelian.ms2', 0)->where('detail_rencana_pembelian.delivery', 0)->first()->stok / 8000 ?? 0, 2, ".", ",");
                })
                ->addColumn('total_pembelian', function ($row) {
                    return $row->detail_rencana_pembelian()
                        ->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'detail_rencana_pembelian.rencana_pembelian_id')
                        ->join('status_purchase', 'status_purchase.status_purchase_id', 'rencana_pembelian.status_purchase_id')
                        ->where('status_purchase.nama_status', 'MS2')
                        ->where('detail_rencana_pembelian.ms2', 0)
                        ->where('detail_rencana_pembelian.delivery', 0)
                        ->count();
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('ms2.produk.show', $row->produk_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'MS2',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')]],
            'headerActions' => [['label' => 'MS2 Pengiriman', 'url' => route('ms2.pengiriman'), 'color' => 'info', 'icon' => 'truck']],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Produk', 'data' => 'nama_produk'],
                    ['label' => 'Stok (Kl)', 'data' => 'stok_kl'],
                    ['label' => 'Total Pembelian', 'data' => 'total_pembelian'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }
    public function show_produk(Request $request, Produk $produk)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $produk->detail_rencana_pembelian()
                ->select('rencana_pembelian.kode_pembelian')
                ->addSelect('rencana_pembelian.tanggal_pembelian')
                ->addSelect('detail_rencana_pembelian.jumlah')
                ->addSelect('detail_rencana_pembelian.detail_rencana_pembelian_id')
                ->addSelect('detail_rencana_pembelian.produk_id')
                ->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'detail_rencana_pembelian.rencana_pembelian_id')
                ->join('status_purchase', 'status_purchase.status_purchase_id', 'rencana_pembelian.status_purchase_id')
                ->where('status_purchase.nama_status', 'MS2')
                ->where('detail_rencana_pembelian.ms2', 0)
                ->where('detail_rencana_pembelian.delivery', 0)
                ->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_pembelian', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal_pembelian)->format('d F Y') ?? 0;
                })
                ->addColumn('jumlah', function ($row) {
                    return number_format($row->jumlah, 6, ".", ",") ?? 0;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('ms2.detail.show', [$row->produk_id, $row->detail_rencana_pembelian_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'MS2 — ' . $produk->nama_produk,
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'MS2', 'url' => route('ms2.produk.index')]],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal Pembelian', 'data' => 'tanggal_pembelian'],
                    ['label' => 'Kode Pembelian', 'data' => 'kode_pembelian'],
                    ['label' => 'Stok (Liter)', 'data' => 'jumlah'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }
    public function show_detail(Request $request, Produk $produk, DetailRencanaPembelian $detail)
    {
        $rencana = $detail->rencana_pembelian()->first();
        $pajak = $rencana ? $rencana->pajak_pembelian()->first() : null;

        $fields = [
            ['label' => 'Produk', 'value' => optional($detail->produk()->first())->nama_produk],
            ['label' => 'Jumlah', 'value' => number_format($detail->jumlah, 6, '.', ',') . ' ' . $detail->satuan_jumlah],
            ['label' => 'Harga', 'value' => 'Rp ' . number_format($detail->harga, 6, '.', ',')],
            ['label' => 'SPBU', 'value' => $rencana ? optional($rencana->spbu()->first())->nama_spbu : null],
            ['label' => 'Kode Pembelian', 'value' => optional($rencana)->kode_pembelian],
            ['label' => 'Keterangan', 'value' => optional($rencana)->keterangan],
            ['label' => 'Grand Total', 'value' => $rencana ? 'Rp ' . number_format($rencana->grand_total, 6, '.', ',') : null],
        ];
        if ($pajak) {
            $fields[] = ['label' => 'Net Price', 'value' => 'Rp ' . number_format($pajak->net_price, 6, '.', ',')];
            $fields[] = ['label' => 'PPn', 'value' => 'Rp ' . number_format($pajak->ppn, 6, '.', ',')];
            $fields[] = ['label' => 'PPh', 'value' => 'Rp ' . number_format($pajak->pph, 6, '.', ',')];
            $fields[] = ['label' => 'PPBKB', 'value' => 'Rp ' . number_format($pajak->ppbkb, 6, '.', ',')];
            $fields[] = ['label' => 'Total Amount', 'value' => 'Rp ' . number_format($pajak->total_amount, 6, '.', ',')];
        }

        $bayar = $rencana ? $rencana->pembayaran()->get()->map(fn ($p) => [
            $p->tanggal,
            optional(optional($p->member()->first())->ktp()->first())->nama,
            'Rp ' . number_format($p->total_setoran, 6, '.', ','),
        ])->values()->all() : [];

        return Inertia::render('Show/Index', [
            'title' => 'Detail MS2 — ' . optional($detail->produk()->first())->nama_produk,
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'MS2', 'url' => route('ms2.produk.index')], ['label' => $produk->nama_produk, 'url' => route('ms2.produk.show', $produk->produk_id)]],
            'backUrl' => route('ms2.produk.show', $produk->produk_id),
            'fields' => $fields,
            'tables' => $bayar ? [[
                'title' => 'Pembayaran',
                'columns' => ['Tanggal', 'Penyetor', 'Total Setoran'],
                'rows' => $bayar,
            ]] : [],
        ]);
    }

    public function index_pengiriman(Request $request)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = Ms2::where('is_done', 0)->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y') ?? 0;
                })
                ->addColumn('produk', function ($row) {
                    return $row->detail_rencana_pembelian_ms2()->count() . ' produk';
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('ms2.pengiriman.show', $row->ms2_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'MS2 Pengiriman',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')]],
            'createUrl' => route('ms2.pengiriman.create'),
            'headerActions' => [['label' => 'Produk MS2', 'url' => route('ms2.produk.index'), 'color' => 'info', 'icon' => 'box']],
            'table' => [
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Produk', 'data' => 'produk'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }
    public function create_pengiriman(Request $request)
    {
        $produk = Produk::where('is_aktif', 1)->get();
        $shift = JamKerja::orderBy('urutan', 'asc')->get();
        $produkGroups = $produk->map(function ($p) {
            $items = $p->detail_rencana_pembelian()
                ->select('detail_rencana_pembelian.detail_rencana_pembelian_id', 'detail_rencana_pembelian.jumlah', 'detail_rencana_pembelian.satuan_jumlah', 'rencana_pembelian.kode_pembelian', 'rencana_pembelian.tanggal_pembelian', 'detail_rencana_file.file_lo_id')
                ->join('rencana_pembelian', 'rencana_pembelian.rencana_pembelian_id', 'detail_rencana_pembelian.rencana_pembelian_id')
                ->leftJoin('detail_rencana_file', 'detail_rencana_file.detail_rencana_pembelian_id', 'detail_rencana_pembelian.detail_rencana_pembelian_id')
                ->where('detail_rencana_file.file_lo_id', null)
                ->where('ms2', false)
                ->where('delivery', false)
                ->orderBy('rencana_pembelian.tanggal_pembelian', 'asc')
                ->get()
                ->map(fn ($pp) => [
                    'id' => $pp->detail_rencana_pembelian_id,
                    'label' => number_format($pp->jumlah, 2, '.', ',') . ' ' . $pp->satuan_jumlah . ' (' . $pp->kode_pembelian . ' | ' . $pp->tanggal_pembelian . ')',
                ])->values()->all();

            return ['nama' => $p->nama_produk, 'items' => $items];
        })->values()->all();

        return Inertia::render('Ms2/CreatePengiriman', [
            'title' => 'Ajukan MS2',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'MS2 Pengiriman', 'url' => route('ms2.pengiriman')]],
            'backUrl' => route('ms2.pengiriman'),
            'action' => route('ms2.pengiriman.store'),
            'tanggalDefault' => date('Y-m-d'),
            'shiftOptions' => $shift->map(fn ($s) => ['value' => $s->jam_kerja_id, 'label' => $s->nama_jam_kerja])->values()->all(),
            'produkGroups' => $produkGroups,
        ]);
    }
    public function store_pengiriman(Request $request)
    {
        $request->validate([
            'tanggal' => ['required']
        ]);

        if (count($request->detail_rencana_pembelian_id) > 0) {
            $kode = $this->generateKodeSms($request->detail_rencana_pembelian_id, $request->detail_rencana_pembelian_id_shift);
            $loop = true;
            do {
                $ms2 = Ms2::where('tanggal', $request->tanggal)->where('member_id', Auth::user()->member_id)->where('is_done', 0)->where('kode_sms', $kode)->count();

                if ($ms2 == 0) {
                    $ms2 = new Ms2();
                    $ms2->tanggal = $request->tanggal;
                    $ms2->member_id = Auth::user()->member_id;
                    $ms2->is_done = 0;
                    $ms2->kode_sms = $kode;
                    $ms2->save();
                }
                if ($ms2 == 1) {
                    $loop = false;
                }

                if ($ms2 > 1) {
                    $ms2->delete();
                }
            } while ($loop);

            $ms2 = Ms2::where('tanggal', $request->tanggal)->where('member_id', Auth::user()->member_id)->where('is_done', 0)->where('kode_sms', $kode)->first();

            foreach ($request->detail_rencana_pembelian_id as $key => $drp) {
                DetailRencanaPembelian::where('detail_rencana_pembelian_id', $drp)->update([
                    'ms2' => 1
                ]);

                if (is_null(DetailRencanaPembelianMs2::where('ms2_id', $ms2->ms2_id)->where('detail_rencana_pembelian_id', $drp)->where('is_delivery', 0)->first())) {
                    DetailRencanaPembelianMs2::create([
                        'ms2_id' => $ms2->ms2_id,
                        'detail_rencana_pembelian_id' => $drp,
                        'jam_kerja_id' => $request->detail_rencana_pembelian_id_shift[$key],
                        'is_delivery' => 0
                    ]);
                }
            }

            return redirect('/procurement/ms2/pengiriman/' . $ms2->ms2_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pengiriman sudah ditambah.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        } else {
            return redirect('/procurement/ms2/pengiriman/tambah')->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Gagal!</strong> Anda Perlu Memilih Produk.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }

    public function show_pengiriman(Request $request, Ms2 $ms2)
    {
        $rows = $ms2->detail_rencana_pembelian_ms2()
            ->select('detail_rencana_pembelian.jumlah', 'detail_rencana_pembelian.satuan_jumlah', 'rencana_pembelian.kode_pembelian', 'produk.nama_produk', 'detail_rencana_pembelian_ms2.is_delivery')
            ->join('detail_rencana_pembelian', 'detail_rencana_pembelian.detail_rencana_pembelian_id', 'detail_rencana_pembelian_ms2.detail_rencana_pembelian_id')
            ->join('produk', 'produk.produk_id', 'detail_rencana_pembelian.produk_id')
            ->join('rencana_pembelian', 'detail_rencana_pembelian.rencana_pembelian_id', 'rencana_pembelian.rencana_pembelian_id')
            ->get();

        $tableRows = $rows->values()->map(fn ($p, $i) => [
            $ms2->is_done ? ($p->is_delivery ? 'OK' : 'NO') : ($i + 1),
            $p->nama_produk,
            number_format($p->jumlah, 2, '.', ',') . ' ' . $p->satuan_jumlah,
            $p->kode_pembelian,
        ])->all();

        $actions = [];
        $notice = null;
        if ($ms2->is_done) {
            $pengiriman = $ms2->pengiriman()->first();
            $notice = ['text' => 'MS2 ini sudah di pengiriman. Gunakan tombol "Ke Halaman Pengiriman".', 'color' => 'success'];
            if ($pengiriman) {
                $actions[] = ['label' => 'Ke Halaman Pengiriman', 'url' => route('pengiriman.show', $pengiriman->pengiriman_id), 'color' => 'info', 'icon' => 'truck'];
            }
        } else {
            $actions[] = ['label' => 'Lanjutkan ke Pengiriman', 'url' => route('ms2.pengiriman.konfirm_pengiriman', $ms2->ms2_id), 'color' => 'success', 'icon' => 'arrow-right'];
        }

        return Inertia::render('Show/Index', [
            'title' => 'Detail MS2',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'MS2 Pengiriman', 'url' => route('ms2.pengiriman')]],
            'backUrl' => route('ms2.pengiriman'),
            'notice' => $notice,
            'fields' => [
                ['label' => 'Yang Menerima', 'value' => optional(optional($ms2->member()->first())->ktp()->first())->nama],
                ['label' => 'Tanggal', 'value' => \Carbon\Carbon::createFromFormat('Y-m-d', $ms2->tanggal)->format('d F Y')],
                ['label' => 'Kode SMS', 'value' => $ms2->kode_sms],
            ],
            'tables' => [[
                'title' => 'Produk',
                'columns' => ['No', 'Nama Produk', 'Kuantitas', 'Kode Pembelian'],
                'rows' => $tableRows,
            ]],
            'actions' => $actions,
            'deleteUrl' => $ms2->is_done ? null : route('ms2.pengiriman.delete', $ms2->ms2_id),
            'deleteConfirm' => "Ms2 akan dihapus!\n Lanjutkan?",
        ]);
    }

    public function konfirm_pengiriman(Request $request, Ms2 $ms2)
    {
        $items = $ms2->detail_rencana_pembelian_ms2()
            ->select('detail_rencana_pembelian.detail_rencana_pembelian_id', 'detail_rencana_pembelian.jumlah', 'detail_rencana_pembelian.satuan_jumlah', 'rencana_pembelian.kode_pembelian', 'produk.nama_produk')
            ->join('detail_rencana_pembelian', 'detail_rencana_pembelian.detail_rencana_pembelian_id', 'detail_rencana_pembelian_ms2.detail_rencana_pembelian_id')
            ->join('produk', 'produk.produk_id', 'detail_rencana_pembelian.produk_id')
            ->join('rencana_pembelian', 'detail_rencana_pembelian.rencana_pembelian_id', 'rencana_pembelian.rencana_pembelian_id')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->detail_rencana_pembelian_id,
                'nama_produk' => $p->nama_produk,
                'kuantitas' => number_format($p->jumlah, 2, '.', ',') . ' ' . $p->satuan_jumlah,
                'kode_pembelian' => $p->kode_pembelian,
            ])->values()->all();

        return Inertia::render('Ms2/Konfirm', [
            'title' => 'Konfirmasi Pengiriman MS2',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'MS2 Pengiriman', 'url' => route('ms2.pengiriman')], ['label' => $ms2->kode_sms, 'url' => route('ms2.pengiriman.show', $ms2->ms2_id)]],
            'backUrl' => route('ms2.pengiriman.show', $ms2->ms2_id),
            'action' => route('ms2.pengiriman.update_pengiriman_to_delivery', $ms2->ms2_id),
            'member' => optional(optional($ms2->member()->first())->ktp()->first())->nama,
            'tanggal' => $ms2->tanggal,
            'items' => $items,
        ]);
    }

    public function destroy_pengiriman(Request $request, Ms2 $ms2)
    {
        foreach ($ms2->detail_rencana_pembelian_ms2()->get() as $dr) {
            $dr->detail_rencana_pembelian()->first()->update([
                'ms2' => 0
            ]);
        }
        $ms2->delete();

        return redirect('/procurement/ms2/pengiriman')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pengiriman sudah dihapus.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function update_pengiriman_to_delivery(Request $request, Ms2 $ms2)
    {
        if (!is_null($request->detail_rencana_pembelian_id)) {
            foreach ($ms2->detail_rencana_pembelian_ms2()->get() as $drms) {
                if (in_array($drms->detail_rencana_pembelian_id, $request->detail_rencana_pembelian_id)) {
                    DetailRencanaPembelian::where('detail_rencana_pembelian_id', $drms->detail_rencana_pembelian_id)->first()->update([
                        'ms2' => 0,
                        'delivery' => 1
                    ]);
                    $drms->update([
                        'is_delivery' => 1
                    ]);
                } else {
                    DetailRencanaPembelian::where('detail_rencana_pembelian_id', $drms->detail_rencana_pembelian_id)->first()->update([
                        'ms2' => 0,
                        'delivery' => 0
                    ]);
                }
            }

            $ms2->update([
                'is_done' => 1
            ]);

            $loop = true;
            do {
                $pengiriman = Pengiriman::where('member_id', $ms2->member_id)->where('tanggal', $request->tanggal)->where('no_do', $request->no_do)->where('no_pol', $request->no_pol)->where('is_confirmation_admin', 0)->where('is_confirmation_top', 0)->where('is_done', 0)->where('catatan', $request->catatan)->count();
                if ($pengiriman == 0) {
                    $pengiriman = Pengiriman::create([
                        'rencana_pembelian_id' => '',
                        'member_id' => $ms2->member_id,
                        'tanggal' => $request->tanggal,
                        'no_do' => $request->no_do,
                        'no_pol' => $request->no_pol,
                        'is_confirmation_admin' => 0,
                        'is_confirmation_top' => 0,
                        'is_done' => 0,
                        'catatan' => $request->catatan,
                    ]);
                } else if ($pengiriman == 1) {
                    $loop = false;
                } else {
                    Pengiriman::where('member_id', $ms2->member_id)->where('tanggal', $request->tanggal)->where('no_do', $request->no_do)->where('no_pol', $request->no_pol)->where('is_confirmation_admin', 0)->where('is_confirmation_top', 0)->where('is_done', 0)->where('catatan', $request->catatan)->delete();
                }
            } while ($loop);

            $pengiriman = Pengiriman::where('member_id', $ms2->member_id)->where('tanggal', $request->tanggal)->where('no_do', $request->no_do)->where('no_pol', $request->no_pol)->where('is_confirmation_admin', 0)->where('is_confirmation_top', 0)->where('is_done', 0)->where('catatan', $request->catatan)->first();

            if (DB::table('pengiriman_ms2')->where('pengiriman_id', $pengiriman->pengiriman_id)->where('ms2_id', $ms2->ms2_id)->count() == 0) {
                DB::table('pengiriman_ms2')->insert([
                    'pengiriman_id' => $pengiriman->pengiriman_id,
                    'ms2_id' => $ms2->ms2_id
                ]);
            }

            return redirect('/procurement/ms2/pengiriman/' . $ms2->ms2_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data MS2 sudah dikirim ke Fase Pengiriman.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        } else {
            return redirect('/procurement/ms2/pengiriman/' . $ms2->ms2_id . '/konfirm')->with('msg', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Anda perlu memilih minimal satu produk untuk dikirim.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }

    public function generateKodeSms($detail_rencana_pembelian_id, $jam_kerja_id)
    {
        $sms = '';
        for ($i = 0; $i < count($detail_rencana_pembelian_id); $i++) {
            $drpp = DetailRencanaPembelian::where('detail_rencana_pembelian_id', $detail_rencana_pembelian_id[$i])->first();
            $sms .= 'Stok ' . explode(' ', $drpp->rencana_pembelian()->first()->spbu()->first()->nama_spbu)[1] . ' ';
            $jk = JamKerja::where('jam_kerja_id', $jam_kerja_id[$i])->first();


            $singkatan = $drpp->produk()->first()->singkatan;
            $stokTerakhirDombak = str_replace(',', '', number_format($drpp->produk()->first()->stok_inventory()->first()->stok, 0, ".", ","));
            $stokPertamina = $drpp->jumlah / 1000;

            $sms .=  $singkatan . $stokTerakhirDombak . '-' . $stokPertamina . '-0-' . $jk->urutan . ':' . $stokPertamina . ' ';
        }

        return $sms;
    }
}
