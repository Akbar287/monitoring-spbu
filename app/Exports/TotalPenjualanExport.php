<?php

namespace App\Exports;

use App\Models\StandMeter;
use Maatwebsite\Excel\Concerns\FromCollection;

class TotalPenjualanExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    // public function collection()
    // {
    //     return PajakPembelian::all();
    // }

    protected $totalPenjualanExport;

    public function __construct(array $totalPenjualanExport)
    {
        $this->totalPenjualanExport = $totalPenjualanExport;
    }
    public function collection()
    {
        return collect($this->totalPenjualanExport);
    }
    public function model(array $row)
    {
        return new StandMeter([
            'Tanggal' => $row[0],
            'Total Penjualan' => $row[1],
            'Harga Satuan' => $row[2],
            'Jumlah' => $row[3]
        ]);
    }
}
