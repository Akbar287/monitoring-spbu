<?php

namespace Database\Seeders;

use App\Models\JenisLosses;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JenisLossSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cek = JenisLosses::count();
        if ($cek == 0) {
            $temp = [
                [
                    "jenis_loss" => "Good Issue Penerimaan",
                    "deskripsi" => "Loss terjadi ketika Penerimaan BBM"
                ],
                [
                    "jenis_loss" => "Stok Taking",
                    "deskripsi" => "Loss terjadi selama jam kerja berlangsung"
                ],
                [
                    "jenis_loss" => "Good Transfer",
                    "deskripsi" => "Loss terjadi selama Pemindahan Stok"
                ]
            ];

            foreach ($temp as $t) {
                $jenis = new JenisLosses();
                $jenis->jenis_loss = $t['jenis_loss'];
                $jenis->deskripsi = $t['deskripsi'];
                $jenis->save();
            }
        }
    }
}
