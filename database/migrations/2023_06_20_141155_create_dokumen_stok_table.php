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
        Schema::create('dokumen_stok', function (Blueprint $table) {
            $table->uuid('dokumen_stok_id')->primary();
            $table->uuid('stok_inventory_id');
            $table->uuid('member_id');
            $table->uuid('dombak_id');
            $table->uuid('jam_kerja_id');
            $table->uuid('type_dokumen_stok_id');
            $table->date('tanggal');
            $table->timestamp('waktu');
            $table->decimal('stok_awal', 16, 6);
            $table->decimal('stok_akhir', 16, 6);
            $table->boolean('is_confirmation');
            $table->timestamps();

            $table->foreign('stok_inventory_id')->references('stok_inventory_id')->on('stok_inventory')->cascadeOnDelete();
            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
            $table->foreign('dombak_id')->references('dombak_id')->on('dombak')->cascadeOnDelete();
            $table->foreign('jam_kerja_id')->references('jam_kerja_id')->on('jam_kerja')->cascadeOnDelete();
            $table->foreign('type_dokumen_stok_id')->references('type_dokumen_stok_id')->on('type_dokumen_stok')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_stok');
    }
};
