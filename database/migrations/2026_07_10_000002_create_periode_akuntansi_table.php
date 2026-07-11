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
        Schema::create('periode_akuntansi', function (Blueprint $table) {
            $table->char('periode_akuntansi_id', 36)->primary();
            $table->char('spbu_id', 36);
            $table->smallInteger('tahun');
            $table->smallInteger('bulan');
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->char('closed_by', 36)->nullable();
            $table->timestamps();

            $table->unique(['spbu_id', 'tahun', 'bulan']);
            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->cascadeOnDelete();
            $table->foreign('closed_by')->references('member_id')->on('member')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_akuntansi');
    }
};
