<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jam_kerja', function (Blueprint $table) {
            $table->uuid('jam_kerja_id')->primary();
            $table->uuid('spbu_id');
            $table->string('nama_jam_kerja', 32);
            $table->time('batas_awal_in');
            $table->time('batas_akhir_in');
            $table->time('jam_datang_in');
            $table->time('jam_pulang_out');
            $table->time('akhir_out');
            $table->time('jam_istirahat');
            $table->time('masuk_istirahat');
            $table->time('batas_telat');
            $table->integer('urutan');
            $table->timestamps();

            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jam_kerja');
    }
};
