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
        Schema::create('segel', function (Blueprint $table) {
            $table->uuid('segel_id')->primary();
            $table->uuid('file_lo_id');
            $table->string('no_segel');

            $table->foreign('file_lo_id')->references('file_lo_id')->on('file_lo')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segel');
    }
};
