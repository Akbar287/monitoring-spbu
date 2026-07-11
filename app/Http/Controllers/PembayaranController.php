<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembayaranRequest;
use App\Models\FilePembayaran;
use App\Models\Member;
use App\Models\Pembayaran;
use App\Models\RencanaPembelian;
use App\Models\StatusPurchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class PembayaranController extends Controller
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

        if ($request->ajax() && ! $request->inertia()) {
            $data = StatusPurchase::where("nama_status", "Pembayaran")->first()->rencana_pembelian()->whereBetween('tanggal_pembelian', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    $actionBtn = Carbon::createFromFormat('Y-m-d', $row->tanggal_pembelian)->format('d F Y');
                    return $actionBtn;
                })
                ->addColumn('produk_id', function ($row) {
                    $actionBtn = $row->detail_rencana_pembelian()->count() . ' Produk';
                    return $actionBtn;
                })
                ->addColumn('grand_total', function ($row) {
                    $actionBtn = "Rp. " . number_format($row->grand_total, 6, '.', ',');
                    return $actionBtn;
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pembayaran.show', $row->rencana_pembelian_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Pembayaran',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')]],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Kode', 'data' => 'kode_pembelian'],
                    ['label' => 'Tanggal', 'data' => 'created_at'],
                    ['label' => 'Grand Total', 'data' => 'grand_total'],
                    ['label' => 'Total Produk', 'data' => 'produk_id'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, RencanaPembelian $rencana)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $rencana->pembayaran()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('is_confirmation', function ($row) {
                    $actionBtn = ($row->is_confirmation_admin == 1 ? "<div class='badge badge-success'>Admin</div>" : "<div class='badge badge-danger'>Admin</div>") . ' ' . ($row->is_confirmation_top == 1 ? "<div class='badge badge-success'>Top</div>" : "<div class='badge badge-danger'>Top</div>");
                    return $actionBtn;
                })
                ->addColumn('total_setoran', function ($row) {
                    $actionBtn = "Rp. " . number_format($row->total_setoran, 6, '.', ',');
                    return $actionBtn;
                })
                ->addColumn('tanggal', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pembayaran.detail', [$row->rencana_pembelian()->first()->rencana_pembelian_id, $row->pembayaran_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action', 'is_confirmation'])
                ->make(true);
        }
        $continue = false;
        if ($rencana->pembayaran()->count() > 0) {
            foreach ($rencana->pembayaran()->get() as $p) {
                if ($p->is_confirmation_admin && $p->is_confirmation_top) {
                    $continue = true;
                }
            }
        }
        $pajak = $rencana->pajak_pembelian()->first();

        $isPJ = optional(optional(optional(auth()->user()->member()->first())->jabatan()->first())->level()->first())->nama_level === 'Penanggung Jawab';

        $fields = [
            ['label' => 'Id Rencana Pembelian', 'value' => $rencana->rencana_pembelian_id],
            ['label' => 'Kode', 'value' => $rencana->kode_pembelian],
            ['label' => 'SPBU', 'value' => optional($rencana->spbu()->first())->nama_spbu],
            ['label' => 'Keterangan', 'value' => $rencana->keterangan],
        ];
        if ($pajak) {
            $fields[] = ['label' => 'Net Price', 'value' => 'Rp ' . number_format($pajak->net_price, 6, '.', ',')];
            $fields[] = ['label' => 'PPn', 'value' => 'Rp ' . number_format($pajak->ppn, 6, '.', ',')];
            $fields[] = ['label' => 'PPh', 'value' => 'Rp ' . number_format($pajak->pph, 6, '.', ',')];
            $fields[] = ['label' => 'PPBKB', 'value' => 'Rp ' . number_format($pajak->ppbkb, 6, '.', ',')];
            $fields[] = ['label' => 'Total Amount (Gross Price)', 'value' => 'Rp ' . number_format($pajak->total_amount, 6, '.', ',')];
        }

        $rows = $rencana->detail_rencana_pembelian()->get()->map(fn ($p) => [
            optional($p->produk()->first())->nama_produk,
            number_format($p->rit, 6, '.', ','),
            number_format($p->jumlah, 6, '.', ','),
            $p->satuan_jumlah,
            'Rp ' . number_format($p->harga, 6, '.', ','),
            'Rp ' . number_format($p->total_harga, 6, '.', ','),
        ])->values()->all();

        $actions = [
            ['label' => 'Tambah Pembayaran', 'url' => route('pembayaran.create', $rencana->rencana_pembelian_id), 'color' => 'primary', 'icon' => 'plus'],
            ['label' => 'Pajak', 'url' => route('pembayaran.pajak_edit', $rencana->rencana_pembelian_id), 'color' => 'warning', 'icon' => 'percent'],
        ];
        if ($isPJ) {
            if ($continue) {
                $actions[] = ['label' => 'Konfirmasi Pembayaran', 'method' => 'put', 'url' => route('pembayaran.delivery', $rencana->rencana_pembelian_id),
                    'color' => 'info', 'icon' => 'check', 'confirm' => 'Sudah Bayar? Ingin meneruskan rencana ini ke tahap MS2 ?'];
            }
            $actions[] = ['label' => 'Batalkan Konfirmasi Rencana', 'method' => 'put', 'url' => route('pembayaran.ditolak', $rencana->rencana_pembelian_id),
                'color' => 'danger', 'icon' => 'times', 'confirm' => 'Anda yakin membatalkan pembayaran untuk rencana pembelian ini?'];
        }

        return Inertia::render('Show/Index', [
            'title' => 'Detail Pembayaran',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembayaran', 'url' => route('pembayaran.index')]],
            'backUrl' => route('pembayaran.index'),
            'fields' => $fields,
            'tables' => [[
                'title' => 'Detail Rencana Pembelian',
                'columns' => ['Nama Produk', 'Rit', 'Quantity', 'Satuan', 'Harga', 'Total Harga'],
                'rows' => $rows,
            ]],
            'table' => [
                'title' => 'Pembayaran',
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal', 'data' => 'tanggal'],
                    ['label' => 'Nomor Rekening', 'data' => 'no_rekening'],
                    ['label' => 'Nama Rekening', 'data' => 'nama_rekening'],
                    ['label' => 'Total Bayar', 'data' => 'total_setoran'],
                    ['label' => 'Status', 'data' => 'is_confirmation', 'raw' => true],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
            'actions' => $actions,
        ]);
    }

    public function konfirmasi_delivery(RencanaPembelian $rencana)
    {
        $status = StatusPurchase::where('nama_status', "MS2")->first();
        $rencana->update([
            'status_purchase_id' => $status->status_purchase_id,
        ]);

        // foreach ($rencana->detail_rencana_pembelian()->get() as $d) {
        //     $d->update(['ms2' => true]);
        // }
        return redirect('/procurement/pembayaran')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Mengubah Data Pembayaran. Data akan dikirim ke tahap MS2.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function konfirmasi_ditolak(RencanaPembelian $rencana)
    {
        $status = StatusPurchase::where('nama_status', "Rencana")->first();
        $rencana->update([
            'status_purchase_id' => $status->status_purchase_id,
            'is_confirmation' => 1,
            'is_denied' => 0
        ]);
        return redirect('/procurement/pembayaran')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Mengubah Data Pembayaran dengan konfirmasi ditolak. Data dikirim ke Rencana Pembelian tertolak.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function create(RencanaPembelian $rencana)
    {
        $member = Member::get();

        return Inertia::render('Form/Index', [
            'title' => 'Tambah Pembayaran',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembayaran', 'url' => route('pembayaran.index')], ['label' => $rencana->kode_pembelian, 'url' => route('pembayaran.show', $rencana->rencana_pembelian_id)]],
            'backUrl' => route('pembayaran.show', $rencana->rencana_pembelian_id),
            'method' => 'post',
            'action' => route('pembayaran.store', $rencana->rencana_pembelian_id),
            'fields' => [
                ['name' => 'tanggal', 'label' => 'Tanggal Bayar', 'type' => 'date', 'value' => null],
                ['name' => 'member_id', 'label' => 'Penyetor', 'type' => 'select', 'value' => null,
                    'options' => $member->map(fn ($s) => ['value' => $s->member_id, 'label' => optional($s->ktp()->first())->nama])->values()->all()],
                ['name' => 'no_rekening', 'label' => 'No Rekening', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'nama_rekening', 'label' => 'Nama Rekening', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'no_cek_bg', 'label' => 'No Cek BG', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
                ['name' => 'total_setoran', 'label' => 'Total Setor Ke Bank', 'type' => 'text', 'value' => null, 'col' => 'col-md-6'],
            ],
        ]);
    }
    public function store(PembayaranRequest $pembayaranRequest, RencanaPembelian $rencana)
    {

        $pembayaran = $rencana->pembayaran()->where("member_id", request('member_id'))->where("no_cek_bg", request('no_cek_bg'))->where("no_rekening", request('no_rekening'))->where("nama_rekening", request('nama_rekening'))->where("total_setoran", str_replace(',', '', request('total_setoran')))->where("is_confirmation_top", 0)->where("is_confirmation_admin", 0)->where("tanggal", request('tanggal'))->first();
        if (is_null($pembayaran)) {
            $pembayaran = $rencana->pembayaran()->create($this->pembayaranData());
        }

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Menulis Data Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function detail(Request $request, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        if ($request->ajax() && ! $request->inertia()) {
            $data = $pembayaran->file_pembayaran()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tanggal_upload', function ($row) {
                    return Carbon::createFromFormat('Y-m-d', $row->tanggal_upload)->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('pembayaran.file.unduh', [$row->pembayaran()->first()->rencana_pembelian()->first()->rencana_pembelian_id, $row->pembayaran()->first()->pembayaran_id, $row->file_pembayaran_id]) . '" class="unduh btn btn-primary btn-sm">Unduh</a> <a href="' . route('pembayaran.file.show', [$row->pembayaran()->first()->rencana_pembelian()->first()->rencana_pembelian_id, $row->pembayaran()->first()->pembayaran_id, $row->file_pembayaran_id]) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $isPJ = optional(optional(optional(auth()->user()->member()->first())->jabatan()->first())->level()->first())->nama_level === 'Penanggung Jawab';

        $actions = [
            ['label' => 'Tambah File', 'url' => route('pembayaran.file.create', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]), 'color' => 'primary', 'icon' => 'plus'],
            ['label' => 'Ubah', 'url' => route('pembayaran.edit', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]), 'color' => 'success', 'icon' => 'pen'],
        ];
        $actions[] = $pembayaran->is_confirmation_top
            ? ['label' => 'Batalkan Konfirmasi Top', 'method' => 'put', 'url' => route('pembayaran.update.confirmation_top_no', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]), 'color' => 'danger', 'icon' => 'times', 'confirm' => "Tolak Konfirmasi Pembayaran ini!\n Lanjutkan?"]
            : ['label' => 'Konfirmasi Top Manajemen', 'method' => 'put', 'url' => route('pembayaran.update.confirmation_top_ok', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]), 'color' => 'info', 'icon' => 'check', 'confirm' => "Konfirmasi Pembayaran ini!\n Lanjutkan?"];
        if ($isPJ) {
            $actions[] = $pembayaran->is_confirmation_admin
                ? ['label' => 'Batalkan Konfirmasi Admin', 'method' => 'put', 'url' => route('pembayaran.update.confirmation_admin_no', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]), 'color' => 'danger', 'icon' => 'times', 'confirm' => "Tolak Konfirmasi Pembayaran ini!\n Lanjutkan?"]
                : ['label' => 'Konfirmasi Admin', 'method' => 'put', 'url' => route('pembayaran.update.confirmation_admin_ok', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]), 'color' => 'info', 'icon' => 'check', 'confirm' => "Konfirmasi Pembayaran ini!\n Lanjutkan?"];
        }

        return Inertia::render('Show/Index', [
            'title' => 'Detail Pembayaran',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembayaran', 'url' => route('pembayaran.index')], ['label' => $rencana->kode_pembelian, 'url' => route('pembayaran.show', $rencana->rencana_pembelian_id)]],
            'backUrl' => route('pembayaran.show', $rencana->rencana_pembelian_id),
            'fields' => [
                ['label' => 'Tanggal Bayar', 'value' => $pembayaran->tanggal],
                ['label' => 'Penyetor', 'value' => optional(optional($pembayaran->member()->first())->ktp()->first())->nama],
                ['label' => 'No Rekening', 'value' => $pembayaran->no_rekening],
                ['label' => 'Nama Rekening', 'value' => $pembayaran->nama_rekening],
                ['label' => 'No Cek BG', 'value' => $pembayaran->no_cek_bg],
                ['label' => 'Total Setor Ke Bank', 'value' => 'Rp ' . number_format($pembayaran->total_setoran, 6, '.', ',')],
                ['label' => 'Konfirmasi Admin', 'value' => $pembayaran->is_confirmation_admin ? 'Terkonfirmasi' : 'Belum Terkonfirmasi'],
                ['label' => 'Konfirmasi Top Manajemen', 'value' => $pembayaran->is_confirmation_top ? 'Terkonfirmasi' : 'Belum Terkonfirmasi'],
            ],
            'table' => [
                'title' => 'File Pembayaran',
                'url' => $request->url(),
                'columns' => [
                    ['label' => 'Tanggal Upload', 'data' => 'tanggal_upload'],
                    ['label' => 'Nama File', 'data' => 'nama_file'],
                    ['label' => 'Nama Dokumen', 'data' => 'nama_dokumen'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
            'actions' => $actions,
            'deleteUrl' => route('pembayaran.destroy', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]),
        ]);
    }
    public function edit(RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        $member = Member::get();
        return Inertia::render('Form/Index', [
            'title' => 'Ubah Pembayaran',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembayaran', 'url' => route('pembayaran.index')], ['label' => $rencana->kode_pembelian, 'url' => route('pembayaran.show', $rencana->rencana_pembelian_id)]],
            'backUrl' => route('pembayaran.detail', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]),
            'method' => 'put',
            'action' => route('pembayaran.update', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]),
            'fields' => [
                ['name' => 'tanggal', 'label' => 'Tanggal Bayar', 'type' => 'date', 'value' => $pembayaran->tanggal],
                ['name' => 'member_id', 'label' => 'Penyetor', 'type' => 'select', 'value' => $pembayaran->member_id,
                    'options' => $member->map(fn ($s) => ['value' => $s->member_id, 'label' => optional($s->ktp()->first())->nama])->values()->all()],
                ['name' => 'no_rekening', 'label' => 'No Rekening', 'type' => 'text', 'value' => $pembayaran->no_rekening, 'col' => 'col-md-6'],
                ['name' => 'nama_rekening', 'label' => 'Nama Rekening', 'type' => 'text', 'value' => $pembayaran->nama_rekening, 'col' => 'col-md-6'],
                ['name' => 'no_cek_bg', 'label' => 'No Cek BG', 'type' => 'text', 'value' => $pembayaran->no_cek_bg, 'col' => 'col-md-6'],
                ['name' => 'total_setoran', 'label' => 'Total Setor Ke Bank', 'type' => 'text', 'value' => number_format($pembayaran->total_setoran, 6, '.', ','), 'col' => 'col-md-6'],
            ],
        ]);
    }
    public function update(PembayaranRequest $pembayaranRequest, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        $pembayaran->update($this->pembayaranData());

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Mengubah Data Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function update_confirmation_admin_ok(Request $request, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        if (Auth::user()->member()->first()->jabatan()->first()->nama_jabatan == 'Admin') {
            $pembayaran->update(['is_confirmation_admin' => 1]);
            return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Konfirmasi Pembayaran - Admin.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }
    public function update_confirmation_admin_no(Request $request, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        if (Auth::user()->member()->first()->jabatan()->first()->nama_jabatan == 'Admin') {
            $pembayaran->update(['is_confirmation_admin' => 0]);

            return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Membatalkan Konfirmasi Pembayaran - Admin.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        }
    }
    public function update_confirmation_top_ok(Request $request, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        // if (Auth::user()->member()->first()->jabatan()->first()->level()->first()->divisi()->first()->nama_divisi == 'Top Manajemen') {
        $pembayaran->update(['is_confirmation_top' => 1]);

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Konfirmasi Pembayaran - Top Manajemen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        // }
    }
    public function update_confirmation_top_no(Request $request, RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        // if (Auth::user()->member()->first()->jabatan()->first()->level()->first()->divisi()->first()->nama_divisi == 'Top Manajemen') {
        $pembayaran->update(['is_confirmation_top' => 0]);

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Membatalkan Konfirmasi Pembayaran - Top Manajemen.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        // }
    }

    public function destroy(RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        $files = $pembayaran->file_pembayaran()->get();
        foreach ($files as $file) {
            if ($file->nama_file != 'default.png') {
                Storage::delete("public/pembayaran/" . $file->nama_file);
            }
        }
        $pembayaran->delete();

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Menghapus Data Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function pembayaranData()
    {
        return [
            "member_id" => request('member_id'),
            "no_cek_bg" => request('no_cek_bg'),
            "no_rekening" => request('no_rekening'),
            "nama_rekening" => request('nama_rekening'),
            "total_setoran" => str_replace(',', '', request('total_setoran')),
            "is_confirmation_top" => 0,
            "is_confirmation_admin" => 0,
            "tanggal" => request('tanggal'),
        ];
    }

    public function file_create(RencanaPembelian $rencana, Pembayaran $pembayaran)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Tambah File Pembayaran',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembayaran', 'url' => route('pembayaran.index')], ['label' => $rencana->kode_pembelian, 'url' => route('pembayaran.show', $rencana->rencana_pembelian_id)], ['label' => 'File', 'url' => route('pembayaran.detail', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id])]],
            'backUrl' => route('pembayaran.detail', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]),
            'method' => 'post',
            'action' => route('pembayaran.file.store', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]),
            'fields' => [
                ['name' => 'file', 'label' => 'File', 'type' => 'file', 'value' => null, 'hint' => 'Pilih file bukti pembayaran yang akan diunggah.'],
            ],
        ]);
    }
    public function file_store(RencanaPembelian $rencana, Pembayaran $pembayaran, Request $request)
    {
        $request->validate(['file' => 'required|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = $request->nama_file;
        if ($request->hasFile('file')) {
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('file')->storeAs('public/pembayaran', $foto);
        }
        $file = $pembayaran->file_pembayaran()->where("nama_file", $foto)->where("nama_dokumen", $filenameWithExt)->where("tanggal_upload", date('Y-m-d'))->first();
        if (is_null($file)) {
            $file = $pembayaran->file_pembayaran()->create($this->filePembayaranData($foto, $filenameWithExt));
        }

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id . '/' . $file->file_pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil menambah Data File Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function file_show(RencanaPembelian $rencana, Pembayaran $pembayaran, FilePembayaran $file)
    {
        return Inertia::render('Show/Index', [
            'title' => 'Detail File Pembayaran',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembayaran', 'url' => route('pembayaran.index')], ['label' => $rencana->kode_pembelian, 'url' => route('pembayaran.show', $rencana->rencana_pembelian_id)], ['label' => 'File', 'url' => route('pembayaran.detail', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id])]],
            'backUrl' => route('pembayaran.detail', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id]),
            'fields' => [
                ['label' => 'Nama Dokumen', 'value' => $file->nama_dokumen],
                ['label' => 'Nama File', 'value' => $file->nama_file],
                ['label' => 'Tanggal Upload', 'value' => $file->tanggal_upload],
            ],
            'image' => asset('storage/pembayaran/' . $file->nama_file),
            'actions' => [
                ['label' => 'Ubah', 'url' => route('pembayaran.file.edit', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id, $file->file_pembayaran_id]), 'color' => 'primary', 'icon' => 'pen'],
                ['label' => 'Download', 'url' => route('pembayaran.file.unduh', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id, $file->file_pembayaran_id]), 'color' => 'info', 'icon' => 'download'],
            ],
            'deleteUrl' => route('pembayaran.file.destroy', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id, $file->file_pembayaran_id]),
        ]);
    }
    public function file_edit(RencanaPembelian $rencana, Pembayaran $pembayaran, FilePembayaran $file)
    {
        return Inertia::render('Form/Index', [
            'title' => 'Ubah File Pembayaran',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembayaran', 'url' => route('pembayaran.index')], ['label' => $rencana->kode_pembelian, 'url' => route('pembayaran.show', $rencana->rencana_pembelian_id)], ['label' => 'File', 'url' => route('pembayaran.detail', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id])]],
            'backUrl' => route('pembayaran.file.show', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id, $file->file_pembayaran_id]),
            'method' => 'put',
            'action' => route('pembayaran.file.update', [$rencana->rencana_pembelian_id, $pembayaran->pembayaran_id, $file->file_pembayaran_id]),
            'fields' => [
                ['name' => 'file', 'label' => 'File Pengganti', 'type' => 'file', 'value' => null, 'hint' => 'File saat ini: ' . $file->nama_dokumen],
            ],
        ]);
    }
    public function file_update(RencanaPembelian $rencana, Pembayaran $pembayaran, FilePembayaran $file, Request $request)
    {
        $request->validate(['file' => 'nullable|file|max:' . \App\Support\Upload::maxKb()]);

        $foto = $request->nama_file;
        if ($request->hasFile('file')) {
            if ($file->nama_file != 'default.png') {
                Storage::delete("public/pembayaran/" . $file->nama_file);
            }

            $filenameWithExt = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $foto = $filename . '_' . time() . '.' . $extension;
            $request->file('file')->storeAs('public/pembayaran', $foto);
        }

        $file->update($this->filePembayaranData($foto, $filenameWithExt));

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id . '/' . $file->file_pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Mengubah Data File Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function file_destroy(RencanaPembelian $rencana, Pembayaran $pembayaran, FilePembayaran $file)
    {
        if ($file->nama_file != 'default.png') {
            Storage::delete("public/pembayaran/" . $file->nama_file);
        }
        $file->delete();

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id . '/' . $pembayaran->pembayaran_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Menghapus Data File Pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
    public function filePembayaranData($namaFile, $namaDokumen)
    {
        return [
            "nama_file" => $namaFile,
            "nama_dokumen" => $namaDokumen,
            "tanggal_upload" => date('Y-m-d')
        ];
    }
    public function file_unduh(RencanaPembelian $rencana, Pembayaran $pembayaran, FilePembayaran $file)
    {
        return Storage::download("public/pembayaran/" . $file->nama_file);
    }

    public function pajak_edit(RencanaPembelian $rencana)
    {
        $pajakLib = \App\Models\PajakPembelianLib::where('is_aktif', 1)->first();

        return Inertia::render('Pembelian/PajakEdit', [
            'title' => 'Ubah Pajak Pembayaran',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembayaran', 'url' => route('pembayaran.index')], ['label' => $rencana->kode_pembelian, 'url' => route('pembayaran.show', $rencana->rencana_pembelian_id)]],
            'backUrl' => route('pembayaran.show', $rencana->rencana_pembelian_id),
            'action' => route('pembayaran.pajak_update', $rencana->rencana_pembelian_id),
            'values' => [
                'net_price' => number_format($rencana->pajak_pembelian()->first()->net_price ?? $rencana->grand_total, 6, '.', ','),
                'ppn' => number_format($rencana->pajak_pembelian()->first()->ppn ?? $rencana->grand_total * ($pajakLib->ppn / 100), 6, '.', ','),
                'ppbkb' => number_format($rencana->pajak_pembelian()->first()->ppbkb ?? $rencana->grand_total * ($pajakLib->ppbkb / 100), 6, '.', ','),
                'pph' => number_format($rencana->pajak_pembelian()->first()->pph ?? $rencana->grand_total * ($pajakLib->pph / 100), 6, '.', ','),
                'total_amount' => number_format($rencana->pajak_pembelian()->first()->total_amount ?? $rencana->grand_total + ($rencana->grand_total * ($pajakLib->ppn / 100)) + ($rencana->grand_total * ($pajakLib->ppbkb / 100)) + ($rencana->grand_total * ($pajakLib->pph / 100)), 6, '.', ','),
                'metode_posting_pajak' => $rencana->pajak_pembelian()->first()->metode_posting_pajak ?? 'hpp',
            ],
        ]);
    }

    public function pajak_update(Request $request, RencanaPembelian $rencana)
    {
        $request->validate([
            'net_price' => ['required'],
            'ppn' => ['required'],
            'ppbkb' => ['required'],
            'pph' => ['required'],
            'metode_posting_pajak' => ['required', 'in:hpp,terpisah'],
        ]);

        if ($rencana->pajak_pembelian()->count() > 0) {
            $rencana->pajak_pembelian()->first()->update($this->pajakData($rencana->grand_total));
        } else {
            $rencana->pajak_pembelian()->create($this->pajakData($rencana->grand_total));
        }

        return redirect('/procurement/pembayaran/' . $rencana->rencana_pembelian_id)->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil mengubah data Pajak Pembelian.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function pajakData($grand_total)
    {
        return [
            'net_price' => str_replace(',', '', request('net_price')),
            'ppn' => str_replace(',', '', request('ppn')),
            'ppbkb' => str_replace(',', '', request('ppbkb')),
            'pph' => str_replace(',', '', request('pph')),
            'total_amount' => $grand_total,
            'metode_posting_pajak' => request('metode_posting_pajak', 'hpp'),
        ];
    }
}
