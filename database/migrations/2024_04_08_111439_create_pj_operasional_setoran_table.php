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
        Schema::create('pj_operasional_setoran', function (Blueprint $table) {
            $table->uuid('pj_operasional_setoran_id')->primary();
            $table->uuid('penjualan_id');
            $table->uuid('jabatan_id');
            $table->uuid('member_id');

            $table->foreign('penjualan_id')->references('penjualan_id')->on('penjualan')->cascadeOnDelete();
            $table->foreign('jabatan_id')->references('jabatan_id')->on('jabatan')->cascadeOnDelete();
            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pj_operasional_setoran');
    }
};
