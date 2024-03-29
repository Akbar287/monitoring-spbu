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
        Schema::create('member', function (Blueprint $table) {
            $table->uuid('member_id')->primary();
            $table->uuid('ktp_id');
            $table->uuid('status_member_id');
            $table->string('email')->unique();
            $table->string('no_hp', 16)->nullable();
            $table->string('no_wa', 16)->nullable();
            $table->string('avatar', 128);
            $table->date('bergabung_sejak');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ktp_id')->references('ktp_id')->on('ktp')->cascadeOnDelete();
            $table->foreign('status_member_id')->references('status_member_id')->on('status_member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member');
    }
};
