<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'stok' => [
                'labels' => $stok->pluck('nama_produk'),
                'data' => $stok->pluck('total'),
            ],
            'penerimaan' => Charts::monthly('penerimaan', 'tanggal', 'COUNT(*)'),
            'losses' => [
                'labels' => $losses->pluck('simbol')->map(fn ($s) => ucfirst($s)),
                'data' => $losses->pluck('total'),
            ],
        ];

        return view('stok/index', compact('allMenu', 'charts'));
    }
}
