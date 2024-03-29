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
        Schema::create('file_lampiran_lo', function (Blueprint $table) {
            $table->uuid('file_lampiran_lo_id')->primary();
            $table->uuid('file_lo_id');
            $table->string('nama_file');
            $table->string('nama_dokumen');
            $table->date('tanggal_upload');

            $table->foreign('file_lo_id')->references('file_lo_id')->on('file_lo')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_lampiran_lo');
    }
};
