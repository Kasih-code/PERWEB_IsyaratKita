<?php

namespace Tests\Feature;

use App\Models\Modul;
use App\Models\ProgresModul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tes Fase 2 (User): login, proteksi route, quiz, profil, donasi, dan regresi Guest.
 */
class HalamanUserTest extends TestCase
{
    use RefreshDatabase;

    /** Data modul/materi/quiz berasal dari database, jadi diisi lebih dulu oleh seeder. */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(string $nama = 'Andi Pratama'): User
    {
        return User::create(['name' => $nama, 'email' => 'pengguna.tes@email.com', 'password' => 'password']);
    }

    private function selesaikan(User $user, int $modul): void
    {
        ProgresModul::create(['user_id' => $user->id, 'modul_id' => $modul, 'status' => 'selesai',
            'skor_terakhir' => 9, 'total_soal_terakhir' => 10, 'skor_terbaik' => 9, 'jumlah_percobaan' => 1]);
    }

    /** [pertanyaan_id => pilihan_id] untuk modul tertentu; $salahIndeks = indeks soal yang sengaja dijawab salah. */
    private function jawaban(int $modul, array $salahIndeks = [], bool $semuaSalah = false): array
    {
        $hasil = [];
        foreach (Modul::find($modul)->kuis->pertanyaan()->with('pilihan')->get() as $i => $p) {
            $salah = $semuaSalah || in_array($i, $salahIndeks, true);
            $hasil[$p->id] = $p->pilihan->first(fn ($o) => $o->benar !== $salah)->id;
        }

        return $hasil;
    }

    private function jawabanBenar(int $modul): array
    {
        return $this->jawaban($modul);
    }

    /* ---------- Autentikasi ---------- */

    public function test_login_berhasil_dan_logout(): void
    {
        $this->user();

        $this->post(route('login.proses'), ['email' => 'pengguna.tes@email.com', 'kata_sandi' => 'password'])
            ->assertRedirect(route('beranda'));
        $this->assertAuthenticated();

        $this->post(route('logout'))->assertRedirect(route('beranda'));
        $this->assertGuest();
    }

    public function test_login_gagal_membuka_kembali_modal(): void
    {
        $this->user();

        $this->from(route('beranda'))
            ->post(route('login.proses'), ['email' => 'pengguna.tes@email.com', 'kata_sandi' => 'salah'])
            ->assertRedirect(route('beranda'))
            ->assertSessionHasErrors('email', null, 'login');
        $this->assertGuest();

        $this->get(route('beranda'))->assertSee('data-buka-otomatis', false);
    }

