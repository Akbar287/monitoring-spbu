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
        Schema::create('losses', function (Blueprint $table) {
            $table->uuid('losses_id')->primary();
            $table->uuid('jenis_losses_id');
            $table->uuid('dokumen_stok_id');
            $table->date('tanggal');
            $table->enum('simbol', ['lebih', 'kurang']);
            $table->decimal('jumlah', 16, 6);
            $table->timestamps();

            $table->foreign('jenis_losses_id')->references('jenis_losses_id')->on('jenis_losses')->cascadeOnDelete();
            $table->foreign('dokumen_stok_id')->references('dokumen_stok_id')->on('dokumen_stok')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('losses');
    }
};
