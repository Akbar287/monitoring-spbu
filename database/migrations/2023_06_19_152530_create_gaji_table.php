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
        Schema::create('gaji', function (Blueprint $table) {
            $table->uuid('gaji_id')->primary();
            $table->uuid('jabatan_id');
            $table->date('tanggal_berlaku');
            $table->decimal('total_gaji', 16, 6);
            $table->decimal('potongan_pajak', 16, 6);
            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif');
            $table->timestamps();

            $table->foreign('jabatan_id')->references('jabatan_id')->on('jabatan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaji');
    }
};
