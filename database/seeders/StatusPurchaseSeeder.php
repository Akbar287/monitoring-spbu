<?php

namespace Database\Seeders;

use App\Models\StatusPurchase;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusPurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cek = StatusPurchase::count();
        if ($cek == 0) {
            $temp = [
                [
                    "nama_status" => "Rencana",
                    "deskripsi" => "Purchase Requisition",
                    "is_aktif" => true,
                ],
                [
                    "nama_status" => "Pembelian",
                    "deskripsi" => "Purchase Order",
                    "is_aktif" => true,
                ],
                [
                    "nama_status" => "Pembayaran",
                    "deskripsi" => "Invoice",
                    "is_aktif" => true,
                ],
                [
                    "nama_status" => "MS2",
                    "deskripsi" => "Stok Pertamina",
                    "is_aktif" => true,
                ],
                [
                    "nama_status" => "Pengiriman",
                    "deskripsi" => "Delivery",
                    "is_aktif" => true,
                ],
                [
                    "nama_status" => "Penerimaan",
                    "deskripsi" => "Good Receive",
                    "is_aktif" => true,
                ],
            ];
            foreach ($temp as $t) {
                $status = new StatusPurchase();
                $status->nama_status = $t['nama_status'];
                $status->deskripsi = $t['deskripsi'];
                $status->is_aktif = $t['is_aktif'];
                $status->save();
            }
        }
    }
}
