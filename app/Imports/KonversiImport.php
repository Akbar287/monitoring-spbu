<?php

namespace App\Imports;

use App\Models\KonversiExcel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class KonversiImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        return KonversiExcel::all();
    }
    public function model(array $row)
    {
        return new KonversiExcel([
            'tinggi' => $row[0],
            'volume' => $row[1],
        ]);
    }
}
