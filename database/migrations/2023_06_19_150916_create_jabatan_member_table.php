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
        Schema::create('jabatan_member', function (Blueprint $table) {
            $table->uuid('jabatan_id');
            $table->uuid('member_id');
            $table->primary(['jabatan_id', 'member_id']);

            $table->foreign('jabatan_id')->references('jabatan_id')->on('jabatan')->cascadeOnDelete();
            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jabatan_member');
    }
};
