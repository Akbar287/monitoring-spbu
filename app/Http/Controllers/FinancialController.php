<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Petty Cash",
                "icon" => "th",
                "url" => route('financial.petty-cash'),
                "color" => "primary"
            ],
            [
                "nama" => "Rekap Pajak",
                "icon" => "file",
                "url" => route('rekap-pajak.index'),
                "color" => "info"
            ],
            [
                "nama" => "Margin",
                "icon" => "tint",
                "url" => route('margin.index'),
                "color" => "success"
            ],
            [
                "nama" => "Laba Rugi",
                "icon" => "upload",
                "url" => route('laba-rugi.index'),
                "color" => "danger"
            ],
            [
                "nama" => "Neraca",
                "icon" => "percent",
                "url" => route('neraca.index'),
                "color" => "warning"
            ],
            [
                "nama" => "Buku Besar",
                "icon" => "file",
                "url" => route('buku-besar.index'),
                "color" => "info"
            ],
            [
                "nama" => "Arus Kas",
                "icon" => "wrench",
                "url" => route('arus-kas.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Perubahan Modal",
                "icon" => "upload",
                "url" => route('perubahan-modal.index'),
                "color" => "info"
            ]
        ];
        // Petty cash per jenis transaksi
        $pettyJenis = DB::table('petty_cash')
            ->whereNull('deleted_at')
            ->select('jenis', DB::raw('SUM(jumlah) as total'))
            ->groupBy('jenis')
            ->get();

        $charts = [
            'penjualan' => Charts::monthly('penjualan', 'tanggal', 'SUM(total_penjualan)'),
            'pettyArus' => Charts::monthly('petty_cash', 'tanggal', 'SUM(jumlah)'),
            'pettyJenis' => [
                'labels' => $pettyJenis->pluck('jenis')->map(fn ($j) => ucfirst($j ?? '-')),
                'data' => $pettyJenis->pluck('total'),
            ],
        ];

        return view('financial/index', compact('allMenu', 'charts'));
    }
}
