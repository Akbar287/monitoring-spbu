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
        Schema::create('file_pembayaran', function (Blueprint $table) {
            $table->uuid('file_pembayaran_id')->primary();
            $table->uuid('pembayaran_id');
            $table->string('nama_file', 64);
            $table->string('nama_dokumen', 64);
            $table->date('tanggal_upload');
            $table->timestamps();

            $table->foreign('pembayaran_id')->references('pembayaran_id')->on('pembayaran')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_pembayaran');
    }
};
