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
        Schema::create('penjualan', function (Blueprint $table) {
            $table->uuid('penjualan_id')->primary();
            $table->uuid('status_setoran_id');
            $table->uuid('spbu_id');
            $table->uuid('jam_kerja_id');
            $table->date('tanggal');
            $table->decimal('total_penjualan', 16, 2);
            $table->decimal('total_uang_kotak', 16, 2);
            $table->boolean('is_accepted_admin');
            $table->boolean('is_accepted_top');
            $table->timestamps();

            $table->foreign('status_setoran_id')->references('status_setoran_id')->on('status_setoran')->cascadeOnDelete();
            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->cascadeOnDelete();
            $table->foreign('jam_kerja_id')->references('jam_kerja_id')->on('jam_kerja')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
