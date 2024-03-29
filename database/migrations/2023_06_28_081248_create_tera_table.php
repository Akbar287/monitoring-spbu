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
        Schema::create('tera', function (Blueprint $table) {
            $table->uuid('tera_id')->primary();
            $table->uuid('spbu_id');
            $table->date('tanggal');
            $table->decimal('grand_total', 16, 2);
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
        Schema::dropIfExists('tera');
    }
};
