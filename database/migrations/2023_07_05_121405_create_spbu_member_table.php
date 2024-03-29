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
        Schema::create('spbu_member', function (Blueprint $table) {
            $table->uuid('spbu_id');
            $table->uuid('member_id');
            $table->primary(['spbu_id', 'member_id']);

            $table->foreign('spbu_id')->references('spbu_id')->on('spbu')->cascadeOnDelete();
            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spbu_member');
    }
};
