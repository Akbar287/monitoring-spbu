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
        Schema::create('file_aset', function (Blueprint $table) {
            $table->uuid('file_aset_id')->primary();
            $table->uuid('aset_id');
            $table->string('nama_file');
            $table->string('nama_dokumen');
            $table->date('tanggal_upload');
            $table->timestamps();

            $table->foreign('aset_id')->references('aset_id')->on('aset')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_aset');
    }
};
