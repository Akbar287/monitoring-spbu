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
        Schema::create('file_lo', function (Blueprint $table) {
            $table->uuid('file_lo_id')->primary();
            $table->uuid('pengiriman_id');
            $table->uuid('produk_id');
            $table->decimal('jumlah', 16, 2);
            $table->string('satuan_jumlah', 16);
            $table->string('no_faktur', 16);
            $table->string('no_lo', 16);
            $table->string('nama_dokumen', 128);
            $table->string('nama_file', 128);
            $table->date('tanggal_upload');
            $table->timestamps();

            $table->foreign('pengiriman_id')->references('pengiriman_id')->on('pengiriman')->cascadeOnDelete();
            $table->foreign('produk_id')->references('produk_id')->on('produk')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_lo');
    }
};
