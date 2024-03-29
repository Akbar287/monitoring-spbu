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
        Schema::create('dombak_transfer_dokumen_stok', function (Blueprint $table) {
            $table->uuid('dombak_transfer_id');
            $table->uuid('dokumen_stok_id');
            $table->primary(['dombak_transfer_id', 'dokumen_stok_id']);

            $table->foreign('dombak_transfer_id')->references('dombak_transfer_id')->on('dombak_transfer')->cascadeOnDelete();
            $table->foreign('dokumen_stok_id')->references('dokumen_stok_id')->on('dokumen_stok')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dombak_transfer_dokumen_stok');
    }
};
