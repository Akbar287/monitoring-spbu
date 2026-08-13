<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sanctum membuat `tokenable_id` sebagai unsignedBigInteger lewat morphs(),
 * padahal seluruh primary key di aplikasi ini adalah UUID char(36)
 * (lihat DATABASE.md §1). Tanpa perubahan tipe ini, token API mobile tidak
 * bisa disimpan sama sekali.
 *
 * Perubahan dilakukan dengan SQL mentah karena doctrine/dbal tidak terpasang,
 * sehingga `->change()` tidak tersedia.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return;
        }

        match (Schema::getConnection()->getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE varchar(36) USING tokenable_id::varchar'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id VARCHAR(36) NOT NULL'),
            default => null, // sqlite bertipe dinamis; tidak perlu diubah
        };
    }

    public function down(): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return;
        }

        // Token bersifat sementara — dikosongkan dulu supaya cast balik ke
        // bigint tidak gagal karena berisi UUID. Efeknya semua sesi mobile logout.
        DB::table('personal_access_tokens')->delete();

        match (Schema::getConnection()->getDriverName()) {
            'pgsql' => DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE bigint USING tokenable_id::bigint'),
            'mysql', 'mariadb' => DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id BIGINT UNSIGNED NOT NULL'),
            default => null,
        };
    }
};
