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
        Schema::create('penarikan', function (Blueprint $table) {
            $table->uuid('penarikan_id')->primary();
            $table->uuid('penjualan_id');
            $table->uuid('member_id');
            $table->date('tanggal');
            $table->string('nama_bank', 16);
            $table->string('no_referensi', 16);
            $table->string('metode_transfer', 64);
            $table->decimal('total_uang', 18, 6);
            $table->boolean('is_accepted');
            $table->timestamps();

            $table->foreign('penjualan_id')->references('penjualan_id')->on('penjualan')->cascadeOnDelete();
            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penarikan');
    }
};
