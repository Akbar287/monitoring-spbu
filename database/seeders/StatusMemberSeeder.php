<?php

namespace Database\Seeders;

use App\Models\StatusMember;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cek = StatusMember::count();
        if ($cek == 0) {
            $temp = [
                [
                    "nama_status" => "Aktif",
                    "keterangan" => "Member tersebut dapat Login ke sistem"
                ],
                [
                    "nama_status" => "Ditangguhkan",
                    "keterangan" => "Member tersebut tidak dapat Login ke sistem"
                ],
                [
                    "nama_status" => "Tidak Aktif",
                    "keterangan" => "Member tersebut tidak dapat Login ke sistem"
                ],
            ];
            foreach ($temp as $t) {
                $s = new StatusMember();
                $s->nama_status = $t['nama_status'];
                $s->keterangan = $t['keterangan'];
                $s->save();
            }
        }
    }
}
