<?php

namespace Database\Seeders;

use App\Models\SatuanUkurTinggi;
use App\Models\SatuanUkurVolume;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanUkurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cekTinggi = SatuanUkurTinggi::count();
        if ($cekTinggi == 0) {
            $tinggi = [
                [
                    "nama_satuan" => "Centimeter",
                    "singkatan" => "cm"
                ],
            ];
            foreach ($tinggi as $t) {
                $satuan = new SatuanUkurTinggi();
                $satuan->nama_satuan = $t['nama_satuan'];
                $satuan->singkatan = $t['singkatan'];
                $satuan->save();
            }
        }

        $cekVolume = SatuanUkurVolume::count();
        if ($cekVolume == 0) {
            $volume = [
                [
                    "nama_satuan" => "Ton",
                    "singkatan" => "ton"
                ],
                [
                    "nama_satuan" => "Kiloliter",
                    "singkatan" => "Kl"
                ],
                [
                    "nama_satuan" => "Liter",
                    "singkatan" => "ltr"
                ],
            ];
            foreach ($volume as $t) {
                $satuan = new SatuanUkurVolume();
                $satuan->nama_satuan = $t['nama_satuan'];
                $satuan->singkatan = $t['singkatan'];
                $satuan->save();
            }
        }
    }
}
