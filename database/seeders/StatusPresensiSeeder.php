<?php

namespace Database\Seeders;

use App\Models\StatusPresensi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusPresensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cek = StatusPresensi::count();
        if ($cek == 0) {
            $statusPresensi = [
                [
                    "nama_status" => "On Time",
                    "keterangan" => "Tepat Waktu"
                ],
                [
                    "nama_status" => "Terlambat",
                    "keterangan" => "Masuk Melewati Batas Waktu"
                ],
                [
                    "nama_status" => "Sakit",
                    "keterangan" => "Tidak Masuk Karena Sakit"
                ],
                [
                    "nama_status" => "Izin",
                    "keterangan" => "Tidak Masuk Dengan Keterangan"
                ],
                [
                    "nama_status" => "Alpa",
                    "keterangan" => "Tidak Masuk Tanpa Keterangan"
                ],
            ];

            foreach ($statusPresensi as $st) {
                $status = new StatusPresensi();
                $status->nama_status = $st['nama_status'];
                $status->keterangan = $st['keterangan'];
                $status->save();
            }
        }
    }
}
