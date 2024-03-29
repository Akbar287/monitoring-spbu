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
        Schema::create('area_member', function (Blueprint $table) {
            $table->uuid('area_member_id')->primary();
            $table->uuid('member_id');
            $table->string('provinsi');
            $table->string('kabupaten');
            $table->string('kecamatan');
            $table->string('desa', 128);
            $table->string('rw', 6);
            $table->string('rt', 6);
            $table->string('no', 6);
            $table->text('alamat');
            $table->boolean('is_primary');
            $table->timestamps();

            $table->foreign('member_id')->references('member_id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_member');
    }
};
