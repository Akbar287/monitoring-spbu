<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StokController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (Auth::user()->member()->first()->jabatan()->first()->level()->first()->nama_level == 'Operator') {
            $allMenu = [
                [
                    "nama" => "Penerimaan BBM",
                    "icon" => "arrow-down",
                    "url" => route('penerimaan.index'),
                    "color" => "info"
                ],
                [
                    "nama" => "Stok Taking",
                    "icon" => "thermometer-full",
                    "url" => route('stok-taking.index'),
                    "color" => "danger"
                ],
                [
                    "nama" => "Stok Transfer",
                    "icon" => "flask",
                    "url" => route('stok-transfer.index'),
                    "color" => "success"
                ],
            ];
        } else {
            $allMenu = [
                [
                    "nama" => "Monitoring Stok",
                    "icon" => "cubes",
                    "url" => route('monitoring.index'),
                    "color" => "primary"
                ],
                [
                    "nama" => "Penerimaan BBM",
                    "icon" => "arrow-down",
                    "url" => route('penerimaan.index'),
                    "color" => "info"
                ],
                [
                    "nama" => "Stok Taking",
                    "icon" => "thermometer-full",
                    "url" => route('stok-taking.index'),
                    "color" => "danger"
                ],
                [
                    "nama" => "Stok Transfer",
                    "icon" => "flask",
                    "url" => route('stok-transfer.index'),
                    "color" => "success"
                ],
            ];
        }
        // Komposisi stok per produk
        $stok = DB::table('produk')
            ->leftJoin('stok_inventory', 'stok_inventory.produk_id', 'produk.produk_id')
            ->where('produk.is_aktif', 1)
            ->select('produk.nama_produk', DB::raw('COALESCE(SUM(stok_inventory.stok), 0) as total'))
            ->groupBy('produk.nama_produk')
            ->orderBy('produk.nama_produk')
            ->get();

        // Losses: lebih (untung) vs kurang (rugi)
        $losses = DB::table('losses')
            ->select('simbol', DB::raw('SUM(jumlah) as total'))
            ->groupBy('simbol')
            ->get();

        $charts = [
            [
                'id' => 'stok',
                'title' => 'Komposisi Stok per Produk',
                'icon' => 'gas-pump',
                'color' => 'primary',
                'badge' => 'Liter',
                'col' => 'col-lg-4 col-12',
                'type' => 'doughnut',
                'data' => ['labels' => $stok->pluck('nama_produk'), 'data' => $stok->pluck('total')],
                'opts' => ['unit' => 'L'],
            ],
            [
                'id' => 'penerimaan',
                'title' => 'Penerimaan BBM per Bulan',
                'icon' => 'arrow-down',
                'color' => 'info',
                'badge' => 'Dokumen',
                'col' => 'col-lg-5 col-12',
                'type' => 'bar',
                'data' => Charts::monthly('penerimaan', 'tanggal', 'COUNT(*)'),
                'opts' => ['colorEach' => false, 'colorIndex' => 4, 'label' => 'Penerimaan'],
            ],
            [
                'id' => 'losses',
                'title' => 'Losses (Lebih / Kurang)',
                'icon' => 'balance-scale',
                'color' => 'danger',
                'badge' => 'Liter',
                'col' => 'col-lg-3 col-12',
                'type' => 'bar',
                'data' => ['labels' => $losses->pluck('simbol')->map(fn ($s) => ucfirst($s)), 'data' => $losses->pluck('total')],
                'opts' => ['unit' => 'L'],
            ],
        ];

        return Inertia::render('Menu/Index', [
            'title' => 'Manajemen Persediaan',
            'description' => 'Sebaran stok BBM, tren penerimaan, dan akumulasi losses.',
            'menus' => $allMenu,
            'charts' => $charts,
        ]);
    }
}
