<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

/**
 * Penyimpanan pengaturan tingkat sistem (kunci/nilai). Saat ini dipakai
 * halaman Set Up untuk menyimpan tanggal mulai operasi dan penanda bahwa
 * pengisian awal sudah dituntaskan — lihat App\Support\Setup.
 *
 * PENTING: instalasi yang SUDAH berjalan tidak boleh tiba-tiba terkunci di
 * halaman set up. Karena itu migrasi ini langsung menandai set up selesai
 * bila database ternyata sudah berisi data operasional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_sistem', function (Blueprint $table) {
            $table->char('pengaturan_sistem_id', 36)->primary();
            $table->string('kunci', 64)->unique();
            $table->text('nilai')->nullable();
            $table->timestamps();
        });

        if ($this->sudahBerjalan()) {
            $sekarang = now();

            DB::table('pengaturan_sistem')->insert([
                [
                    'pengaturan_sistem_id' => Uuid::uuid4()->toString(),
                    'kunci' => 'setup_selesai_at',
                    'nilai' => $sekarang->toDateTimeString(),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ],
                [
                    'pengaturan_sistem_id' => Uuid::uuid4()->toString(),
                    'kunci' => 'setup_dilewati',
                    'nilai' => 'sistem sudah berisi data saat migrasi dijalankan',
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sistem');
    }

    /**
     * Dianggap sudah berjalan bila tiga pilar utamanya sudah terisi:
     * ada SPBU, ada produk, dan ada pegawai.
     */
    private function sudahBerjalan(): bool
    {
        foreach (['spbu', 'produk', 'member'] as $tabel) {
            if (! Schema::hasTable($tabel) || DB::table($tabel)->count() === 0) {
                return false;
            }
        }

        return true;
    }
};
