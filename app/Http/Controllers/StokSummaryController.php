<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\DokumenStok;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StokSummaryController extends Controller
{
    public function index()
    {
        return Inertia::render('StokSummary/Index', [
            'title' => 'Stok Summary',
            'breadcrumbs' => [['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')]],
            'showBase' => url('/stok/stok-summary'),
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function show($date)
    {
        $produk = Produk::where('is_aktif', 1)->get()->toArray();

        $temp = [];
        foreach ($produk as $p) {
            $temp[] = DokumenStok::with(['type_dokumen_stok', 'dombak', 'losses.jenis_losses'])
                ->select('dokumen_stok.tanggal')->distinct()
                ->addSelect('dokumen_stok.dokumen_stok_id')
                ->addSelect('tds.type_movement')
                ->addSelect('dokumen_stok.stok_awal')
                ->addSelect('dokumen_stok.stok_akhir')
                ->addSelect('dokumen_stok.is_confirmation')
                ->addSelect('dokumen_stok.created_at as waktu')
                ->addSelect('l.simbol')
                ->addSelect('l.jumlah')
                ->addSelect('jl.jenis_loss')
                ->addSelect('d.nama_dombak')
                ->addSelect('p.nama_produk')
                ->addSelect('jk.nama_jam_kerja')
                ->addSelect('jk.urutan')
                ->addSelect('sm.stand_meter_awal')
                ->addSelect('sm.stand_meter_akhir')
                ->addSelect(DB::raw("CASE
                    WHEN tds.type_movement = 'Stok Taking' THEN 1
                    WHEN tds.type_movement = 'Stok Taking Good Receive Penerimaan' THEN 2
                    WHEN tds.type_movement = 'Good Receive Penerimaan' THEN 3
                    WHEN tds.type_movement = 'Good Issue Sales' THEN 4
                    WHEN tds.type_movement = 'Stok Taking Good Transfer' THEN 5
                    WHEN tds.type_movement = 'Good Transfer Send' THEN 6
                    WHEN tds.type_movement = 'Good Transfer Receive' THEN 7
                    WHEN tds.type_movement = 'Good Issue Tera' THEN 8
                    WHEN tds.type_movement = 'Good Return Tera' THEN 9
                    WHEN tds.type_movement = 'Loss' THEN 10
                    WHEN tds.type_movement = 'Commit Change From User' THEN 11
                END as movement_order"))
                ->join('type_dokumen_stok as tds', 'dokumen_stok.type_dokumen_stok_id', '=', 'tds.type_dokumen_stok_id')
                ->join('dombak as d', 'dokumen_stok.dombak_id', '=', 'd.dombak_id')
                ->join('stok_inventory as si', 'si.stok_inventory_id', '=', 'dokumen_stok.stok_inventory_id')
                ->join('produk as p', 'si.produk_id', '=', 'p.produk_id')
                ->join('jam_kerja as jk', 'jk.jam_kerja_id', '=', 'dokumen_stok.jam_kerja_id')
                ->leftJoin('losses as l', 'dokumen_stok.dokumen_stok_id', '=', 'l.dokumen_stok_id')
                ->leftJoin('jenis_losses as jl', 'l.jenis_losses_id', '=', 'jl.jenis_losses_id')
                ->leftJoin('stand_meter_dokumen_stok as smds', 'smds.dokumen_stok_id', '=', 'dokumen_stok.dokumen_stok_id')
                ->leftJoin('stand_meter as sm', 'sm.stand_meter_id', '=', 'smds.stand_meter_id')
                ->where('dokumen_stok.tanggal', $date)
                ->where('si.produk_id', $p['produk_id'])
                ->orderBy('waktu', 'asc')
                ->orderBy('d.nama_dombak', 'asc')
                ->orderBy('jk.urutan', 'asc')
                ->orderBy('movement_order')->get();
        }

        $format = fn ($value) => number_format($value ?? 0, 6, '.', ',');
        $products = collect($produk)->map(function ($product, $index) use ($temp, $format) {
            return [
                'id' => $product['produk_id'],
                'name' => $product['nama_produk'],
                'items' => $temp[$index]->map(function ($item) use ($format) {
                    $rows = [
                        ['label' => 'Stok Awal', 'value' => $format($item->stok_awal)],
                    ];

                    if ($item->type_movement === 'Stok Taking') {
                        $rows[] = [
                            'label' => 'Losses / Profit',
                            'value' => ($item->simbol === 'kurang' ? '-' : '+') . ' ' . $format($item->jumlah),
                        ];
                    }

                    if ($item->type_movement === 'Good Transfer Send') {
                        $rows[] = ['label' => 'Stok Dipindah', 'value' => $format($item->stok_akhir - $item->stok_awal)];
                    }

                    if ($item->type_movement === 'Good Transfer Receive') {
                        $rows[] = ['label' => 'Stok Terpindah', 'value' => $format($item->stok_akhir - $item->stok_awal)];
                    }

                    if ($item->type_movement === 'Good Receive Penerimaan') {
                        $rows[] = ['label' => 'Stok Terisi', 'value' => $format($item->stok_akhir - $item->stok_awal)];
                    }

                    if (in_array($item->type_movement, ['Stok Taking Good Receive Penerimaan', 'Stok Taking Good Transfer'], true)) {
                        $rows[] = ['label' => 'Penjualan / Losses', 'value' => $format($item->stok_akhir - $item->stok_awal)];
                    }

                    $rows[] = ['label' => 'Stok Akhir', 'value' => $format($item->stok_akhir)];

                    if ($item->type_movement === 'Good Issue Sales') {
                        $rows[] = ['label' => 'Stand Meter Awal', 'value' => $format($item->stand_meter_awal) . ' Liter'];
                        $rows[] = ['label' => 'Penjualan', 'value' => $format(($item->stand_meter_akhir ?? 0) - ($item->stand_meter_awal ?? 0)) . ' Liter'];
                        $rows[] = ['label' => 'Stand Meter Akhir', 'value' => $format($item->stand_meter_akhir) . ' Liter'];
                    }

                    return [
                        'id' => $item->dokumen_stok_id,
                        'waktu' => $item->waktu,
                        'type_movement' => $item->type_movement,
                        'is_confirmation' => (bool) $item->is_confirmation,
                        'rows' => $rows,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return Inertia::render('StokSummary/Show', [
            'title' => 'Stok Summary',
            'dateLabel' => Carbon::createFromFormat('Y-m-d', $date)->format('d F Y'),
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('home')],
                ['label' => 'Manajemen Persediaan', 'url' => route('menu.stok')],
                ['label' => 'Stok Summary', 'url' => route('stok-summary.index')],
            ],
            'products' => $products,
            'backUrl' => route('stok-summary.index'),
        ]);
    }

    public function update(Request $request, $date)
    {
        //
    }

    public function api_call(Request $request) {}
}
