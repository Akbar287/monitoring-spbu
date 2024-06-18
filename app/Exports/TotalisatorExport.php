<?php

namespace App\Exports;

use App\Models\StandMeter;
use Maatwebsite\Excel\Concerns\FromCollection;

class TotalisatorExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    // public function collection()
    // {
    //     return PajakPembelian::all();
    // }

    protected $totalisatorExport;

    public function __construct(array $totalisatorExport)
    {
        $this->totalisatorExport = $totalisatorExport;
    }
    public function collection()
    {
        return collect($this->totalisatorExport);
    }
    public function model(array $row)
    {
        return new StandMeter([
            'Tanggal' => $row[0],
            'Shift' => $row[1],
            'Nama Nozzle' => $row[2],
            'Produk' => $row[3],
            'Stand Meter Awal' => $row[4],
            'Stand Meter Akhir' => $row[5],
            'Harga Jual' => $row[6],
            'Total Penjualan' => $row[7]
        ]);
    }
}
