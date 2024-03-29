<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SetoranController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Open Penjualan",
                "icon" => "pen",
                "url" => route('setoran.open'),
                "color" => "primary"
            ],
            [
                "nama" => "Penitipan Setoran",
                "icon" => "credit-card",
                "url" => route('setoran.penitipan'),
                "color" => "info"
            ],
            [
                "nama" => "Closing Penjualan",
                "icon" => "file",
                "url" => route('setoran.closing'),
                "color" => "success"
            ]
        ];
        return view('setoran/index', compact('allMenu'));
    }
}
