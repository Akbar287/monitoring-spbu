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
        Schema::create('detail_rencana_pembelian', function (Blueprint $table) {
            $table->uuid('detail_rencana_pembelian_id')->primary();
            $table->uuid('rencana_pembelian_id');
            $table->uuid('produk_id');
            $table->decimal('jumlah', 16, 2);
            $table->string('satuan_jumlah', 16);
            $table->decimal('rit', 6, 2);
            $table->decimal('harga', 16, 2);
            $table->decimal('total_harga', 16, 2);
            $table->boolean('is_confirmation');
            $table->boolean('ms2');
            $table->boolean('delivery');
            $table->timestamps();

            $table->foreign('rencana_pembelian_id')->references('rencana_pembelian_id')->on('rencana_pembelian')->cascadeOnDelete();
            $table->foreign('produk_id')->references('produk_id')->on('produk')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_rencana_pembelian');
    }
};
