<?php

namespace App\Http\Controllers;

use App\Models\PajakPembelianLib;
use App\Models\RencanaPembelian;
use App\Models\StatusPurchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Yajra\DataTables\DataTables;

class PembelianController extends Controller
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
            $data = StatusPurchase::where("nama_status", "Pembelian")->first()->rencana_pembelian()->whereBetween('created_at', [$waktuMulai, $waktuSelesai])->get();
            return DataTables::of($data)
                ->addIndexColumn()
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
                    $actionBtn = '<a href="' . route('pembelian.show', $row->rencana_pembelian_id) . '" class="edit btn btn-success btn-sm">Detail</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return Inertia::render('Table/Index', [
            'title' => 'Pembelian',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')]],
            'dateFilter' => ['mulai' => $waktuMulai, 'selesai' => $waktuSelesai],
            'table' => [
                'url' => $request->fullUrl(),
                'columns' => [
                    ['label' => 'Kode', 'data' => 'kode_pembelian'],
                    ['label' => 'Tanggal', 'data' => 'created_at'],
                    ['label' => 'Diajukan Oleh', 'data' => 'member_id'],
                    ['label' => 'Total Produk', 'data' => 'produk_id'],
                    ['label' => 'Aksi', 'data' => 'action', 'orderable' => false, 'searchable' => false, 'raw' => true],
                ],
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(RencanaPembelian $pembelian)
    {
        $pajak = PajakPembelianLib::where('is_aktif', 1)->first();

        $fields = [
            ['label' => 'Id Rencana Pembelian', 'value' => $pembelian->rencana_pembelian_id],
            ['label' => 'Kode', 'value' => $pembelian->kode_pembelian],
            ['label' => 'SPBU', 'value' => optional($pembelian->spbu()->first())->nama_spbu],
            ['label' => 'Keterangan', 'value' => $pembelian->keterangan],
            ['label' => 'Grand Total (Sblm Pajak)', 'value' => 'Rp ' . number_format($pembelian->grand_total, 6, '.', ',')],
        ];
        if ($pp = $pembelian->pajak_pembelian()->first()) {
            $fields[] = ['label' => 'PPN', 'value' => 'Rp ' . number_format($pp->ppn, 6, '.', ',')];
            $fields[] = ['label' => 'PPBKB', 'value' => 'Rp ' . number_format($pp->ppbkb, 6, '.', ',')];
            $fields[] = ['label' => 'PPH', 'value' => 'Rp ' . number_format($pp->pph, 6, '.', ',')];
            $fields[] = ['label' => 'Grand Total (Stlh Pajak)', 'value' => 'Rp ' . number_format($pp->total_amount, 6, '.', ',')];
        } elseif ($pajak) {
            $stlh = $pembelian->grand_total + $pembelian->grand_total * ($pajak->ppn / 100) + $pembelian->grand_total * ($pajak->ppbkb / 100) + $pembelian->grand_total * ($pajak->pph / 100);
            $fields[] = ['label' => 'Grand Total (Stlh Pajak)', 'value' => 'Rp ' . number_format($stlh, 6, '.', ',')];
        }

        $rows = $pembelian->detail_rencana_pembelian()->get()->map(fn ($p) => [
            optional($p->produk()->first())->nama_produk,
            number_format($p->rit, 6, '.', ','),
            number_format($p->jumlah, 6, '.', ','),
            $p->satuan_jumlah,
            'Rp ' . number_format($p->harga, 6, '.', ','),
            'Rp ' . number_format($p->total_harga, 6, '.', ','),
        ])->values()->all();

        return Inertia::render('Show/Index', [
            'title' => 'Detail Pembelian',
            'breadcrumbs' => [['label' => 'Procurement', 'url' => route('menu.procurement')], ['label' => 'Pembelian', 'url' => route('pembelian.index')]],
            'backUrl' => route('pembelian.index'),
            'fields' => $fields,
            'tables' => [[
                'title' => 'Detail Rencana Pembelian',
                'columns' => ['Nama Produk', 'Rit', 'Quantity', 'Satuan', 'Harga', 'Total Harga'],
                'rows' => $rows,
            ]],
            'actions' => [
                ['label' => 'Detail Produk', 'url' => route('pembelian.detail.index', $pembelian->rencana_pembelian_id), 'color' => 'warning', 'icon' => 'box'],
                ['label' => 'Konfirmasi Harga', 'url' => route('pembelian.konfirmasi', $pembelian->rencana_pembelian_id), 'color' => 'info', 'icon' => 'check',
                    'confirm' => 'Anda yakin ingin meneruskan Rencana Pembelian ini ke tahap pembayaran?'],
                ['label' => 'Batalkan Pembelian', 'url' => route('pembelian.perencanaan', $pembelian->rencana_pembelian_id), 'color' => 'danger', 'icon' => 'times',
                    'confirm' => 'Anda yakin membatalkan pembelian untuk rencana ini?'],
            ],
        ]);
    }

    public function konfirmasi_harga(RencanaPembelian $pembelian)
    {
        $status = StatusPurchase::where('nama_status', "Pembayaran")->first();
        $pembelian->update([
            'tanggal_pembelian' => date('Y-m-d'),
            'status_purchase_id' => $status->status_purchase_id
        ]);
        return redirect('/procurement/pembelian')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Data Pembelian sudah dilanjutkan ke tahap pembayaran.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }

    public function konfirmasi_ulang(RencanaPembelian $pembelian)
    {
        $status = StatusPurchase::where('nama_status', "Rencana")->first();
        $pembelian->update([
            'status_purchase_id' => $status->status_purchase_id,
            'is_confirmation' => 1,
            'is_denied' => 0
        ]);
        return redirect('/procurement/pembelian')->with('msg', '<div class="alert alert-success alert-dismissible fade show" role="alert"><strong>Sukses!</strong> Berhasil Mengubah Data Pembelian dengan konfirmasi ditolak.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
    }
}
