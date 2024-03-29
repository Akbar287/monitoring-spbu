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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->uuid('pembayaran_id')->primary();
            $table->uuid('rencana_pembelian_id');
            $table->uuid('member_id');
            $table->date('tanggal');
            $table->string('no_cek_bg', 64);
            $table->string('no_rekening', 32);
            $table->string('nama_rekening', 128);
            $table->decimal('total_setoran', 18, 2);
            $table->boolean('is_confirmation_admin');
            $table->boolean('is_confirmation_top');
            $table->timestamps();

            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
            $table->foreign('rencana_pembelian_id')->references('rencana_pembelian_id')->on('rencana_pembelian')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
