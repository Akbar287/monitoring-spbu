<?php

namespace Tests\Feature;

use App\Models\UserLogin;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InertiaShowPagesTest extends TestCase
{
    /** route prefix => [table, primary key] */
    private array $entities = [
        'spbu' => ['spbu', 'spbu_id'],
        'divisi' => ['divisi', 'divisi_id'],
        'hari' => ['hari', 'hari_id'],
        'jam_kerja' => ['jam_kerja', 'jam_kerja_id'],
        'level' => ['level', 'level_id'],
        'jabatan' => ['jabatan', 'jabatan_id'],
        'pajak_pembelian_lib' => ['pajak_pembelian_lib', 'pajak_pembelian_lib_id'],
        'kategori' => ['kategori', 'kategori_id'],
        'tag' => ['tag', 'tag_id'],
        'aset' => ['aset', 'aset_id'],
        'fasilitas' => ['fasilitas', 'fasilitas_id'],
        'produk' => ['produk', 'produk_id'],
        'dombak' => ['dombak', 'dombak_id'],
        'payung' => ['payung', 'payung_id'],
        'dispenser' => ['dispenser', 'dispenser_id'],
        'nozzle' => ['nozzle', 'nozzle_id'],
        'harga' => ['harga', 'harga_id'],
    ];

    private function inertiaHeaders(): array
    {
        $version = (new \App\Http\Middleware\HandleInertiaRequests())
            ->version(\Illuminate\Http\Request::create('/'));

        return ['X-Inertia' => 'true', 'X-Inertia-Version' => $version];
    }

    public function test_show_pages_render_inertia_detail(): void
    {
        $user = UserLogin::first();
        $this->assertNotNull($user);

        $checked = 0;
        foreach ($this->entities as $prefix => [$table, $pk]) {
            $row = DB::table($table)->first();
            if (! $row) {
                continue;
            }

            $response = $this->actingAs($user)->get(route("$prefix.show", $row->$pk), $this->inertiaHeaders());
            $response->assertOk();
            $json = $response->json();
            $this->assertSame('Show/Index', $json['component'] ?? null, "$prefix.show bukan Show/Index");
            $this->assertNotEmpty($json['props']['fields'] ?? [], "$prefix.show tanpa fields");
            $this->assertNotEmpty($json['props']['deleteUrl'] ?? null, "$prefix.show tanpa deleteUrl");
            $checked++;
        }

        $this->assertGreaterThan(0, $checked, 'tidak ada data untuk uji halaman show');
    }

    public function test_parameterized_index_pages_render_inertia(): void
    {
        $user = UserLogin::first();
        $this->assertNotNull($user);

        // [route name, tabel induk, pk induk, komponen yang diharapkan]
        $pages = [
            ['member.jabatan.index', 'member', 'member_id', 'Table/Index'],
            ['member.jabatan.create', 'member', 'member_id', 'Table/Index'],
            ['payung.dombak', 'payung', 'payung_id', 'Table/Index'],
            ['payung.dombak.create', 'payung', 'payung_id', 'Table/Index'],
            ['konversi.index', 'dombak', 'dombak_id', 'Table/Index'],
            ['konversi.create', 'dombak', 'dombak_id', 'Form/Index'],
            ['konversi.upload_show', 'dombak', 'dombak_id', 'Form/Index'],
            ['aset.file.index', 'aset', 'aset_id', 'Table/Index'],
            ['fasilitas.file.index', 'fasilitas', 'fasilitas_id', 'Table/Index'],
            // show & form transaksional (skip bila tabel kosong)
            ['totalisator.show', 'stand_meter', 'stand_meter_id', 'Show/Index'],
            ['totalisator.edit', 'stand_meter', 'stand_meter_id', 'Totalisator/Final'],
            ['totalisator.final', 'stand_meter', 'stand_meter_id', 'Totalisator/Final'],
            ['stok-taking.show', 'dokumen_stok', 'dokumen_stok_id', 'Show/Index'],
            ['stok-taking.riwayat.show', 'dokumen_stok', 'dokumen_stok_id', 'Show/Index'],
            ['stok-transfer.show', 'dombak_transfer', 'dombak_transfer_id', 'Show/Index'],
        ];

        $checked = 0;
        foreach ($pages as [$routeName, $table, $pk, $component]) {
            $row = DB::table($table)->first();
            if (! $row) {
                continue;
            }

            $response = $this->actingAs($user)->get(route($routeName, $row->$pk), $this->inertiaHeaders());
            $response->assertOk();
            $json = $response->json();
            $this->assertSame($component, $json['component'] ?? null, "$routeName bukan $component");
            $checked++;
        }

        $this->assertGreaterThan(0, $checked, 'tidak ada data induk untuk uji halaman param');
    }
}
