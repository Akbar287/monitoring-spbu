<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MasterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $allMenu = [
            [
                "nama" => "Aset",
                "icon" => "th",
                "url" => route('aset.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Fasilitas",
                "icon" => "filter",
                "url" => route('menu.procurement'),
                "color" => "secondary"
            ],
            [
                "nama" => "Manajemen Persediaan",
                "icon" => "tint",
                "url" => route('fasilitas.index'),
                "color" => "success"
            ],
            [
                "nama" => "Produk",
                "icon" => "upload",
                "url" => route('produk.index'),
                "color" => "danger"
            ],
            [
                "nama" => "Pegawai",
                "icon" => "percent",
                "url" => route('member.index'),
                "color" => "warning"
            ],
            [
                "nama" => "Harga",
                "icon" => "percent",
                "url" => route('harga.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Dombak",
                "icon" => "file",
                "url" => route('dombak.index'),
                "color" => "info"
            ],
            [
                "nama" => "Payung",
                "icon" => "wrench",
                "url" => route('payung.index'),
                "color" => "primary"
            ],
            [
                "nama" => "Dispenser",
                "icon" => "percent",
                "url" => route('dispenser.index'),
                "color" => "warning"
            ],
            [
                "nama" => "Nozzle",
                "icon" => "file",
                "url" => route('nozzle.index'),
                "color" => "info"
            ],
        ];
        return view('master/index', compact('allMenu'));
    }
}
