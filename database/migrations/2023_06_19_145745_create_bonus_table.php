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
        Schema::create('bonus', function (Blueprint $table) {
            $table->uuid('bonus_id')->primary();
            $table->uuid('member_id');
            $table->integer('persentase');
            $table->decimal('total_bonus', 16, 2);
            $table->date('tanggal');
            $table->text('deksripsi')->nullable();

            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonus');
    }
};
