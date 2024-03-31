<?php

namespace App\Exports;

use App\Models\PajakPembelian;
use Maatwebsite\Excel\Concerns\FromCollection;

class PajakPembelianExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    // public function collection()
    // {
    //     return PajakPembelian::all();
    // }

    protected $pajakPembelian;

    public function __construct(array $pajakPembelian)
    {
        $this->pajakPembelian = $pajakPembelian;
    }
    public function collection()
    {
        return collect($this->pajakPembelian);
    }
    public function model(array $row)
    {
        return new PajakPembelian([
            'Tanggal' => $row[0],
            'Kode Pembelian' => $row[1],
            'PPN' => $row[2],
            'PPH' => $row[3],
            'PPBKB' => $row[4],
            'Total Pajak' => $row[5]
        ]);
    }
}
