<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;

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
            'jumlah' => Charts::monthly('tera', 'tanggal', 'COUNT(*)'),
            'nilai' => Charts::monthly('tera', 'tanggal', 'SUM(grand_total)'),
        ];

        return view('tera/index', compact('allMenu', 'charts'));
    }
}
