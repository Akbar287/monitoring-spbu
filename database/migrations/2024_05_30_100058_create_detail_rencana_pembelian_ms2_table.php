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
        Schema::create('detail_rencana_pembelian_ms2', function (Blueprint $table) {
            $table->uuid('detail_rencana_pembelian_ms2_id')->primary();
            $table->uuid('ms2_id');
            $table->uuid('detail_rencana_pembelian_id');
            $table->uuid('jam_kerja_id');
            $table->boolean('is_delivery');
            $table->timestamps();

            $table->foreign('ms2_id')->references('ms2_id')->on('ms2')->cascadeOnDelete();
            $table->foreign('jam_kerja_id')->references('jam_kerja_id')->on('jam_kerja')->cascadeOnDelete();
            $table->foreign('detail_rencana_pembelian_id')->references('detail_rencana_pembelian_id')->on('detail_rencana_pembelian')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_rencana_pembelian_ms2');
    }
};
