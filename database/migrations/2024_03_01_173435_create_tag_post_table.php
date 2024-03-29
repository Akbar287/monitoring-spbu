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
        Schema::create('tag_post', function (Blueprint $table) {
            $table->uuid('tag_id');
            $table->uuid('post_id');
            $table->primary(['tag_id', 'post_id']);

            $table->foreign('tag_id')->references('tag_id')->on('tag')->cascadeOnDelete();
            $table->foreign('post_id')->references('post_id')->on('post')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_post');
    }
};
