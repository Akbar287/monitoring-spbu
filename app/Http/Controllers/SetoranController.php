<?php

namespace App\Http\Controllers;

use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // Penjualan per status setoran
        $status = DB::table('penjualan')
            ->join('status_setoran', 'status_setoran.status_setoran_id', 'penjualan.status_setoran_id')
            ->select('status_setoran.nama_status', DB::raw('COUNT(*) as total'))
            ->groupBy('status_setoran.nama_status')
            ->get();

        // Total penjualan per jam kerja
        $jamKerja = DB::table('penjualan')
            ->join('jam_kerja', 'jam_kerja.jam_kerja_id', 'penjualan.jam_kerja_id')
            ->select('jam_kerja.nama_jam_kerja', DB::raw('SUM(penjualan.total_penjualan) as total'))
            ->groupBy('jam_kerja.nama_jam_kerja')
            ->orderBy('jam_kerja.nama_jam_kerja')
            ->get();

        $charts = [
            'bulanan' => Charts::monthly('penjualan', 'tanggal', 'SUM(total_penjualan)'),
            'status' => [
                'labels' => $status->pluck('nama_status'),
                'data' => $status->pluck('total'),
            ],
            'jamKerja' => [
                'labels' => $jamKerja->pluck('nama_jam_kerja'),
                'data' => $jamKerja->pluck('total'),
            ],
        ];

        return view('setoran/index', compact('allMenu', 'charts'));
    }
}
