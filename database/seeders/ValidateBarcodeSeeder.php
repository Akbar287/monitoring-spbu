<?php

namespace Database\Seeders;

use App\Models\ValidateBarcode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ValidateBarcodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cek = ValidateBarcode::count();
        if ($cek == 0) {
            $temp = [
                [
                    "is_expired" => "15",
                    "is_active" => true
                ]
            ];
            foreach ($temp as $t) {
                $type = new ValidateBarcode();
                $type->is_expired = $t['is_expired'];
                $type->is_active = $t['is_active'];
                $type->save();
            }
        }
    }
}
