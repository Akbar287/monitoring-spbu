<?php

namespace Tests\Feature;

use App\Models\UserLogin;
use Tests\TestCase;

class InertiaMenuPagesTest extends TestCase
{
    /**
     * Semua halaman menu yang sudah dimigrasi ke Inertia harus merender
     * root template dengan payload data-page (bukan error / Blade lama).
     * Test ini read-only terhadap database.
     */
    public function test_menu_pages_render_inertia(): void
    {
        $user = UserLogin::first();
        $this->assertNotNull($user, 'Butuh minimal satu user di database untuk test ini.');

        $pages = ['/master', '/procurement', '/stok', '/tera', '/setoran', '/artikel', '/financial', '/jadwal_absensi', '/konfigurasi'];

        foreach ($pages as $page) {
            $response = $this->actingAs($user)->get($page);
            $response->assertOk();
            $response->assertSee('data-page', false);
        }
    }
}
