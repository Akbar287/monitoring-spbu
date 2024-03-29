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
        Schema::create('file_fasilitas', function (Blueprint $table) {
            $table->uuid('file_fasilitas_id')->primary();
            $table->uuid('fasilitas_id');
            $table->string('nama_file');
            $table->string('nama_dokumen');
            $table->date('tanggal_upload');
            $table->timestamps();

            $table->foreign('fasilitas_id')->references('fasilitas_id')->on('fasilitas')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_fasilitas');
    }
};
