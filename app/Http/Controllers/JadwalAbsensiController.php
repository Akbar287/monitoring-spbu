<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class JadwalAbsensiController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Penjadwalan Kerja",
                "icon" => "calendar",
                "url" => route('jadwal-kerja.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Absensi",
                "icon" => "users",
                "url" => route('absensi.index'),
                "color" => "info"
            ],
            [
                "nama" => "Laporan Absensi",
                "icon" => "file",
                "url" => route('laporan-absensi.index'),
                "color" => "success"
            ]
        ];
        // Presensi per status kehadiran
        $presensi = DB::table('presensi')
            ->join('status_presensi', 'status_presensi.status_presensi_id', 'presensi.status_presensi_id')
            ->select('status_presensi.nama_status', DB::raw('COUNT(*) as total'))
            ->groupBy('status_presensi.nama_status')
            ->get();

        // Penjadwalan per status kehadiran
        $penjadwalan = DB::table('penjadwalan')
            ->join('status_kehadiran', 'status_kehadiran.status_kehadiran_id', 'penjadwalan.status_kehadiran_id')
            ->select('status_kehadiran.nama_kehadiran', DB::raw('COUNT(*) as total'))
            ->groupBy('status_kehadiran.nama_kehadiran')
            ->get();

        $charts = [
            [
                'id' => 'presensi',
                'title' => 'Presensi per Status',
                'icon' => 'user-check',
                'color' => 'info',
                'col' => 'col-lg-4 col-12',
                'type' => 'doughnut',
                'data' => ['labels' => $presensi->pluck('nama_status'), 'data' => $presensi->pluck('total')],
            ],
            [
                'id' => 'penjadwalan',
                'title' => 'Penjadwalan per Status',
                'icon' => 'calendar-check',
                'color' => 'primary',
                'col' => 'col-lg-4 col-12',
                'type' => 'doughnut',
                'data' => ['labels' => $penjadwalan->pluck('nama_kehadiran'), 'data' => $penjadwalan->pluck('total')],
            ],
            [
                'id' => 'bulanan',
                'title' => 'Presensi per Bulan',
                'icon' => 'chart-line',
                'color' => 'success',
                'badge' => 'Presensi',
                'col' => 'col-lg-4 col-12',
                'type' => 'bar',
                'data' => Charts::monthly('presensi', 'tanggal', 'COUNT(*)'),
                'opts' => ['colorEach' => false, 'colorIndex' => 3, 'label' => 'Presensi'],
            ],
        ];

        return Inertia::render('Menu/Index', [
            'title' => 'Jadwal & Absensi',
            'description' => 'Komposisi status presensi, penjadwalan, dan tren kehadiran pegawai.',
            'menus' => $allMenu,
            'charts' => $charts,
        ]);
    }
}
