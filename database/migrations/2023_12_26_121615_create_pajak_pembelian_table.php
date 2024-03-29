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
        Schema::create('pajak_pembelian', function (Blueprint $table) {
            $table->uuid('pajak_pembelian_id')->primary();
            $table->uuid('rencana_pembelian_id');
            $table->uuid('pajak_pembelian_lib_history_id');
            $table->double('net_price', 13, 2);
            $table->double('ppn', 13, 2);
            $table->double('ppbkb', 13, 2);
            $table->double('pph', 13, 2);
            $table->double('total_amount', 13, 2);
            $table->timestamps();

            $table->foreign('rencana_pembelian_id')->references('rencana_pembelian_id')->on('rencana_pembelian')->cascadeOnDelete();
            $table->foreign('pajak_pembelian_lib_history_id')->references('pajak_pembelian_lib_history_id')->on('pajak_pembelian_lib_history')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pajak_pembelian');
    }
};
