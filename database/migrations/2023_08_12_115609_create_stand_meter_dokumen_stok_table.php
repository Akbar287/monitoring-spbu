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
        Schema::create('stand_meter_dokumen_stok', function (Blueprint $table) {
            $table->uuid('stand_meter_id');
            $table->uuid('dokumen_stok_id');
            $table->primary(['stand_meter_id', 'dokumen_stok_id']);

            $table->foreign('dokumen_stok_id')->references('dokumen_stok_id')->on('dokumen_stok')->cascadeOnDelete();
            $table->foreign('stand_meter_id')->references('stand_meter_id')->on('stand_meter')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stand_meter_dokumen_stok');
    }
};
