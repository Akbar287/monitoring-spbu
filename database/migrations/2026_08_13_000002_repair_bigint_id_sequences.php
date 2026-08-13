<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sama seperti 2026_07_10_000000_repair_migrations_table: database ini hasil
 * restore dump sehingga tabel ber-PK bigint kehilangan sequence default-nya.
 * Tanpa perbaikan ini, insert gagal dengan
 * "null value in column id ... violates not-null constraint".
 *
 * `personal_access_tokens` menjadi masalah begitu API mobile aktif;
 * `failed_jobs` akan menjadi masalah pada saat queue mulai dipakai.
 */
return new class extends Migration
{
    private const TABEL = ['personal_access_tokens', 'failed_jobs'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::TABEL as $tabel) {
            if (! Schema::hasTable($tabel)) {
                continue;
            }

            $sequence = $tabel . '_id_seq';
            DB::statement("CREATE SEQUENCE IF NOT EXISTS {$sequence} OWNED BY {$tabel}.id");
            DB::statement("ALTER TABLE {$tabel} ALTER COLUMN id SET DEFAULT nextval('{$sequence}')");
            DB::statement("SELECT setval('{$sequence}', (SELECT COALESCE(MAX(id), 1) FROM {$tabel}))");
        }
    }

    public function down(): void
    {
        // perbaikan sequence tidak perlu di-rollback
    }
};
