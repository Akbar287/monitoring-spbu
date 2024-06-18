<?php

namespace App\Exports;

use App\Models\StandMeter;
use Maatwebsite\Excel\Concerns\FromCollection;

class StokDombakExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    // public function collection()
    // {
    //     return PajakPembelian::all();
    // }

    protected $stokDombakExport;

    public function __construct(array $stokDombakExport)
    {
        $this->stokDombakExport = $stokDombakExport;
    }
    public function collection()
    {
        return collect($this->stokDombakExport);
    }
    public function model(array $row)
    {
        return new StandMeter([
            'Tanggal' => $row[0],
            'Shift' => $row[1],
            'Jam' => $row[2],
            'Stok Awal' => $row[3],
            'Penerimaan BBM Aktual' => $row[4],
            'Penjualan' => $row[5],
            'Stok AKhir Teoritis' => $row[6],
            'Stok Akhir Dipping' => $row[7],
            'Total Losses' => $row[8]
        ]);
    }
}
