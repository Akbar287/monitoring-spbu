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
        Schema::create('aset', function (Blueprint $table) {
            $table->uuid('aset_id')->primary();
            $table->uuid('spbu_id');
            $table->string('nama_aset');
            $table->integer('jumlah');
            $table->date('tanggal_digunakan');
            $table->decimal('harga', 16, 6);
            $table->decimal('penyusutan_per_hari', 16, 6);
            $table->boolean('is_digunakan');
            $table->text('keterangan');
            $table->timestamps();

            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset');
    }
};
