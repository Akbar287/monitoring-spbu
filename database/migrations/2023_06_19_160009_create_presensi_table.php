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
        Schema::create('presensi', function (Blueprint $table) {
            $table->uuid('presensi_id')->primary();
            $table->uuid('status_presensi_id');
            $table->uuid('mesin_presensi_id');
            $table->uuid('jam_kerja_id');
            $table->uuid('member_id');
            $table->date('tanggal');
            $table->timestamp('waktu_datang');
            $table->timestamp('waktu_selesai')->nullable();
            $table->boolean('is_verified');
            $table->boolean('is_aktif');
            $table->timestamp('terlambat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('status_presensi_id')->references('status_presensi_id')->on('status_presensi')->cascadeOnDelete();
            $table->foreign('mesin_presensi_id')->references('mesin_presensi_id')->on('mesin_presensi')->cascadeOnDelete();
            $table->foreign('jam_kerja_id')->references('jam_kerja_id')->on('jam_kerja')->cascadeOnDelete();
            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