    public function test_daftar_membuat_akun_lalu_meminta_user_masuk(): void
    {
        $this->post(route('daftar.proses'), [
            'nama' => 'Budi Santoso', 'email' => 'budi@email.com', 'kata_sandi' => 'rahasia123',
        ])->assertRedirect(route('beranda'))
            ->assertSessionHas('buka_modal_login', true);

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'budi@email.com', 'name' => 'Budi Santoso']);
    }

    public function test_daftar_menolak_email_terdaftar_dan_sandi_pendek(): void
    {
        $this->user();

        $this->post(route('daftar.proses'), ['nama' => 'X Y', 'email' => 'pengguna.tes@email.com', 'kata_sandi' => '123'])
            ->assertSessionHasErrors(['email', 'kata_sandi'], null, 'login');
        $this->assertGuest();
    }

    public function test_redirect_setelah_login_hanya_ke_situs_sendiri(): void
    {
        $this->user();

        $this->post(route('login.proses'), ['email' => 'pengguna.tes@email.com', 'kata_sandi' => 'password', 'kembali' => 'https://jahat.example/x'])
            ->assertRedirect(route('beranda'));
    }

    /* ---------- Proteksi route ---------- */

    public function test_route_user_mengalihkan_guest_ke_login(): void
    {
        $this->get(route('modul.quiz.tampil', 1))->assertRedirect(route('login'));
        $this->get(route('modul.quiz.hasil', 1))->assertRedirect(route('login'));
        $this->post(route('modul.quiz.kirim', 1))->assertRedirect(route('login'));
        $this->patch(route('profil.update'), ['nama' => 'Baru'])->assertRedirect(route('login'));
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->get(route('login'))->assertOk()->assertSee('data-buka-otomatis', false);
    }

    public function test_login_setelah_diarahkan_kembali_ke_quiz(): void
    {
        $this->user();
        $this->get(route('modul.quiz.tampil', 1));

        $this->post(route('login.proses'), ['email' => 'pengguna.tes@email.com', 'kata_sandi' => 'password'])
            ->assertRedirect(route('modul.quiz.tampil', 1));
    }

    public function test_user_login_tidak_bisa_membuka_form_masuk(): void
    {
        $this->actingAs($this->user())->get(route('login'))->assertRedirect('/');
    }

    /* ---------- Halaman User ---------- */

    public function test_navbar_user_menampilkan_nama_dan_menu_profil(): void
    {
        $this->actingAs($this->user())->get(route('beranda'))
            ->assertOk()
            ->assertSee('Andi Pratama')
            ->assertSee('Edit Profil')
            ->assertSee('Keluar')
            ->assertDontSee('Daftar Gratis');
    }

    public function test_modul_user_status_dan_progres(): void
    {
        $user = $this->user();
        $this->selesaikan($user, 1);

        $this->actingAs($user)->get(route('modul.index'))
            ->assertOk()
            ->assertSee('Ulangi Materi')
            ->assertSee('Mulai Belajar')
            ->assertSee('Sedang Dipelajari')
            ->assertSee('20%')
            ->assertDontSee('data-buka-otomatis', false);
    }

    public function test_modul_terkunci_dialihkan_dengan_pesan(): void
    {
        $user = $this->user();

        $this->actingAs($user)->get(route('modul.detail', 3))
            ->assertRedirect(route('modul.index'))
            ->assertSessionHas('galat');
        $this->actingAs($user)->get(route('modul.quiz.tampil', 2))->assertRedirect(route('modul.index'));
    }

    public function test_detail_modul_user_membuat_progres_dan_tombol_quiz(): void
    {
        $user = $this->user();

        // Materi dibagi per heading; tombol quiz muncul pada materi terakhir.
        $this->actingAs($user)->get(route('modul.detail', 1))
            ->assertOk()->assertSee('Materi 1 dari 3')->assertSee('Materi Berikutnya');
        $this->get(route('modul.detail', ['id' => 1, 'materi' => 3]))
            ->assertOk()->assertSee(route('modul.quiz.tampil', 1), false);
        $this->assertDatabaseHas('progres_modul', ['user_id' => $user->id, 'modul_id' => 1, 'status' => 'berjalan']);
    }

    /* ---------- Quiz ---------- */

    public function test_halaman_quiz_dapat_dibuka(): void
    {
        $this->actingAs($this->user())->get(route('modul.quiz.tampil', 1))
            ->assertOk()->assertSee('Quiz Modul 1')->assertSee('Pertanyaan');
    }

    public function test_quiz_semua_benar_menyelesaikan_modul_dan_membuka_berikutnya(): void
    {
        $user = $this->user();

        $this->actingAs($user)->post(route('modul.quiz.kirim', 1), ['jawaban' => $this->jawabanBenar(1)])
            ->assertRedirect(route('modul.quiz.hasil', 1));

        $this->assertDatabaseHas('progres_modul', ['user_id' => $user->id, 'modul_id' => 1, 'status' => 'selesai', 'skor_terakhir' => 10]);

        $this->get(route('modul.quiz.hasil', 1))
            ->assertOk()->assertSee('Selamat!')->assertSee('10 / 10')->assertSee('Lanjut ke Modul Berikutnya')->assertSee('20% Selesai');
        $this->get(route('modul.detail', 2))->assertOk();
    }

    public function test_quiz_nilai_rendah_tidak_menyelesaikan_modul(): void
    {
        $user = $this->user();
        $salah = $this->jawaban(1, semuaSalah: true);

        $this->actingAs($user)->post(route('modul.quiz.kirim', 1), ['jawaban' => $salah])
            ->assertRedirect(route('modul.quiz.hasil', 1));

        $this->assertDatabaseHas('progres_modul', ['user_id' => $user->id, 'modul_id' => 1, 'status' => 'berjalan', 'skor_terakhir' => 0]);
        $this->get(route('modul.quiz.hasil', 1))->assertSee('Belum Berhasil')->assertDontSee('Lanjut ke Modul Berikutnya');
        $this->get(route('modul.detail', 2))->assertRedirect(route('modul.index'));
    }

    public function test_quiz_9_dari_10_tampil_seperti_desain(): void
    {
        $user = $this->user();
        $jawaban = $this->jawaban(1, [9]);

        $this->actingAs($user)->post(route('modul.quiz.kirim', 1), ['jawaban' => $jawaban]);
        $this->get(route('modul.quiz.hasil', 1))->assertSee('9 / 10')->assertSee('Sangat Bagus!');
    }

    public function test_quiz_menolak_jawaban_tidak_lengkap(): void
    {
        $user = $this->user();

        $this->actingAs($user)->from(route('modul.quiz.tampil', 1))
            ->post(route('modul.quiz.kirim', 1), ['jawaban' => array_slice($this->jawabanBenar(1), 0, 2, true)])
            ->assertRedirect(route('modul.quiz.tampil', 1))
            ->assertSessionHas('galat');
        $this->assertDatabaseMissing('progres_modul', ['skor_terakhir' => 0]);
    }

    public function test_hasil_tanpa_percobaan_kembali_ke_quiz_dan_modul_tidak_ada_404(): void
    {
        $user = $this->user();

        $this->actingAs($user)->get(route('modul.quiz.hasil', 1))->assertRedirect(route('modul.quiz.tampil', 1));
        $this->get('/modul/99/quiz')->assertNotFound();
    }

    public function test_modul_selesai_tidak_turun_status_saat_ulang_quiz_gagal(): void
    {
        $user = $this->user();
        $this->selesaikan($user, 1);
        $salah = $this->jawaban(1, semuaSalah: true);

        $this->actingAs($user)->post(route('modul.quiz.kirim', 1), ['jawaban' => $salah]);

        $this->assertDatabaseHas('progres_modul', ['user_id' => $user->id, 'modul_id' => 1, 'status' => 'selesai', 'skor_terbaik' => 9, 'skor_terakhir' => 0]);
    }

    /* ---------- Profil ---------- */

    public function test_edit_profil_mengubah_nama_saja(): void
    {
        $user = $this->user();

        $this->actingAs($user)->patch(route('profil.update'), ['nama' => 'Andi Baru', 'email' => 'x@y.com'])
            ->assertSessionHas('sukses');

        $user->refresh();
        $this->assertSame('Andi Baru', $user->name);
        $this->assertSame('pengguna.tes@email.com', $user->email);
    }

    public function test_edit_profil_validasi_nama_kosong(): void
    {
        $this->actingAs($this->user())->patch(route('profil.update'), ['nama' => ''])
            ->assertSessionHasErrors('nama', null, 'profil');
    }

    public function test_popup_profil_terbuka_otomatis_setelah_galat(): void
    {
        $this->actingAs($this->user())->from(route('beranda'))
            ->patch(route('profil.update'), ['nama' => ''])
            ->assertRedirect(route('beranda'));

        $html = $this->get(route('beranda'))->getContent();
        $this->assertMatchesRegularExpression('/id="modal-profil"[^>]*data-buka-otomatis/s', $html);
        $this->assertStringContainsString('wajib diisi', $html);
    }

    /* ---------- Donasi ---------- */

    public function test_donasi_user_menampilkan_form_aktif_dan_snap_midtrans(): void
    {
        $this->actingAs($this->user())->get(route('donasi.index'))
            ->assertOk()
            ->assertSee('data-form-donasi', false)
            ->assertSee('snap.js', false)
            ->assertDontSee('data-buka-otomatis', false);
    }

    /* ---------- Regresi Guest ---------- */

    public function test_guest_masih_dapat_membuka_semua_halaman_publik(): void
    {
        foreach ([route('beranda'), route('kamus.index'), route('kamus.index', ['huruf' => 'M']),
                  route('kamus.index', ['cari' => 'aku']), route('kamus.detail', 'makan'),
                  route('modul.index'), route('modul.detail', 1), route('donasi.index')] as $alamat) {
            $this->get($alamat)->assertOk();
        }
    }

    public function test_guest_modul_terkunci_dan_modal_login_otomatis(): void
    {
        $this->get(route('modul.index'))->assertSee('data-buka-otomatis', false)->assertSee('Terkunci')->assertSee('0%');
        $this->get(route('modul.detail', 1))->assertSee('data-buka-otomatis', false);
        $this->get(route('donasi.index'))->assertSee('data-buka-otomatis', false)->assertDontSee('data-form-donasi', false);
        $this->get(route('beranda'))->assertSee('Daftar Gratis')->assertDontSee('data-buka-otomatis', false);
    }

    public function test_kamus_user_tetap_bisa_cari_filter_dan_detail(): void
    {
        $this->actingAs($this->user());

        $this->get(route('kamus.index', ['cari' => 'selamat']))->assertSee('Selamat Pagi');
        $this->get(route('kamus.index', ['huruf' => 'B']))->assertSee('Baca');
        $this->get(route('kamus.detail', 'makan'))->assertOk()->assertSee('MAKAN')->assertSee('Huruf 5');
    }
}
