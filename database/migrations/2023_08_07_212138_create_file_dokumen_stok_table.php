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
        Schema::create('file_dokumen_stok', function (Blueprint $table) {
            $table->uuid('file_dokumen_stok_id')->primary();
            $table->uuid('dokumen_stok_id');
            $table->string('nama_file', 128);
            $table->string('nama_dokumen', 128);
            $table->date('tanggal_upload');
            $table->timestamps();

            $table->foreign('dokumen_stok_id')->references('dokumen_stok_id')->on('dokumen_stok')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_dokumen_stok');
    }
};
