<?php

namespace Database\Seeders;

use App\Models\Ktp;
use App\Models\Member;
use App\Models\StatusMember;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cek = Member::count();

        if ($cek == 0) {
            $data = [
                [
                    "nik" => "3175098761234567",
                    "nama" => "Akbar",
                    "jenis_kelamin" => "pria",
                    "tempat_lahir" => "Jakarta",
                    "tanggal_lahir" => "2023-06-05",
                    "verified" => true,
                    "email" => "akbar@email.com",
                    "no_hp" => "081298337465",
                    "no_wa" => "081298337465",
                    "avatar" => "default.png",
                    "bergabung_sejak" => "2022-05-20",
                    "username" => "Akbar287",
                    "password" => Hash::make("Akbar287"),
                    "is_aktif" => true
                ],
                [
                    "nik" => "1234567890123450",
                    "nama" => "Reza Rahardian",
                    "jenis_kelamin" => "pria",
                    "tempat_lahir" => "Jakarta",
                    "tanggal_lahir" => "2023-06-25",
                    "verified" => true,
                    "email" => "reza@email.com",
                    "no_hp" => "081298337565",
                    "no_wa" => "081298337565",
                    "avatar" => "default.png",
                    "bergabung_sejak" => "2022-05-22",
                    "username" => "Reza12345",
                    "password" => Hash::make("Reza12345"),
                    "is_aktif" => true
                ],
                [
                    "nik" => "1234567890123456",
                    "nama" => "Rena Mizunashi",
                    "jenis_kelamin" => "wanita",
                    "tempat_lahir" => "Surabaya",
                    "tanggal_lahir" => "2023-07-05",
                    "verified" => true,
                    "email" => "rena@email.com",
                    "no_hp" => "081298327465",
                    "no_wa" => "081298327465",
                    "avatar" => "default.png",
                    "bergabung_sejak" => "2022-05-24",
                    "username" => "Rena12345",
                    "password" => Hash::make("Rena12345"),
                    "is_aktif" => true
                ],
            ];

            $statusMember = StatusMember::where('nama_status', 'Aktif')->first();
            foreach ($data as $d) {
                $ktp = new Ktp();
                $ktp->nik = $d['nik'];
                $ktp->nama = $d['nama'];
                $ktp->jenis_kelamin = $d['jenis_kelamin'];
                $ktp->tempat_lahir = $d['tempat_lahir'];
                $ktp->tanggal_lahir = $d['tanggal_lahir'];
                $ktp->verified = $d['verified'];
                $ktp->save();

                $member = $ktp->member()->create([
                    "status_member_id" => $statusMember->status_member_id,
                    "email" => $d["email"],
                    "no_hp" => $d["no_hp"],
                    "no_wa" => $d["no_wa"],
                    "avatar" => $d["avatar"],
                    "bergabung_sejak" => $d["bergabung_sejak"]
                ]);

                $userlogin = $member->user_login()->create([
                    "username" => $d['username'],
                    "password" => $d['password'],
                    "is_aktif" => $d['is_aktif']
                ]);
            }
        }
    }
}
