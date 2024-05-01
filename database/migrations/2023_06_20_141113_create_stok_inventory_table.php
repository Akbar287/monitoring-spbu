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
        Schema::create('stok_inventory', function (Blueprint $table) {
            $table->uuid('stok_inventory_id')->primary();
            $table->uuid('produk_id');
            $table->decimal('stok', 16, 6);
            $table->timestamps();

            $table->foreign('produk_id')->references('produk_id')->on('produk')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_inventory');
    }
};
