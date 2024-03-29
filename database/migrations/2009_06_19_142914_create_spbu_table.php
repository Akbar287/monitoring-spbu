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
        Schema::create('spbu', function (Blueprint $table) {
            $table->uuid('spbu_id')->primary();
            $table->string('nama_spbu');
            $table->string('nomor_spbu', 32);
            $table->date('tanggal_pendirian');
            $table->double('luas_lahan');
            $table->string('satuan_luas', 8);
            $table->string('alamat');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spbu');
    }
};
