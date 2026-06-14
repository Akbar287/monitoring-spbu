<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MasterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Aset",
                "icon" => "th",
                "url" => route('aset.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Fasilitas",
                "icon" => "filter",
                "url" => route('menu.procurement'),
                "color" => "secondary"
            ],
            [
                "nama" => "Manajemen Persediaan",
                "icon" => "tint",
                "url" => route('fasilitas.index'),
                "color" => "success"
            ],
            [
                "nama" => "Produk",
                "icon" => "upload",
                "url" => route('produk.index'),
                "color" => "danger"
            ],
            [
                "nama" => "Pegawai",
                "icon" => "percent",
                "url" => route('member.index'),
                "color" => "warning"
            ],
            [
                "nama" => "Harga",
                "icon" => "percent",
                "url" => route('harga.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Dombak",
                "icon" => "file",
                "url" => route('dombak.index'),
                "color" => "info"
            ],
            [
                "nama" => "Payung",
                "icon" => "wrench",
                "url" => route('payung.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Dispenser",
                "icon" => "percent",
                "url" => route('dispenser.index'),
                "color" => "warning"
            ],
            [
                "nama" => "Nozzle",
                "icon" => "file",
                "url" => route('nozzle.index'),
                "color" => "info"
            ],
        ];

        // Ringkasan jumlah entitas master
        $ringkasan = [
            'Produk' => DB::table('produk')->where('is_aktif', 1)->count(),
            'Pegawai' => DB::table('member')->whereNull('deleted_at')->count(),
            'Dombak' => DB::table('dombak')->count(),
            'Payung' => DB::table('payung')->count(),
            'Dispenser' => DB::table('dispenser')->count(),
            'Nozzle' => DB::table('nozzle')->count(),
            'Aset' => DB::table('aset')->count(),
            'Fasilitas' => DB::table('fasilitas')->count(),
        ];

        // Komposisi stok per produk
        $stok = DB::table('produk')
            ->leftJoin('stok_inventory', 'stok_inventory.produk_id', 'produk.produk_id')
            ->where('produk.is_aktif', 1)
            ->select('produk.nama_produk', DB::raw('COALESCE(SUM(stok_inventory.stok), 0) as total'))
            ->groupBy('produk.nama_produk')
            ->orderBy('produk.nama_produk')
            ->get();

        // Harga jual default per produk
        $harga = DB::table('produk')
            ->join('harga', 'harga.produk_id', 'produk.produk_id')
            ->where('produk.is_aktif', 1)
            ->where('harga.is_default', 1)
            ->select('produk.nama_produk', DB::raw('MAX(harga.harga_jual) as harga'))
            ->groupBy('produk.nama_produk')
            ->orderBy('produk.nama_produk')
            ->get();

        $charts = [
            [
                'id' => 'ringkasan',
                'title' => 'Jumlah Entitas Master',
                'icon' => 'database',
                'color' => 'primary',
                'badge' => 'Unit',
                'col' => 'col-lg-4 col-12',
                'type' => 'bar',
                'data' => ['labels' => array_keys($ringkasan), 'data' => array_values($ringkasan)],
                'opts' => ['horizontal' => true],
            ],
            [
                'id' => 'stok',
                'title' => 'Komposisi Stok per Produk',
                'icon' => 'gas-pump',
                'color' => 'info',
                'badge' => 'Liter',
                'col' => 'col-lg-4 col-12',
                'type' => 'doughnut',
                'data' => ['labels' => $stok->pluck('nama_produk'), 'data' => $stok->pluck('total')],
                'opts' => ['unit' => 'L'],
            ],
            [
                'id' => 'harga',
                'title' => 'Harga Jual per Produk',
                'icon' => 'tags',
                'color' => 'success',
                'badge' => 'Rupiah',
                'col' => 'col-lg-4 col-12',
                'type' => 'bar',
                'data' => ['labels' => $harga->pluck('nama_produk'), 'data' => $harga->pluck('harga')],
                'opts' => ['money' => true],
            ],
        ];

        return Inertia::render('Menu/Index', [
            'title' => 'Master Data',
            'description' => 'Komposisi aset, persediaan, dan harga yang menjadi dasar operasional SPBU.',
            'menus' => $allMenu,
            'charts' => $charts,
        ]);
    }
}
