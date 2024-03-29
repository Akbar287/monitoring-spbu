<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        return view('konfigurasi/index', compact('allMenu'));
    }
}
