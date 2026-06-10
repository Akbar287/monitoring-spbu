<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'ringkasan' => [
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
            'jabatanLevel' => [
                'labels' => $jabatanLevel->pluck('nama_level'),
                'data' => $jabatanLevel->pluck('total'),
            ],
            'hari' => [
                'labels' => ['Hari Kerja', 'Hari Libur'],
                'data' => [
                    DB::table('hari')->where('hari_kerja', 1)->count(),
                    DB::table('hari')->where('hari_kerja', 0)->count(),
                ],
            ],
        ];

        return view('konfigurasi/index', compact('allMenu', 'charts'));
    }
}
