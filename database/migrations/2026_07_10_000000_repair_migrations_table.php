<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Database ini hasil restore dump: kolom migrations.id kehilangan sequence
     * default-nya sehingga setiap `php artisan migrate` gagal mencatat dirinya.
     * Migrasi ini memperbaiki sequence lebih dulu, lalu mencatat migrasi lama
     * yang tabelnya sudah ada di database tetapi belum tercatat.
     */
    public function up(): void
    {
        DB::statement("CREATE SEQUENCE IF NOT EXISTS migrations_id_seq OWNED BY migrations.id");
        DB::statement("ALTER TABLE migrations ALTER COLUMN id SET DEFAULT nextval('migrations_id_seq')");
        DB::statement("SELECT setval('migrations_id_seq', (SELECT COALESCE(MAX(id),1) FROM migrations))");

        $sudahApplied = [
            '2023_02_07_113515_create_pajak_pembelian_lib_table' => 'pajak_pembelian_lib',
            '2023_12_26_121615_create_pajak_pembelian_table' => 'pajak_pembelian',
            '2024_03_28_135650_create_file_lampiran_lo_table' => 'file_lampiran_lo',
            '2025_02_04_083843_create_penerimaan_stok_taking_penyesuaian' => 'penerimaan_stok_taking_penyesuaian',
        ];
        $batch = DB::table('migrations')->max('batch') + 1;
        foreach ($sudahApplied as $migration => $tabel) {
            $tercatat = DB::table('migrations')->where('migration', $migration)->exists();
            if (! $tercatat && Schema::hasTable($tabel)) {
                DB::table('migrations')->insert(['migration' => $migration, 'batch' => $batch]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // perbaikan sequence tidak perlu di-rollback
    }
};
