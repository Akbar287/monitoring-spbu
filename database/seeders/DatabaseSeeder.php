<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(HariSeeder::class);
        $this->call(JenisLossSeeder::class);
        $this->call(SatuanUkurSeeder::class);
        $this->call(StatusPresensiSeeder::class);
        $this->call(StatusPurchaseSeeder::class);
        $this->call(TypeDokumenStokSeeder::class);
        $this->call(StatusMemberSeeder::class);
        $this->call(MemberSeeder::class);
        $this->call(StatusSetoranSeeder::class);
        $this->call(StatusKehadiranSeeder::class);
    }
}
