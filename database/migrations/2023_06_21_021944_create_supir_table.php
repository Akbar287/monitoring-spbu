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
        Schema::create('supir', function (Blueprint $table) {
            $table->uuid('supir_id')->primary();
            $table->uuid('pengiriman_id');
            $table->string('nama_supir');
            $table->timestamps();

            $table->foreign('pengiriman_id')->references('pengiriman_id')->on('pengiriman')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supir');
    }
};
