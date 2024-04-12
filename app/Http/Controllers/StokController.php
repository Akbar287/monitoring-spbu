<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StokController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Monitoring Stok",
                "icon" => "cubes",
                "url" => route('monitoring.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Penerimaan BBM",
                "icon" => "arrow-down",
                "url" => route('penerimaan.index'),
                "color" => "info"
            ],
            [
                "nama" => "Stok Taking",
                "icon" => "thermometer-full",
                "url" => route('stok-taking.index'),
                "color" => "danger"
            ],
            [
                "nama" => "Stok Transfer",
                "icon" => "flask",
                "url" => route('stok-transfer.index'),
                "color" => "success"
            ],
        ];
        return view('stok/index', compact('allMenu'));
    }
}
