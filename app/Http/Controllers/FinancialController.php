<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FinancialController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Petty Cash",
                "icon" => "th",
                "url" => route('financial.petty-cash'),
                "color" => "primary"
            ],
            [
                "nama" => "Margin",
                "icon" => "tint",
                "url" => route('margin.index'),
                "color" => "success"
            ],
            [
                "nama" => "Laba Rugi",
                "icon" => "upload",
                "url" => route('laba-rugi.index'),
                "color" => "danger"
            ],
            [
                "nama" => "Neraca",
                "icon" => "percent",
                "url" => route('neraca.index'),
                "color" => "warning"
            ],
            [
                "nama" => "Buku Besar",
                "icon" => "file",
                "url" => route('buku-besar.index'),
                "color" => "info"
            ],
            [
                "nama" => "Arus Kas",
                "icon" => "wrench",
                "url" => route('arus-kas.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Perubahan Modal",
                "icon" => "upload",
                "url" => route('perubahan-modal.index'),
                "color" => "info"
            ]
        ];
        return view('financial/index', compact('allMenu'));
    }
}
