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
        Schema::create('jurnal', function (Blueprint $table) {
            $table->char('jurnal_id', 36)->primary();
            $table->char('spbu_id', 36);
            $table->string('nomor_jurnal', 32)->unique();
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            // sumber posting otomatis: penjualan, penarikan, pembayaran, petty_cash,
            // gaji, losses, penyusutan, stok_taking, saldo_awal, closing, manual
            $table->string('tipe_sumber', 32);
            $table->char('sumber_id', 36)->nullable();
            $table->boolean('is_posted')->default(true);
            $table->char('posted_by', 36)->nullable();
            $table->timestamps();

            // satu sumber transaksi hanya boleh menghasilkan satu jurnal (anti double-posting)
            $table->unique(['tipe_sumber', 'sumber_id']);
            $table->index(['spbu_id', 'tanggal']);
            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->cascadeOnDelete();
            $table->foreign('posted_by')->references('member_id')->on('member')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal');
    }
};
