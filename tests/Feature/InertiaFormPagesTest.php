<?php

namespace Tests\Feature;

use App\Models\UserLogin;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InertiaFormPagesTest extends TestCase
{
    /** route prefix => [table, primary key] utk halaman edit */
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
        'petty-cash' => ['petty_cash', 'petty_cash_id'],
    ];

    private function inertiaHeaders(): array
    {
        $version = (new \App\Http\Middleware\HandleInertiaRequests())
            ->version(\Illuminate\Http\Request::create('/'));

        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => $version,
        ];
    }

    public function test_create_pages_render_inertia_form(): void
    {
        $user = UserLogin::first();
        $this->assertNotNull($user);

        foreach (array_keys($this->entities) as $prefix) {
            $response = $this->actingAs($user)->get(route("$prefix.create"), $this->inertiaHeaders());
            $response->assertOk();
            $json = $response->json();
            $this->assertSame('Form/Index', $json['component'] ?? null, "$prefix.create bukan Form/Index");
            $this->assertNotEmpty($json['props']['fields'] ?? [], "$prefix.create tanpa fields");
        }
    }

    public function test_home_and_login_render_inertia(): void
    {
        // login dicek dulu (guest) — actingAs membuat sesi terautentikasi
        $login = $this->get('/login', $this->inertiaHeaders());
        $login->assertOk();
        $this->assertSame('Auth/Login', $login->json()['component'] ?? null);

        $user = UserLogin::first();
        $this->assertNotNull($user);

        $home = $this->actingAs($user)->get('/home', $this->inertiaHeaders());
        $home->assertOk();
        $this->assertSame('Home/Index', $home->json()['component'] ?? null);
    }

    public function test_import_page_renders_inertia_form(): void
    {
        $user = UserLogin::first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get(route('import.index'), $this->inertiaHeaders());
        $response->assertOk();
        $json = $response->json();
        $this->assertSame('Form/Index', $json['component'] ?? null);
        $this->assertSame('file', $json['props']['fields'][1]['type'] ?? null);
    }

    public function test_chained_create_forms_render_dedicated_components(): void
    {
        $user = UserLogin::first();
        $this->assertNotNull($user);

        $pages = [
            ['stok-taking.create', 'StokTaking/Create', ['options', 'api']],
            ['totalisator.create', 'Totalisator/Create', ['dombakOptions', 'apiUrl']],
            ['stok-transfer.create', 'StokTransfer/Create', ['jamKerjaOptions', 'dombakOptions', 'api']],
            ['post.create', 'Form/Index', ['fields']],
            ['setoran.open.create', 'Setoran/OpenCreate', ['spbuOptions', 'jamKerjaOptions', 'nozzleOptions', 'api']],
            ['perencanaan.create', 'Form/Index', ['fields']],
        ];

        foreach ($pages as [$routeName, $component, $props]) {
            $response = $this->actingAs($user)->get(route($routeName), $this->inertiaHeaders());
            $response->assertOk();
            $json = $response->json();
            $this->assertSame($component, $json['component'] ?? null, "$routeName bukan $component");
            foreach ($props as $p) {
                $this->assertArrayHasKey($p, $json['props'] ?? [], "$routeName tanpa prop $p");
            }
        }
    }

    public function test_edit_pages_render_inertia_form(): void
    {
        $user = UserLogin::first();
        $this->assertNotNull($user);

        $checked = 0;
        foreach ($this->entities as $prefix => [$table, $pk]) {
            $row = DB::table($table)->first();
            if (! $row) {
                continue; // tabel kosong di lingkungan ini — lewati
            }

            $response = $this->actingAs($user)->get(route("$prefix.edit", $row->$pk), $this->inertiaHeaders());
            $response->assertOk();
            $json = $response->json();
            $this->assertSame('Form/Index', $json['component'] ?? null, "$prefix.edit bukan Form/Index");
            $this->assertSame('put', $json['props']['method'] ?? null, "$prefix.edit method bukan put");
            $checked++;
        }

        $this->assertGreaterThan(0, $checked, 'tidak ada tabel berisi data untuk uji halaman edit');
    }
}
