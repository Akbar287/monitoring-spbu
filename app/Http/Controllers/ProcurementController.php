<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcurementController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Perencanaan",
                "icon" => "th",
                "url" => route('perencanaan.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Pembelian",
                "icon" => "tint",
                "url" => route('pembelian.index'),
                "color" => "success"
            ],
            [
                "nama" => "Pembayaran",
                "icon" => "upload",
                "url" => route('pembayaran.index'),
                "color" => "danger"
            ],
            [
                "nama" => "MS2",
                "icon" => "percent",
                "url" => route('ms2.pengiriman'),
                "color" => "warning"
            ],
            [
                "nama" => "Delivery",
                "icon" => "file",
                "url" => route('pengiriman.index'),
                "color" => "info"
            ]
        ];

        return view('procurement/index', compact('allMenu'));
    }
}
