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
        Schema::create('file_petty_cash', function (Blueprint $table) {
            $table->uuid('file_petty_cash_id')->primary();
            $table->uuid('petty_cash_id');
            $table->string('nama_file', 64);
            $table->string('nama_dokumen', 64);
            $table->date('tanggal_upload');

            $table->foreign('petty_cash_id')->references('petty_cash_id')->on('petty_cash')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_petty_cash');
    }
};
