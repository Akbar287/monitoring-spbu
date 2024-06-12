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
        Schema::create('ms2', function (Blueprint $table) {
            $table->uuid('ms2_id')->primary();
            $table->uuid('member_id');
            $table->date('tanggal');
            $table->string('kode_sms', 64);
            $table->boolean('is_done');
            $table->timestamps();

            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ms2');
    }
};
