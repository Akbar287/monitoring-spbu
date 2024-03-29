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
        Schema::create('konversi_dokumen_stok', function (Blueprint $table) {
            $table->uuid('konversi_id');
            $table->uuid('dokumen_stok_id');
            $table->primary(['konversi_id', 'dokumen_stok_id']);

            $table->foreign('dokumen_stok_id')->references('dokumen_stok_id')->on('dokumen_stok')->cascadeOnDelete();
            $table->foreign('konversi_id')->references('konversi_id')->on('konversi')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konversi_dokumen_stok');
    }
};
