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
        Schema::create('detail_tera', function (Blueprint $table) {
            $table->uuid('detail_tera_id')->primary();
            $table->uuid('dokumen_stok_id');
            $table->uuid('tera_id');
            $table->decimal('quantity', 16, 6);
            $table->decimal('harga', 16, 6);
            $table->text('keterangan');
            $table->timestamps();

            $table->foreign('tera_id')->references('tera_id')->on('tera')->cascadeOnDelete();
            $table->foreign('dokumen_stok_id')->references('dokumen_stok_id')->on('dokumen_stok')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_tera');
    }
};
