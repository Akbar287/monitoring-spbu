<?php

namespace App\Imports;

use App\Models\Penjadwalan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class JadwalKerjaImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        return Penjadwalan::all();
    }
    public function model(array $row)
    {
        return new Penjadwalan([
            'member_id' => $row[0],
            'jabatan_id' => $row[1],
            'tanggal' => $row[2],
            'jam_kerja' => $row[3],
            'nozzle_id' => $row[4]
        ]);
    }
}
