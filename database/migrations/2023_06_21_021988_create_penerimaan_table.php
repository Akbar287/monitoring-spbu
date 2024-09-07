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
        Schema::create('penerimaan', function (Blueprint $table) {
            $table->uuid('penerimaan_id')->primary();
            $table->uuid('file_lo_id');
            // $table->uuid('dokumen_stok_id');
            $table->date('tanggal');
            $table->timestamps();

            $table->foreign('file_lo_id')->references('file_lo_id')->on('file_lo')->cascadeOnDelete();
            $table->foreign('dokumen_stok_id')->references('dokumen_stok_id')->on('dokumen_stok')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan');
    }
};
