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
        Schema::create('pengiriman_ms2', function (Blueprint $table) {
            $table->uuid('pengiriman_id');
            $table->uuid('ms2_id');
            $table->primary(['pengiriman_id', 'ms2_id']);

            $table->foreign('ms2_id')->references('ms2_id')->on('ms2')->cascadeOnDelete();
            $table->foreign('pengiriman_id')->references('pengiriman_id')->on('pengiriman')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman_ms2');
    }
};
