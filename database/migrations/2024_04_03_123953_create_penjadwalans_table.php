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
        Schema::create('penjadwalan', function (Blueprint $table) {
            $table->uuid('penjadwalan_id')->primary();
            $table->uuid('status_kehadiran_id');
            $table->uuid('jabatan_id');
            $table->uuid('member_id');
            $table->uuid('jam_kerja_id');
            $table->date('tanggal');
            $table->text('deskripsi')->nullable();
            $table->timestamp('attended')->nullable();
            $table->boolean('is_attended')->default(false);
            $table->boolean('is_confirm')->default(false);
            $table->timestamps();

            $table->foreign('status_kehadiran_id')->references('status_kehadiran_id')->on('status_kehadiran')->cascadeOnDelete();
            $table->foreign('jabatan_id')->references('jabatan_id')->on('jabatan')->cascadeOnDelete();
            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
            $table->foreign('jam_kerja_id')->references('jam_kerja_id')->on('jam_kerja')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjadwalans');
    }
};
