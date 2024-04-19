<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        return view('jadwal_absensi/index', compact('allMenu'));
    }
}
