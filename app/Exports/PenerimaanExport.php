<?php

namespace App\Exports;

use App\Models\Penerimaan;
use Maatwebsite\Excel\Concerns\FromCollection;

class PenerimaanExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $penerimaan;

    public function __construct(array $penerimaan)
    {
        $this->penerimaan = $penerimaan;
    }
    public function collection()
    {
        return collect($this->penerimaan);
    }
    public function model(array $row)
    {
        return new Penerimaan([
            'tanggal' => $row[0],
            'jam_kerja' => $row[1],
            'jam' => $row[2],
            'nama' => $row[3],
            'no_pol' => $row[4],
            'stok_awal' => $row[5],
            'stok_dibeli' => $row[6],
            'stok_akhir' => $row[7],
            'total_aktual' => $row[8],
            'total' => $row[9]
        ]);
    }
}
