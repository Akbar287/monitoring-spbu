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
        Schema::create('post', function (Blueprint $table) {
            $table->uuid('post_id')->primary();
            $table->uuid('member_id');
            $table->string('title');
            $table->text('content');
            $table->boolean('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post');
    }
};
