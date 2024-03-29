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
        Schema::create('kategori_post', function (Blueprint $table) {
            $table->uuid('kategori_id');
            $table->uuid('post_id');
            $table->primary(['kategori_id', 'post_id']);

            $table->foreign('kategori_id')->references('kategori_id')->on('kategori')->cascadeOnDelete();
            $table->foreign('post_id')->references('post_id')->on('post')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_post');
    }
};
