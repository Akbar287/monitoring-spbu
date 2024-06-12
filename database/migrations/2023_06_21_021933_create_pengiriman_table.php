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
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->uuid('pengiriman_id')->primary();
            $table->uuid('rencana_pembelian_id');
            $table->uuid('member_id');
            $table->date('tanggal');
            $table->string('no_do', 32)->nullable();
            $table->string('no_pol', 16);
            $table->boolean('is_confirmation_admin');
            $table->boolean('is_confirmation_top');
            $table->boolean('is_done');
            $table->text('catatan')->nullable();

            $table->foreign('rencana_pembelian_id')->references('rencana_pembelian_id')->on('rencana_pembelian')->cascadeOnDelete();
            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman');
    }
};
