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
        Schema::create('file_post', function (Blueprint $table) {
            $table->uuid('file_post_id')->primary();
            $table->uuid('post_id');
            $table->string('nama_file');
            $table->string('nama_dokumen');
            $table->timestamps();

            $table->foreign('post_id')->references('post_id')->on('post')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_post');
    }
};
