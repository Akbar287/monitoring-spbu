<?php

namespace Database\Seeders;

use App\Models\Hari;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HariSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cek = Hari::count();
        if ($cek == 0) {
            $hari = [
                [
                    "nama_hari" => "Senin",
                    "hari_kerja" => true,
                    "keterangan" => "Hari senin"
                ],
                [
                    "nama_hari" => "Selasa",
                    "hari_kerja" => true,
                    "keterangan" => "Hari Selasa"
                ],
                [
                    "nama_hari" => "Rabu",
                    "hari_kerja" => true,
                    "keterangan" => "Hari Rabu"
                ],
                [
                    "nama_hari" => "Kamis",
                    "hari_kerja" => true,
                    "keterangan" => "Hari Kamis"
                ],
                [
                    "nama_hari" => "Jumat",
                    "hari_kerja" => true,
                    "keterangan" => "Hari Jumat"
                ],
                [
                    "nama_hari" => "Sabtu",
                    "hari_kerja" => false,
                    "keterangan" => "Hari Sabtu"
                ],
                [
                    "nama_hari" => "Minggu",
                    "hari_kerja" => false,
                    "keterangan" => "Hari Minggu"
                ],
            ];

            foreach ($hari as $h) {
                $hari = new Hari();
                $hari->nama_hari = $h['nama_hari'];
                $hari->hari_kerja = $h['hari_kerja'];
                $hari->keterangan = $h['keterangan'];
                $hari->save();
            }
        }
    }
}
