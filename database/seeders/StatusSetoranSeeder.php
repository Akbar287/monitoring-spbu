<?php

namespace Database\Seeders;

use App\Models\StatusSetoran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSetoranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = StatusSetoran::count();

        if ($status == 0) {
            $temp = [
                [
                    'nama_status' => 'Open Penjualan'
                ],
                [
                    'nama_status' => 'Penitipan Setoran'
                ],
                [
                    'nama_status' => 'Closing Penjualan'
                ],
                [
                    'nama_status' => 'Setoran Ke Bank'
                ],
                [
                    'nama_status' => 'Selesai'
                ]
            ];

            foreach ($temp as $t) {
                StatusSetoran::create(['nama_status' => $t['nama_status']]);
            }
        }
    }
}
