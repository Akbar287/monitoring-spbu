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
        Schema::create('file_penarikan', function (Blueprint $table) {
            $table->uuid('file_penarikan_id')->primary();
            $table->uuid('penarikan_id');
            $table->date('tanggal');
            $table->string('nama_file', 128);
            $table->string('nama_dokumen', 128);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('penarikan_id')->references('penarikan_id')->on('penarikan')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_penarikan');
    }
};
