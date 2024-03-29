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
        Schema::create('gaji_member', function (Blueprint $table) {
            $table->uuid('gaji_member_id')->primary();
            $table->uuid('gaji_id');
            $table->uuid('member_id');
            $table->date('tanggal');
            $table->decimal('total_gaji_bersih', 16, 2);

            $table->foreign('gaji_id')->references('gaji_id')->on('gaji')->cascadeOnDelete();
            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaji_member');
    }
};
