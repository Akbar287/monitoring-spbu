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
        Schema::create('tera_return', function (Blueprint $table) {
            $table->uuid('tera_id');
            $table->uuid('tera_return_id');

            $table->primary(['tera_id', 'tera_return_id']);

            $table->foreign('tera_id')->references('tera_id')->on('tera')->cascadeOnDelete();
            $table->foreign('tera_return_id')->references('tera_id')->on('tera')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tera_return');
    }
};
