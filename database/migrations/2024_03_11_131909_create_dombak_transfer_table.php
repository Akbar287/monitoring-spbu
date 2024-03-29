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
        Schema::create('dombak_transfer', function (Blueprint $table) {
            $table->uuid('dombak_transfer_id')->primary();
            $table->uuid('produk_id');
            $table->uuid('jam_kerja_id');
            $table->date('tanggal');
            $table->boolean('is_confirmation');
            $table->timestamps();

            $table->foreign('produk_id')->references('produk_id')->on('produk')->cascadeOnDelete();
            $table->foreign('jam_kerja_id')->references('jam_kerja_id')->on('jam_kerja')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dombak_transfer');
    }
};
