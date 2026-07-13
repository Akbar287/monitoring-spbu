<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FinancialController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "COA (Master Akun)",
                "icon" => "sitemap",
                "url" => route('coa.index'),
                "color" => "dark"
            ],
            [
                "nama" => "Jurnal Umum",
                "icon" => "book",
                "url" => route('jurnal.index'),
                "color" => "secondary"
            ],
            [
                "nama" => "Saldo Awal",
                "icon" => "balance-scale",
                "url" => route('saldo-awal.index'),
                "color" => "warning"
            ],
            [
                "nama" => "Tutup Buku",
                "icon" => "lock",
                "url" => route('periode-akuntansi.index'),
                "color" => "danger"
            ],
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
                "nama" => "Neraca Saldo",
                "icon" => "list-ol",
                "url" => route('neraca-saldo.index'),
                "color" => "success"
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
            [
                'id' => 'penjualan',
                'title' => 'Pendapatan Penjualan per Bulan',
                'icon' => 'chart-line',
                'color' => 'success',
                'badge' => 'Rupiah',
                'col' => 'col-lg-6 col-12',
                'type' => 'line',
                'data' => Charts::monthly('penjualan', 'tanggal', 'SUM(total_penjualan)'),
                'opts' => ['money' => true, 'label' => 'Pendapatan'],
            ],
            [
                'id' => 'pettyArus',
                'title' => 'Arus Petty Cash per Bulan',
                'icon' => 'wallet',
                'color' => 'primary',
                'badge' => 'Rupiah',
                'col' => 'col-lg-3 col-md-6 col-12',
                'type' => 'bar',
                'data' => Charts::monthly('petty_cash', 'tanggal', 'SUM(jumlah)'),
                'opts' => ['money' => true, 'colorEach' => false, 'colorIndex' => 0, 'label' => 'Petty Cash'],
            ],
            [
                'id' => 'pettyJenis',
                'title' => 'Petty Cash per Jenis',
                'icon' => 'random',
                'color' => 'warning',
                'badge' => 'Rupiah',
                'col' => 'col-lg-3 col-md-6 col-12',
                'type' => 'doughnut',
                'data' => ['labels' => $pettyJenis->pluck('jenis')->map(fn ($j) => ucfirst($j ?? '-')), 'data' => $pettyJenis->pluck('total')],
                'opts' => ['money' => true],
            ],
        ];

        return Inertia::render('Menu/Index', [
            'title' => 'Financial',
            'description' => 'Tren pendapatan, arus petty cash, dan komposisi transaksi kas kecil.',
            'menus' => $allMenu,
            'charts' => $charts,
        ]);
    }
}
