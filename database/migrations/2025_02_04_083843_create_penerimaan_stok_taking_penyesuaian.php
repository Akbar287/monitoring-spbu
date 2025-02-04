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
        Schema::create('penerimaan_stok_taking_penyesuaian', function (Blueprint $table) {
            $table->uuid('penerimaan_stok_taking_penyesuaian_id')->primary();
            $table->uuid('penerimaan_id');
            $table->uuid('dokumen_stok_id');

            $table->foreign('penerimaan_id')->references('penerimaan_id')->on('penerimaan')->cascadeOnDelete();
            $table->foreign('dokumen_stok_id')->references('dokumen_stok_id')->on('dokumen_stok')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_stok_taking_penyesuaian');
    }
};
