<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeraController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Peminjaman",
                "icon" => "th",
                "url" => route('tera.peminjaman'),
                "color" => "primary"
            ],
            [
                "nama" => "Pengembalian",
                "icon" => "filter",
                "url" => route('tera.pengembalian'),
                "color" => "secondary"
            ]
        ];
        $charts = [
            [
                'id' => 'jumlah',
                'title' => 'Jumlah Tera per Bulan',
                'icon' => 'clipboard-check',
                'color' => 'primary',
                'badge' => 'Kegiatan',
                'col' => 'col-lg-6 col-12',
                'type' => 'bar',
                'data' => Charts::monthly('tera', 'tanggal', 'COUNT(*)'),
                'opts' => ['colorEach' => false, 'colorIndex' => 0, 'label' => 'Tera'],
            ],
            [
                'id' => 'nilai',
                'title' => 'Nilai Tera per Bulan',
                'icon' => 'coins',
                'color' => 'success',
                'badge' => 'Rupiah',
                'col' => 'col-lg-6 col-12',
                'type' => 'line',
                'data' => Charts::monthly('tera', 'tanggal', 'SUM(grand_total)'),
                'opts' => ['money' => true, 'label' => 'Nilai Tera'],
            ],
        ];

        return Inertia::render('Menu/Index', [
            'title' => 'Tera',
            'description' => 'Frekuensi dan nilai kegiatan tera dalam 12 bulan terakhir.',
            'menus' => $allMenu,
            'charts' => $charts,
        ]);
    }
}
