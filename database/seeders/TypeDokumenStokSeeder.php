<?php

namespace Database\Seeders;

use App\Models\TypeDokumenStok;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeDokumenStokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cek = TypeDokumenStok::count();
        if ($cek == 0) {
            $temp = [
                [
                    "type_movement" => "Good Receive Penerimaan",
                    "deskripsi" => "Penerimaan Stok BBM memengaruhi Dokumen Stok secara positif"
                ],
                [
                    "type_movement" => "Good Issue Sales",
                    "deskripsi" => "Pengeluaran Stok BBM memengaruhi Dokumen Stok secara negatif"
                ],
                [
                    "type_movement" => "Good Issue Tera",
                    "deskripsi" => "Pengeluaran Stok BBM melalui Tera memengaruhi Dokumen Stok secara negatif"
                ],
                [
                    "type_movement" => "Good Return Tera",
                    "deskripsi" => "Penerimaan Stok BBM melalui Tera memengaruhi Dokumen Stok secara positif"
                ],
                [
                    "type_movement" => "Stok Taking Good Receive Penerimaan",
                    "deskripsi" => "Perhitungan stok sebelum penerimaan BBM"
                ],
                [
                    "type_movement" => "Stok Taking",
                    "deskripsi" => "Perhitungan stok"
                ],
                [
                    "type_movement" => "Loss",
                    "deskripsi" => "Perhitungan Kelebihan dan / atau Kekurangan Stok diluar perhitungan"
                ],
                [
                    "type_movement" => "Commit Change From User",
                    "deskripsi" => "Perubahan Stok dari User"
                ],
                [
                    "type_movement" => "Good Transfer Send",
                    "deskripsi" => "Pengiriman Stok dari Dombak"
                ],
                [
                    "type_movement" => "Good Transfer Receive",
                    "deskripsi" => "Penerimaan Stok dari Dombak"
                ],
            ];
            foreach ($temp as $t) {
                $type = new TypeDokumenStok();
                $type->type_movement = $t['type_movement'];
                $type->deskripsi = $t['deskripsi'];
                $type->save();
            }
        }
    }
}
