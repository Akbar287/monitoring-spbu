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
        Schema::create('nozzle', function (Blueprint $table) {
            $table->uuid('nozzle_id')->primary();
            $table->uuid('dispenser_id');
            $table->uuid('produk_id');
            $table->string('nama_nozzle', 16);
            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif');

            $table->foreign('dispenser_id')->references('dispenser_id')->on('dispenser')->cascadeOnDelete();
            $table->foreign('produk_id')->references('produk_id')->on('produk')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nozzle');
    }
};
