<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Tes dasar Fase 1 (Guest): semua halaman dapat dibuka.
 * Jalankan dengan: php artisan test
 */
class HalamanGuestTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /** Modul berasal dari database, jadi diisi dulu oleh seeder. */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_halaman_utama_guest_dapat_dibuka(): void
    {
        $daftarRute = [
            route('beranda'),
            route('kamus.index'),
            route('kamus.index', ['huruf' => 'M']),
            route('kamus.detail', 'makan'),
            route('modul.index'),
            route('modul.detail', 1),
            route('donasi.index'),
        ];

        foreach ($daftarRute as $alamat) {
            $this->get($alamat)->assertOk();
        }
    }

    public function test_halaman_modul_dan_donasi_membuka_modal_login_otomatis(): void
    {
        $this->get(route('modul.index'))->assertSee('data-buka-otomatis', false);
        $this->get(route('donasi.index'))->assertSee('data-buka-otomatis', false);
    }

    public function test_kata_dan_modul_yang_tidak_ada_menghasilkan_404(): void
    {
        $this->get('/kamus/kata-yang-tidak-ada')->assertNotFound();
        $this->get('/modul/999')->assertNotFound();
    }
}
