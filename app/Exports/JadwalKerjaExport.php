<?php

namespace App\Exports;

use App\Models\Penjadwalan;
use Maatwebsite\Excel\Concerns\FromCollection;

class JadwalKerjaExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $jadwalKerja;

    public function __construct(array $jadwalKerja)
    {
        $this->jadwalKerja = $jadwalKerja;
    }
    public function collection()
    {
        return collect($this->jadwalKerja);
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
