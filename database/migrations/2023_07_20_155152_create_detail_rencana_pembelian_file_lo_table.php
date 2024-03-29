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
        Schema::create('detail_rencana_file', function (Blueprint $table) {
            $table->uuid('detail_rencana_pembelian_id');
            $table->uuid('file_lo_id');
            $table->primary(['detail_rencana_pembelian_id', 'file_lo_id']);

            $table->foreign('detail_rencana_pembelian_id')->references('detail_rencana_pembelian_id')->on('detail_rencana_pembelian')->cascadeOnDelete();
            $table->foreign('file_lo_id')->references('file_lo_id')->on('file_lo')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_rencana_file');
    }
};
