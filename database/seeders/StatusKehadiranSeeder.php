<?php

namespace Database\Seeders;

use App\Models\StatusKehadiran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusKehadiranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cek = StatusKehadiran::count();
        if ($cek == 0) {
            $temp = ["Dijadwalkan", "Hadir", "Cuti", "Sakit", "Tanpa Keterangan"];
            foreach ($temp as $t) {
                $s = new StatusKehadiran();
                $s->nama_kehadiran = $t;
                $s->save();
            }
        }
    }
}
