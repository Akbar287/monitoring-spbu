<?php

namespace Tests\Feature;

use App\Http\Middleware\PastikanSetupSelesai;
use App\Models\KualitasMinyak;
use App\Models\UserLogin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class KualitasMinyakTest extends TestCase
{
    private string $spbu;
    private string $produk;
    private string $dombak;
    private string $level;
    private string $status;
    private UserLogin $operator;
    private UserLogin $admin;
    private UserLogin $manajer;
    private string $base = '/tera/kualitas-minyak';

    protected function setUp(): void
    {
        parent::setUp();
        // Seluruh tes fitur ini mandiri; tidak membaca atau menulis database .env.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
        DB::purge('sqlite');
        $this->withoutMiddleware(PastikanSetupSelesai::class);
        $patterns = ['2009_*create_spbu*', '2009_*create_status_member*', '2010_*create_ktp*', '2010_*create_member*',
            '2023_*create_userlogin*', '2023_*create_produk_table*', '2023_*create_divisi*', '2023_*create_level*',
            '2023_*create_jabatan_table*', '2023_*create_jabatan_member*', '2023_*create_dombak_table*',
            '2023_*create_stok_inventory_table*', '2023_*create_stok_inventory_dombak*', '2026_11_17_*'];
        foreach ($patterns as $pattern) {
            foreach (glob(database_path('migrations/'.$pattern.'.php')) as $file) {
                (require $file)->up();
            }
        }
        $this->spbu = $this->insert('spbu', 'spbu_id', ['nomor_spbu' => '2434520', 'nama_spbu' => 'SPBU Uji', 'tanggal_pendirian' => '2000-01-01', 'luas_lahan' => 100, 'satuan_luas' => 'm2', 'alamat' => 'Uji']);
        $this->status = $this->insert('status_member', 'status_member_id', ['nama_status' => 'Aktif']);
        $divisi = $this->insert('divisi', 'divisi_id', ['spbu_id' => $this->spbu, 'nama_divisi' => 'Operasional', 'is_aktif' => 1]);
        $this->level = $this->insert('level', 'level_id', ['divisi_id' => $divisi, 'nama_level' => 'Operator', 'is_aktif' => 1]);
        $this->produk = $this->insert('produk', 'produk_id', ['spbu_id' => $this->spbu, 'nama_produk' => 'Pertalite', 'singkatan' => 'PL', 'is_aktif' => 1]);
        $this->dombak = $this->tank('DM2', $this->produk);
        $this->operator = $this->user('Operator');
        $this->admin = $this->user('Admin');
        $this->manajer = $this->user('Manajer');
        $this->actingAs($this->operator);
    }

    private function insert(string $table, string $key, array $data): string
    {
        $id = (string) Str::uuid();
        DB::table($table)->insert([$key => $id] + $data);

        return $id;
    }

    private function user(string $jabatan): UserLogin
    {
        $ktp = $this->insert('ktp', 'ktp_id', ['nik' => '1234567890123456', 'nama' => $jabatan.' Uji', 'jenis_kelamin' => 'pria', 'tempat_lahir' => 'Uji', 'tanggal_lahir' => '1990-01-01', 'verified' => 1]);
        $member = $this->insert('member', 'member_id', ['ktp_id' => $ktp, 'status_member_id' => $this->status, 'email' => Str::uuid().'@example.test', 'avatar' => 'default.png', 'bergabung_sejak' => '2020-01-01']);
        $role = $this->insert('jabatan', 'jabatan_id', ['level_id' => $this->level, 'nama_jabatan' => $jabatan, 'is_aktif' => 1]);
        DB::table('jabatan_member')->insert(['jabatan_id' => $role, 'member_id' => $member]);

        return UserLogin::create(['member_id' => $member, 'username' => substr(Str::uuid(), 0, 24), 'password' => 'test-password', 'is_aktif' => 1]);
    }

    private function tank(string $nama, string $produk): string
    {
        $id = $this->insert('dombak', 'dombak_id', ['spbu_id' => $this->spbu, 'nama_dombak' => $nama, 'is_aktif' => 1]);
        $stok = $this->insert('stok_inventory', 'stok_inventory_id', ['produk_id' => $produk, 'stok' => 1000]);
        $this->insert('stok_inventory_dombak', 'stok_inventory_dombak_id', ['dombak_id' => $id, 'stok_inventory_id' => $stok, 'stok' => 1000]);

        return $id;
    }

    private function payload(array $extra = []): array
    {
        return array_replace(['tanggal' => '2026-11-17', 'dombak_id' => $this->dombak, 'jam_awal' => '06:00', 'density_awal' => '0,726', 'suhu_awal' => '31', 'tinggi_air' => '0', 'catatan' => 'Sampel pagi'], $extra);
    }

    private function receipt(array $extra = []): array
    {
        return array_replace(['waktu_penerimaan' => '2026-11-17T23:30', 'nomor_mobil_tangki' => 'BE 1234 AB', 'nomor_pnbp' => '814612341', 'truk_berwenang' => true,
            'density_15_pnbp' => '0.7393', 'density_penerimaan' => '0.726', 'suhu_penerimaan' => '31',
            'volume_pnbp' => '', 'volume_diterima' => '', 'catatan_penerimaan' => ''], $extra);
    }

    private function inertiaHeaders(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (new \App\Http\Middleware\HandleInertiaRequests())->version(\Illuminate\Http\Request::create('/'))];
    }

    private function save(array $payload = []): KualitasMinyak
    {
        $values = $this->payload($payload);
        $this->postJson($this->base, $values)->assertRedirect();

        return KualitasMinyak::where('tanggal', $values['tanggal'])->where('dombak_id', $values['dombak_id'])->firstOrFail();
    }

    public function test_astm_matches_source_and_rejects_gaps_conflicts_and_rounding(): void
    {
        $this->assertSame(15369, DB::table('astm_density')->count());
        $this->getJson($this->base.'/astm/lookup?density=0.726&suhu=31')->assertOk()->assertJson(['density_15' => '0.7389', 'halaman' => 9]);
        foreach (['density=0.780&suhu=30', 'density=0.691&suhu=25', 'density=0.7265&suhu=31', 'density=0.726&suhu=31.1'] as $query) {
            $this->getJson($this->base.'/astm/lookup?'.$query)->assertUnprocessable();
        }
        $this->get($this->base.'/astm?suhu=0', $this->inertiaHeaders())->assertOk()->assertJsonPath('props.rows.data.0.suhu', 0);
    }

    private function addReceipt(KualitasMinyak $record, array $extra = [])
    {
        $this->postJson($this->base.'/'.$record->getKey().'/penerimaan', $this->receipt($extra))->assertRedirect();

        return $record->penerimaan()->reorder('id', 'desc')->firstOrFail();
    }

    private function finalPayload(array $extra = []): array
    {
        return array_replace(['waktu_akhir' => '2026-11-18T01:30', 'density_akhir' => '0.725', 'suhu_akhir' => '32', 'catatan_akhir' => 'Pemeriksaan dua jam'], $extra);
    }

    private function approval(KualitasMinyak $record, string $tahap, $row = null): string
    {
        $this->actingAs($this->admin)->postJson($this->base.'/'.$record->getKey().'/setujui', ['tahap' => $tahap, 'penerimaan_id' => $row?->id])->assertRedirect();

        return $this->base.'/'.$record->getKey();
    }

    public function test_morning_record_derives_master_and_calculates_on_server(): void
    {
        $record = $this->save(['density_15_awal' => 9, 'disetujui_oleh' => $this->admin->getKey(), 'disetujui_pada' => now()]);
        $this->assertSame($this->produk, $record->produk_id);
        $this->assertSame('2434520', $record->nomor_spbu);
        $this->assertEquals(0.7389, $record->density_15_awal);
        $this->assertNull($record->disetujui_pada);
        $this->postJson($this->base, $this->payload())->assertUnprocessable()->assertJsonValidationErrors('tanggal');
        $this->approval($record, 'pagi');
        $this->assertNotNull($record->fresh()->disetujui_pada);
    }

    public function test_approved_morning_allows_receipt_then_approved_receipt_allows_final(): void
    {
        $record = $this->save();
        $url = $this->approval($record, 'pagi');
        $morningApproval = $record->fresh()->disetujui_pada;
        $otherOperator = $this->user('Operator');
        $this->actingAs($otherOperator);
        $this->get($url.'/edit?tahap=penerimaan', $this->inertiaHeaders())->assertOk();
        $row = $this->addReceipt($record, ['waktu_penerimaan' => '2026-11-17T12:00']);
        $this->assertSame($otherOperator->getKey(), $row->penerimaan_dibuat_oleh);
        $this->assertNull($row->penerimaan_disetujui_pada);
        $this->assertEquals($morningApproval, $record->fresh()->disetujui_pada);
        $this->actingAs($this->manajer)->postJson($url.'/setujui', ['tahap' => 'penerimaan', 'penerimaan_id' => $row->id])->assertRedirect();
        $this->assertSame($this->manajer->getKey(), $row->fresh()->penerimaan_disetujui_oleh);
        $this->assertNull($row->fresh()->waktu_akhir);
        $this->assertEquals(-0.0004, $row->selisih_density);
        $this->actingAs($this->operator)->get($url.'/edit?tahap=akhir&penerimaan_id='.$row->id, $this->inertiaHeaders())->assertOk();
        $this->putJson($url.'/penerimaan/'.$row->id.'/akhir', $this->finalPayload(['waktu_akhir' => '2026-11-17T14:00']))->assertRedirect();
        $row->refresh();
        $this->assertSame($this->operator->getKey(), $row->akhir_dibuat_oleh);
        $this->assertEquals(0.7387, $row->density_15_akhir);
        $this->assertNull($row->akhir_disetujui_pada);
        $this->approval($record, 'akhir', $row);
        $this->actingAs($this->admin)->putJson($url, $this->payload())->assertForbidden();
        $this->putJson($url.'/penerimaan/'.$row->id, $this->receipt())->assertForbidden();
        $this->putJson($url.'/penerimaan/'.$row->id.'/akhir', $this->finalPayload())->assertForbidden();
        $this->postJson($url.'/setujui', ['tahap' => 'akhir', 'penerimaan_id' => $row->id])->assertStatus(409);
        // Bahkan setelah ketiga density disetujui, penerimaan berikutnya tetap bisa ditambah.
        $this->actingAs($otherOperator);
        $new = $this->addReceipt($record, ['nomor_pnbp' => 'PNBP-BERIKUTNYA']);
        $this->assertNotEquals($row->id, $new->id);
        $this->assertCount(2, $record->fresh()->penerimaan);
        $this->assertNotNull($row->fresh()->akhir_disetujui_pada);
    }

    public function test_each_stage_can_be_approved_without_approving_other_stages(): void
    {
        $record = $this->save();
        $row = $this->addReceipt($record);
        $url = $this->base.'/'.$record->getKey();
        $this->postJson($url.'/setujui', ['tahap' => 'pagi'])->assertForbidden();
        $this->actingAs($this->admin)->postJson($url.'/setujui', ['tahap' => 'akhir', 'penerimaan_id' => $row->id])->assertUnprocessable();
        $this->actingAs($this->operator)->putJson($url.'/penerimaan/'.$row->id.'/akhir', $this->finalPayload())->assertRedirect();
        $this->approval($record, 'akhir', $row);
        $this->assertNull($record->fresh()->disetujui_pada);
        $this->assertNull($row->fresh()->penerimaan_disetujui_pada);
        $this->actingAs($this->operator)->putJson($url, $this->payload(['density_awal' => '0.727']))->assertRedirect();
        $this->putJson($url.'/penerimaan/'.$row->id, $this->receipt(['density_penerimaan' => '0.727']))->assertRedirect();
        $this->assertNotNull($row->fresh()->akhir_disetujui_pada);
        $this->assertEquals(0.7387, $row->fresh()->density_15_akhir);
        $this->actingAs($this->admin)->postJson($url.'/setujui', [])->assertUnprocessable()->assertJsonValidationErrors('tahap');
    }

    public function test_time_validation_and_product_family(): void
    {
        $this->postJson($this->base, $this->payload(['density_awal' => '0.820']))->assertUnprocessable()->assertJsonValidationErrors('density_awal');
        $record = $this->save();
        $url = $this->base.'/'.$record->getKey();
        $this->postJson($url.'/penerimaan', $this->receipt(['waktu_penerimaan' => '2026-11-17T05:00']))->assertUnprocessable()->assertJsonValidationErrors('waktu_penerimaan');
        $row = $this->addReceipt($record);
        $this->putJson($url.'/penerimaan/'.$row->id.'/akhir', $this->finalPayload(['waktu_akhir' => '2026-11-18T01:29']))->assertUnprocessable()->assertJsonValidationErrors('waktu_akhir');
        $this->putJson($url.'/penerimaan/'.$row->id.'/akhir', ['density_akhir' => '0.725'])->assertUnprocessable()->assertJsonValidationErrors('waktu_akhir');
        $this->putJson($url.'/penerimaan/'.$row->id.'/akhir', $this->finalPayload())->assertRedirect();
        $this->putJson($url.'/penerimaan/'.$row->id, $this->receipt(['waktu_penerimaan' => '2026-11-18T00:00']))->assertUnprocessable()->assertJsonValidationErrors('waktu_penerimaan');
        $this->putJson($url, $this->payload(['jam_awal' => '23:45']))->assertUnprocessable()->assertJsonValidationErrors('jam_awal');
        $diesel = $this->insert('produk', 'produk_id', ['spbu_id' => $this->spbu, 'nama_produk' => 'Bio Solar', 'singkatan' => 'BS', 'is_aktif' => 1]);
        $tank = $this->tank('DM5', $diesel);
        $this->postJson($this->base, $this->payload(['dombak_id' => $tank]))->assertUnprocessable()->assertJsonValidationErrors('density_awal');
        $this->postJson($this->base, $this->payload(['dombak_id' => $tank, 'density_awal' => '0.820']))->assertRedirect();
    }

    public function test_filters_show_day_in_pending_and_history_with_independent_approvals(): void
    {
        $first = $this->save();
        $row = $this->addReceipt($first);
        $this->save(['tanggal' => '2026-11-16']);
        $this->approval($first, 'pagi');
        $this->get($this->base, $this->inertiaHeaders())->assertOk()->assertJsonPath('props.records.total', 2);
        $this->get($this->base.'/riwayat?mulai=2026-11-17&selesai=2026-11-17&dombak_id='.$this->dombak.'&produk_id='.$this->produk, $this->inertiaHeaders())->assertOk()->assertJsonPath('props.records.total', 1)->assertJsonPath('props.records.data.0.penerimaan.0.penerimaan_disetujui_pada', null);
        $this->get($this->base.'/riwayat?selesai=2026-11-16', $this->inertiaHeaders())->assertOk()->assertJsonPath('props.records.total', 0);
        $this->actingAs($this->operator)->get($this->base.'/create?dombak_id='.$this->dombak.'&tanggal=2026-11-17')->assertRedirect($this->base.'/'.$first->getKey());
        $this->get($this->base.'/create', $this->inertiaHeaders())->assertOk()->assertJsonPath('props.dombak.0.nomor_spbu', '2434520');
        $tera = collect(\App\Support\Navigation::for('Operator'))->firstWhere('label', 'Tera');
        $this->assertSame('Kualitas Minyak', $tera['children'][0]['label']);
        $this->approval($first, 'penerimaan', $row);
        $this->actingAs($this->operator)->putJson($this->base.'/'.$first->getKey().'/penerimaan/'.$row->id.'/akhir', $this->finalPayload())->assertRedirect();
        $this->approval($first, 'akhir', $row);
        $this->get($this->base.'?mulai=2026-11-17', $this->inertiaHeaders())->assertOk()->assertJsonPath('props.records.total', 0);
    }

    public function test_stage_ownership_parent_scoping_and_protected_approval_fields(): void
    {
        $record = $this->save();
        $row = $this->addReceipt($record);
        $url = $this->base.'/'.$record->getKey();
        $other = $this->user('Operator');
        $this->actingAs($other)->putJson($url, $this->payload())->assertForbidden();
        $this->putJson($url.'/penerimaan/'.$row->id, $this->receipt())->assertForbidden();
        $this->putJson($url.'/penerimaan/'.$row->id.'/akhir', $this->finalPayload())->assertRedirect();
        $this->actingAs($this->operator)->putJson($url.'/penerimaan/'.$row->id.'/akhir', $this->finalPayload())->assertForbidden();
        $this->actingAs($this->user('Supervisor'))->postJson($url.'/setujui', ['tahap' => 'penerimaan', 'penerimaan_id' => $row->id])->assertForbidden();
        $this->postJson($url.'/penerimaan', $this->receipt())->assertForbidden();
        $this->actingAs($this->operator)->putJson($url.'/penerimaan/'.$row->id, $this->receipt(['density_15_penerimaan' => '0.7000', 'penerimaan_disetujui_pada' => now(), 'akhir_disetujui_pada' => now()]))->assertRedirect();
        $this->assertEquals(0.7389, $row->fresh()->density_15_penerimaan);
        $this->assertNull($row->fresh()->penerimaan_disetujui_pada);
        $second = $this->save(['tanggal' => '2026-11-16']);
        $secondUrl = $this->base.'/'.$second->getKey();
        $this->putJson($secondUrl.'/penerimaan/'.$row->id.'/akhir', $this->finalPayload())->assertNotFound();
        $this->actingAs($this->admin)->postJson($secondUrl.'/setujui', ['tahap' => 'penerimaan', 'penerimaan_id' => $row->id])->assertNotFound();
    }

    public function test_multiple_receipts_outside_tolerance_and_ids_are_preserved(): void
    {
        $record = $this->save();
        $first = $this->addReceipt($record);
        $second = $this->addReceipt($record, ['nomor_pnbp' => 'PNBP-2', 'truk_berwenang' => false, 'density_15_pnbp' => '0.7200', 'volume_pnbp' => 8000, 'volume_diterima' => 7980]);
        $this->approval($record, 'penerimaan', $first);
        $this->actingAs($this->operator)->putJson($this->base.'/'.$record->getKey().'/penerimaan/'.$second->id, $this->receipt(['nomor_pnbp' => 'PNBP-2', 'truk_berwenang' => false, 'density_15_pnbp' => '0.7200', 'volume_pnbp' => 8000, 'volume_diterima' => 7980]))->assertRedirect();
        $this->assertCount(2, $record->fresh()->penerimaan);
        $this->assertNotNull($first->fresh()->penerimaan_disetujui_pada);
        $this->assertCount(3, $second->peringatan());
        $this->get($this->base.'/'.$record->getKey(), $this->inertiaHeaders())->assertOk()->assertJsonCount(3, 'props.peringatan');
    }
}
