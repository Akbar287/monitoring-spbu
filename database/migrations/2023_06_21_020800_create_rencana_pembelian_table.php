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
        Schema::create('rencana_pembelian', function (Blueprint $table) {
            $table->uuid('rencana_pembelian_id')->primary();
            $table->uuid('status_purchase_id');
            $table->uuid('spbu_id');
            $table->uuid('member_id');
            $table->uuid('pembelian_id')->nullable();
            $table->string('kode_pembelian', 32)->nullable();
            $table->date('tanggal_pembelian')->nullable();
            $table->decimal('grand_total', 16, 6)->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_confirmation');
            $table->boolean('is_denied');
            $table->text('keterangan_denied')->nullable();
            $table->timestamps();

            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->cascadeOnDelete();
            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
            $table->foreign('status_purchase_id')->references('status_purchase_id')->on('status_purchase')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rencana_pembelian');
    }
};
