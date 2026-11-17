<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KualitasMinyakMigrationTest extends TestCase
{
    public function test_legacy_approvals_are_preserved_only_for_existing_measurements(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        Schema::create('userlogin', fn (Blueprint $t) => $t->string('user_login_id')->primary());
        Schema::create('kualitas_minyak', function (Blueprint $t) {
            $t->string('kualitas_minyak_id')->primary();
            foreach (['dibuat_oleh', 'nama_operator', 'disetujui_oleh', 'nama_penyetuju', 'disetujui_pada'] as $col) {
                $t->string($col)->nullable();
            }
        });
        Schema::create('kualitas_minyak_penerimaan', function (Blueprint $t) {
            $t->id();
            $t->string('kualitas_minyak_id');
            $t->dateTime('waktu_akhir')->nullable();
        });
        DB::table('userlogin')->insert([['user_login_id' => 'operator'], ['user_login_id' => 'admin']]);
        DB::table('kualitas_minyak')->insert([
            ['kualitas_minyak_id' => 'approved', 'dibuat_oleh' => 'operator', 'nama_operator' => 'Operator', 'disetujui_oleh' => 'admin', 'nama_penyetuju' => 'Admin', 'disetujui_pada' => '2026-11-17 18:00:00'],
            ['kualitas_minyak_id' => 'pending', 'dibuat_oleh' => 'operator', 'nama_operator' => 'Operator', 'disetujui_oleh' => null, 'nama_penyetuju' => null, 'disetujui_pada' => null],
        ]);
        DB::table('kualitas_minyak_penerimaan')->insert([
            ['id' => 1, 'kualitas_minyak_id' => 'approved', 'waktu_akhir' => '2026-11-17 16:00:00'],
            ['id' => 2, 'kualitas_minyak_id' => 'pending', 'waktu_akhir' => null],
        ]);
        (require database_path('migrations/2026_11_17_000003_add_density_stage_approvals.php'))->up();
        $approved = DB::table('kualitas_minyak_penerimaan')->find(1);
        $pending = DB::table('kualitas_minyak_penerimaan')->find(2);
        $this->assertSame('admin', $approved->penerimaan_disetujui_oleh);
        $this->assertSame('admin', $approved->akhir_disetujui_oleh);
        $this->assertSame('2026-11-17 18:00:00', $approved->akhir_disetujui_pada);
        $this->assertSame('operator', $approved->akhir_dibuat_oleh);
        $this->assertSame('operator', $pending->penerimaan_dibuat_oleh);
        $this->assertNull($pending->penerimaan_disetujui_pada);
        $this->assertNull($pending->akhir_dibuat_oleh);
        $this->assertNull($pending->akhir_disetujui_pada);
        $this->assertSame('admin', DB::table('kualitas_minyak')->where('kualitas_minyak_id', 'approved')->value('disetujui_oleh'));
    }
}
