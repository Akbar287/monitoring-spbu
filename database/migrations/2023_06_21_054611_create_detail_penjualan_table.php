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
        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->uuid('detail_penjualan_id')->primary();
            $table->uuid('penjualan_id');
            $table->uuid('stand_meter_id');
            $table->uuid('harga_id');
            $table->decimal('liter', 16, 2);
            $table->decimal('total_detail_jual', 16, 2);
            $table->boolean('is_confirmed')->default(false);
            $table->timestamps();

            $table->foreign('penjualan_id')->references('penjualan_id')->on('penjualan')->cascadeOnDelete();
            $table->foreign('stand_meter_id')->references('stand_meter_id')->on('stand_meter')->cascadeOnDelete();
            $table->foreign('harga_id')->references('harga_id')->on('harga')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penjualan');
    }
};
