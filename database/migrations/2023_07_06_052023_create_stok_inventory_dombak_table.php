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
        Schema::create('stok_inventory_dombak', function (Blueprint $table) {
            $table->uuid('stok_inventory_dombak_id')->primary();
            $table->uuid('stok_inventory_id');
            $table->uuid('dombak_id');
            $table->decimal('stok', 16, 2);

            $table->foreign('dombak_id')->references('dombak_id')->on('dombak')->cascadeOnDelete();
            $table->foreign('stok_inventory_id')->references('stok_inventory_id')->on('stok_inventory')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_inventory_dombak');
    }
};
