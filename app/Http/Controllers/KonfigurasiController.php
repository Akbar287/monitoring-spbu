<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KonfigurasiController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "SPBU",
                "icon" => "th",
                "url" => route('spbu.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Divisi",
                "icon" => "tint",
                "url" => route('divisi.index'),
                "color" => "success"
            ],
            [
                "nama" => "Level",
                "icon" => "upload",
                "url" => route('level.index'),
                "color" => "danger"
            ],
            [
                "nama" => "Jabatan",
                "icon" => "percent",
                "url" => route('jabatan.index'),
                "color" => "warning"
            ],
            [
                "nama" => "Hari",
                "icon" => "file",
                "url" => route('hari.index'),
                "color" => "info"
            ],
            [
                "nama" => "Jam Kerja",
                "icon" => "wrench",
                "url" => route('jam_kerja.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Import",
                "icon" => "upload",
                "url" => route('import.index'),
                "color" => "info"
            ]
        ];
        // Jabatan per level
        $jabatanLevel = DB::table('jabatan')
            ->join('level', 'level.level_id', 'jabatan.level_id')
            ->whereNull('jabatan.deleted_at')
            ->select('level.nama_level', DB::raw('COUNT(*) as total'))
            ->groupBy('level.nama_level')
            ->orderBy('level.nama_level')
            ->get();

        $charts = [
            [
                'id' => 'ringkasan',
                'title' => 'Jumlah Data Konfigurasi',
                'icon' => 'cogs',
                'color' => 'primary',
                'badge' => 'Data',
                'col' => 'col-lg-5 col-12',
                'type' => 'bar',
                'data' => [
                    'labels' => ['SPBU', 'Divisi', 'Level', 'Jabatan', 'Hari', 'Jam Kerja'],
                    'data' => [
                        DB::table('spbu')->whereNull('deleted_at')->count(),
                        DB::table('divisi')->whereNull('deleted_at')->count(),
                        DB::table('level')->whereNull('deleted_at')->count(),
                        DB::table('jabatan')->whereNull('deleted_at')->count(),
                        DB::table('hari')->count(),
                        DB::table('jam_kerja')->count(),
                    ],
                ],
                'opts' => ['horizontal' => true],
            ],
            [
                'id' => 'jabatanLevel',
                'title' => 'Jabatan per Level',
                'icon' => 'sitemap',
                'color' => 'warning',
                'badge' => 'Jabatan',
                'col' => 'col-lg-4 col-12',
                'type' => 'bar',
                'data' => ['labels' => $jabatanLevel->pluck('nama_level'), 'data' => $jabatanLevel->pluck('total')],
                'opts' => ['colorEach' => false, 'colorIndex' => 2, 'label' => 'Jabatan'],
            ],
            [
                'id' => 'hari',
                'title' => 'Hari Kerja vs Libur',
                'icon' => 'calendar-day',
                'color' => 'info',
                'col' => 'col-lg-3 col-12',
                'type' => 'doughnut',
                'data' => [
                    'labels' => ['Hari Kerja', 'Hari Libur'],
                    'data' => [
                        DB::table('hari')->where('hari_kerja', 1)->count(),
                        DB::table('hari')->where('hari_kerja', 0)->count(),
                    ],
                ],
            ],
        ];

        return Inertia::render('Menu/Index', [
            'title' => 'Konfigurasi',
            'description' => 'Struktur organisasi, jabatan, dan pengaturan hari kerja.',
            'menus' => $allMenu,
            'charts' => $charts,
        ]);
    }
}
