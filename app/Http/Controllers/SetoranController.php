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
            ],
            [
                "nama" => "Setoran ke Bank",
                "icon" => "building",
                "url" => route('setoran.setoran_bank.index_setoran_bank_penjualan'),
                "color" => "primary"
            ]
        ];
        return view('setoran/index', compact('allMenu'));
    }
}
