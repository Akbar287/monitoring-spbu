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
        Schema::create('file_penerimaan', function (Blueprint $table) {
            $table->uuid('file_penerimaan_id')->primary();
            $table->uuid('penerimaan_id');
            $table->string('nama_file', 128);
            $table->string('nama_dokumen', 128);
            $table->date('tanggal_upload');

            $table->foreign('penerimaan_id')->references('penerimaan_id')->on('penerimaan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_penerimaan');
    }
};
