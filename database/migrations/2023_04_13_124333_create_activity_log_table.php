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
        Schema::create('activity_log', function (Blueprint $table) {
            $table->uuid('activity_log_id')->primary();
            $table->uuid('member_id');
            $table->string('nama_aktivitas');
            $table->string('class', 64);
            $table->string('function', 64);
            $table->string('input', 64);
            $table->string('output', 64);
            $table->timestamps();

            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
