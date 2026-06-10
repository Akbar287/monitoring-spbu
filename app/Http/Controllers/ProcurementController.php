<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Perencanaan",
                "icon" => "th",
                "url" => route('perencanaan.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Pembelian",
                "icon" => "tint",
                "url" => route('pembelian.index'),
                "color" => "success"
            ],
            [
                "nama" => "Pembayaran",
                "icon" => "upload",
                "url" => route('pembayaran.index'),
                "color" => "danger"
            ],
            [
                "nama" => "MS2",
                "icon" => "percent",
                "url" => route('ms2.pengiriman'),
                "color" => "warning"
            ],
            [
                "nama" => "Delivery",
                "icon" => "file",
                "url" => route('pengiriman.index'),
                "color" => "info"
            ]
        ];

        // Rencana pembelian per status purchase
        $status = DB::table('rencana_pembelian')
            ->join('status_purchase', 'status_purchase.status_purchase_id', 'rencana_pembelian.status_purchase_id')
            ->select('status_purchase.nama_status', DB::raw('COUNT(*) as total'))
            ->groupBy('status_purchase.nama_status')
            ->orderBy('status_purchase.nama_status')
            ->get();

        // Status pengiriman
        $pengirimanSelesai = DB::table('pengiriman')->where('is_done', 1)->count();
        $pengirimanProses = DB::table('pengiriman')->where('is_done', 0)->count();

        $charts = [
            'status' => [
                'labels' => $status->pluck('nama_status'),
                'data' => $status->pluck('total'),
            ],
            'pengiriman' => [
                'labels' => ['Selesai', 'Dalam Proses'],
                'data' => [$pengirimanSelesai, $pengirimanProses],
            ],
            'nilai' => Charts::monthly('rencana_pembelian', 'tanggal_pembelian', 'SUM(grand_total)'),
        ];

        return view('procurement/index', compact('allMenu', 'charts'));
    }
}
