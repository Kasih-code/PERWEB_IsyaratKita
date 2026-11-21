<?php

namespace Tests\Feature;

use App\Models\AsetBisindo;
use App\Models\Donasi;
use App\Models\Modul;
use App\Models\Pertanyaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Tes Fase 3 (Admin CMS): autentikasi, otorisasi, CRUD, upload, quiz, donasi, profil,
 * dan pengaruh perubahan Admin terhadap halaman Guest/User.
 */
class AdminCmsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('unggahan');
        $this->admin = User::where('email', 'andi@isyaratkita.id')->firstOrFail();
    }

    private function biasa(): User
    {
        $user = User::firstOrCreate(
            ['email' => 'andi@email.com'],
            ['name' => 'Andi', 'password' => 'password123']
        );

        // Modul 2 baru terbuka kalau Modul 1 sudah selesai.
        \App\Models\ProgresModul::firstOrCreate(
            ['user_id' => $user->id, 'modul_id' => 1],
            ['status' => 'selesai', 'skor_terakhir' => 9, 'total_soal_terakhir' => 10, 'skor_terbaik' => 9, 'jumlah_percobaan' => 1]
        );

        return $user;
    }

    /* ---------- Autentikasi & otorisasi ---------- */

    public function test_semua_route_admin_menolak_guest(): void
    {
        foreach (['admin.dashboard', 'admin.asset.index', 'admin.modul.index', 'admin.modul.create', 'admin.donasi.index',
                  'admin.profil', 'admin.profil.edit'] as $nama) {
            $this->get(route($nama))->assertRedirect(route('admin.login'));
        }
        $this->get(route('admin.kuis.index', 1))->assertRedirect(route('admin.login'));
        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
    }

    public function test_user_biasa_mendapat_403_di_semua_route_admin(): void
    {
        $user = $this->biasa();
        foreach (['admin.dashboard', 'admin.asset.index', 'admin.modul.index', 'admin.donasi.index', 'admin.profil'] as $nama) {
            $this->actingAs($user)->get(route($nama))->assertForbidden();
        }
        $this->actingAs($user)->post(route('admin.modul.store'), ['judul' => 'x', 'status' => 'draft', 'nomor' => 1])->assertForbidden();
        $this->actingAs($user)->delete(route('admin.modul.destroy', 1))->assertForbidden();
        $this->actingAs($user)->post(route('admin.asset.store'), ['kata' => 'x'])->assertForbidden();
        $this->assertDatabaseHas('modul', ['id' => 1]);
    }

    public function test_login_admin_berhasil_dan_user_biasa_ditolak(): void
    {
        $this->post(route('admin.login.proses'), ['email' => 'andi@email.com', 'kata_sandi' => 'password123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('admin.login.proses'), ['email' => 'andi@isyaratkita.id', 'kata_sandi' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->post(route('admin.login.proses'), ['email' => 'andi@isyaratkita.id', 'kata_sandi' => 'admin12345'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
        $this->assertNotNull($this->admin->fresh()->terakhir_login_pada);
    }

    public function test_logout_admin(): void
    {
        $this->actingAs($this->admin)->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_pendaftaran_publik_tidak_bisa_membuat_admin(): void
    {
        $this->post(route('daftar.proses'), ['nama' => 'Jahat', 'email' => 'jahat@email.com', 'kata_sandi' => 'password123', 'role' => 'admin']);
        $this->assertSame('user', User::where('email', 'jahat@email.com')->first()->role);
    }

    public function test_semua_halaman_admin_dapat_dibuka_admin(): void
    {
        foreach ([route('admin.dashboard'), route('admin.asset.index'), route('admin.modul.index'), route('admin.modul.create'),
                  route('admin.modul.edit', 2), route('admin.kuis.index', 2), route('admin.kuis.soal.create', 2),
                  route('admin.donasi.index'), route('admin.profil'), route('admin.profil.edit')] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    /* ---------- Dashboard ---------- */

    public function test_dashboard_memakai_data_database(): void
    {
        Donasi::create(['kode' => 'IK-DON-X-0001', 'nama' => 'Donor Uji', 'email' => 'd@x.test', 'whatsapp' => '081200000000',
            'nominal' => 2500000, 'status' => 'berhasil', 'simulasi' => true, 'dibayar_pada' => now()]);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertSee('Total Pengguna')->assertSee('Rp 2,5 jt')->assertSee('Donor Uji')->assertSee('Termasuk donasi simulasi');
    }

    /* ---------- Asset ---------- */

    public function test_admin_menambah_mengubah_dan_menghapus_asset_yang_memengaruhi_kamus(): void
    {
        $this->actingAs($this->admin)->post(route('admin.asset.store'), [
            'kata' => 'Tulip Uji', 'status' => 'aktif', 'berkas' => UploadedFile::fake()->image('a.png', 600, 450),
        ])->assertRedirect(route('admin.asset.index'));

        $aset = AsetBisindo::firstWhere('kata', 'Tulip Uji');
        Storage::disk('unggahan')->assertExists($aset->berkas);
        $this->get(route('kamus.index', ['huruf' => 'T']))->assertSee('Tulip Uji');
        $this->get(route('kamus.detail', 'tulip-uji'))->assertOk();

        $this->put(route('admin.asset.update', $aset), ['kata' => 'Tulip Uji', 'status' => 'nonaktif'])->assertRedirect();
        $this->get(route('beranda')); // menghabiskan pesan flash "berhasil diperbarui" agar tidak ikut tampil
        $this->get(route('kamus.index', ['huruf' => 'T']))->assertDontSee('Tulip Uji');

        $berkas = $aset->fresh()->berkas;
        $this->delete(route('admin.asset.destroy', $aset))->assertRedirect(route('admin.asset.index'));
        $this->assertDatabaseMissing('aset_bisindo', ['id' => $aset->id]);
        Storage::disk('unggahan')->assertMissing($berkas);
    }

    public function test_upload_asset_menolak_berkas_berbahaya_atau_terlalu_besar(): void
    {
        $this->actingAs($this->admin);
        foreach ([
            UploadedFile::fake()->create('x.php', 10, 'image/png'),
            UploadedFile::fake()->create('x.svg', 10, 'image/svg+xml'),
            UploadedFile::fake()->image('besar.png')->size(6000),
        ] as $berkas) {
            $this->post(route('admin.asset.store'), ['kata' => 'Uji', 'status' => 'aktif', 'berkas' => $berkas])
                ->assertSessionHasErrorsIn('asset', 'berkas');
        }
        $this->assertSame(0, AsetBisindo::count());
    }

    /* ---------- Modul & materi ---------- */

    private function dataModul(array $tambahan = []): array
    {
        return array_merge([
            'judul' => 'Modul Uji', 'deskripsi' => 'Deskripsi', 'status' => 'terbit', 'nomor' => 6, 'tampil_publik' => 1,
            'blok' => [
                ['tipe' => 'heading', 'teks' => 'Bagian Uji'],
                ['tipe' => 'teks', 'teks' => 'Isi uji coba.'],
                ['tipe' => 'video', 'video_url' => 'https://youtu.be/abcdefghijk', 'durasi' => '03:24'],
            ],
        ], $tambahan);
    }

    public function test_admin_membuat_modul_yang_tampil_ke_guest_dan_draft_tersembunyi(): void
    {
        $this->actingAs($this->admin)->post(route('admin.modul.store'), $this->dataModul())->assertRedirect();
        $modul = Modul::firstWhere('judul', 'Modul Uji');
        $this->assertSame(6, $modul->nomor);
        $this->assertSame($this->admin->id, $modul->dibuat_oleh);
        $this->assertCount(3, $modul->blok);
        $this->assertNotNull($modul->kuis);

        auth()->logout();
        $this->get(route('modul.index'))->assertSee('Modul Uji');
        $this->get(route('modul.detail', $modul))->assertOk()->assertSee('Bagian Uji')->assertSee('youtube-nocookie.com/embed/abcdefghijk', false);

        $modul->update(['status' => 'draft']);
        $this->get(route('modul.index'))->assertDontSee('Modul Uji');
        $this->get(route('modul.detail', $modul))->assertNotFound();
        $this->actingAs($this->admin)->get(route('modul.detail', $modul))->assertOk()->assertSee('Pratinjau Admin');
    }

    public function test_validasi_modul_dan_blok(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('admin.modul.store'), $this->dataModul(['judul' => '']))->assertSessionHasErrors('judul');
        $this->post(route('admin.modul.store'), $this->dataModul(['status' => 'rahasia']))->assertSessionHasErrors('status');
        $this->post(route('admin.modul.store'), $this->dataModul(['blok' => [['tipe' => 'teks', 'teks' => '']]]))->assertSessionHasErrors('blok.0.teks');
        $this->post(route('admin.modul.store'), $this->dataModul(['blok' => [['tipe' => 'video', 'video_url' => 'javascript:alert(1)']]]))->assertSessionHasErrors('blok.0.video_url');
        $this->post(route('admin.modul.store'), $this->dataModul(['blok' => [['tipe' => 'gambar']]]))->assertSessionHasErrors('blok.0.gambar');
        $this->assertSame(5, Modul::count());
    }

    public function test_ubah_urutan_dan_hapus_modul_merapikan_nomor(): void
    {
        $this->actingAs($this->admin);
        $modul5 = Modul::where('nomor', 5)->first();
        $this->put(route('admin.modul.update', $modul5), ['judul' => $modul5->judul, 'status' => 'terbit', 'nomor' => 1, 'tampil_publik' => 1])->assertRedirect();
        $this->assertSame(1, $modul5->fresh()->nomor);
        $this->assertSame([1, 2, 3, 4, 5], Modul::urut()->pluck('nomor')->all());

        $this->delete(route('admin.modul.destroy', $modul5))->assertRedirect(route('admin.modul.index'));
        $this->assertSame([1, 2, 3, 4], Modul::urut()->pluck('nomor')->all());
        $this->assertDatabaseMissing('kuis', ['modul_id' => $modul5->id]);
        $this->assertDatabaseMissing('materi_blok', ['modul_id' => $modul5->id]);
    }

    public function test_blok_gambar_tersimpan_dan_dipertahankan_saat_edit(): void
    {
        $this->actingAs($this->admin)->post(route('admin.modul.store'), $this->dataModul([
            'blok' => [['tipe' => 'gambar', 'alt' => 'alt', 'gambar' => UploadedFile::fake()->image('g.png')]],
        ]));
        $modul = Modul::firstWhere('judul', 'Modul Uji');
        $blok = $modul->blok->first();
        Storage::disk('unggahan')->assertExists($blok->gambar);

        $this->put(route('admin.modul.update', $modul), $this->dataModul(['nomor' => 6, 'blok' => [['id' => $blok->id, 'tipe' => 'gambar', 'alt' => 'baru']]]))->assertRedirect();
        $this->assertSame($blok->gambar, $blok->fresh()->gambar);
        $this->assertSame('baru', $blok->fresh()->alt);
    }

    /* ---------- Quiz ---------- */

    public function test_admin_mengelola_soal_dan_user_memakainya(): void
    {
        $this->actingAs($this->admin);
        $modul = Modul::find(2);

        $this->put(route('admin.kuis.update', $modul), ['status' => 'aktif', 'urutan_soal' => 'acak', 'nilai_lulus' => 60, 'durasi_menit' => 5])->assertRedirect();
        $this->assertDatabaseHas('kuis', ['modul_id' => 2, 'nilai_lulus' => 60, 'urutan_soal' => 'acak', 'durasi_menit' => 5]);
        $this->put(route('admin.kuis.update', $modul), ['status' => 'aktif', 'urutan_soal' => 'acak', 'nilai_lulus' => 150])->assertSessionHasErrors('nilai_lulus');

        $this->post(route('admin.kuis.soal.store', $modul), [
            'teks' => 'Soal bergambar?', 'tipe_jawaban' => 'teks_gambar', 'poin' => 10, 'kesulitan' => 'dasar', 'acak_pilihan' => 1, 'benar' => 1,
            'pembahasan' => 'Karena B.',
            'gambar' => UploadedFile::fake()->image('q.png'),
            'pilihan' => [
                ['teks' => 'A', 'gambar' => UploadedFile::fake()->image('a.png')],
                ['teks' => 'B', 'gambar' => UploadedFile::fake()->image('b.png')],
            ],
        ])->assertRedirect(route('admin.kuis.index', $modul));

        $soal = Pertanyaan::firstWhere('teks', 'Soal bergambar?');
        $this->assertCount(2, $soal->pilihan);
        $this->assertTrue($soal->pilihan[1]->benar);
        $this->assertFalse($soal->pilihan[0]->benar);
        Storage::disk('unggahan')->assertExists($soal->gambar);

        // Validasi: tipe gambar tanpa gambar, kurang dari 2 pilihan, indeks benar di luar jangkauan.
        $dasar = ['teks' => 'X', 'poin' => 10, 'kesulitan' => 'dasar', 'benar' => 0];
        $this->post(route('admin.kuis.soal.store', $modul), $dasar + ['tipe_jawaban' => 'gambar', 'pilihan' => [['teks' => ''], ['teks' => '']]])->assertSessionHasErrors('pilihan.0.gambar');
        $this->post(route('admin.kuis.soal.store', $modul), $dasar + ['tipe_jawaban' => 'teks', 'pilihan' => [['teks' => 'a']]])->assertSessionHasErrors('pilihan');
        $this->post(route('admin.kuis.soal.store', $modul), ['benar' => 5] + $dasar + ['tipe_jawaban' => 'teks', 'pilihan' => [['teks' => 'a'], ['teks' => 'b']]])->assertSessionHasErrors('benar');

        // Soal milik modul lain tidak bisa diakses lewat modul ini.
        $this->get(route('admin.kuis.soal.edit', [Modul::find(1), $soal]))->assertNotFound();

        // User mengerjakan soal baru; nilai lulus 60% berlaku.
        $user = $this->biasa();
        $jawaban = $modul->kuis->pertanyaan()->with('pilihan')->get()->mapWithKeys(fn ($p) => [$p->id => $p->pilihan->firstWhere('benar', true)->id])->all();
        $this->actingAs($user)->get(route('modul.quiz.tampil', $modul))->assertOk()->assertSee('data-batas-detik="300"', false)->assertSee('Soal bergambar?');
        $this->post(route('modul.quiz.kirim', $modul), ['jawaban' => $jawaban])->assertRedirect(route('modul.quiz.hasil', $modul));
        $this->assertDatabaseHas('percobaan_kuis', ['user_id' => $user->id, 'kuis_id' => $modul->kuis->id, 'nilai' => 100, 'lulus' => 1]);

        $this->actingAs($this->admin)->delete(route('admin.kuis.soal.destroy', [$modul, $soal]))->assertRedirect();
        $this->assertDatabaseMissing('pertanyaan', ['id' => $soal->id]);
    }

    public function test_quiz_nonaktif_tidak_bisa_dikerjakan_user(): void
    {
        Modul::find(2)->kuis->update(['status' => 'nonaktif']);
        $this->actingAs($this->biasa())->get(route('modul.quiz.tampil', 2))->assertRedirect(route('modul.detail', 2));
    }

    /* ---------- Donasi ---------- */

    public function test_alur_donasi_user_tercatat_dan_tampil_di_laporan_admin_sebagai_simulasi(): void
    {
        $this->markTestSkipped('Alur simulasi sudah diganti Midtrans Snap; tes perlu ditulis ulang dengan Midtrans di-mock.');

        $user = $this->biasa();
        $r = $this->actingAs($user)->postJson(route('donasi.simpan'), ['nama' => 'Andi', 'email' => 'andi@email.com', 'whatsapp' => '0812 3456 7890', 'nominal' => 100000])
            ->assertCreated();
        $kode = $r->json('kode');
        $this->assertDatabaseHas('donasi', ['kode' => $kode, 'user_id' => $user->id, 'status' => 'menunggu', 'simulasi' => 1, 'whatsapp' => '081234567890']);

        // Validasi & otorisasi
        $this->postJson(route('donasi.simpan'), ['nama' => '', 'email' => 'x', 'whatsapp' => '1', 'nominal' => 123])->assertUnprocessable();
        $lain = User::create(['name' => 'Lain', 'email' => 'lain@email.com', 'password' => 'password123']);
        $this->actingAs($lain)->postJson(route('donasi.bayar', $kode))->assertForbidden();

        $this->actingAs($user)->postJson(route('donasi.bayar', $kode))->assertOk()->assertJson(['status' => 'berhasil', 'simulasi' => true]);

        $this->actingAs($this->admin)->get(route('admin.donasi.index'))->assertSee('Andi')->assertSee('Simulasi')->assertSee('Rp 100.000');
        $this->get(route('admin.donasi.index', ['cari' => '3456']))->assertSee($kode, false);
        $this->get(route('admin.donasi.export'))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $id = Donasi::firstWhere('kode', $kode)->id;
        $this->get(route('admin.donasi.bukti', $id))->assertOk()->assertSee('SIMULASI');
    }

    public function test_donasi_guest_ditolak(): void
    {
        $this->postJson(route('donasi.simpan'), ['nama' => 'A'])->assertUnauthorized();
    }

    /* ---------- Profil ---------- */

    public function test_admin_mengubah_profil_dan_kata_sandi(): void
    {
        $this->actingAs($this->admin);
        $this->put(route('admin.profil.update'), ['name' => 'Andi Baru', 'email' => 'andi@isyaratkita.id', 'whatsapp' => '0812 8899 1044',
            'foto' => UploadedFile::fake()->image('f.png', 100, 100)])->assertRedirect(route('admin.profil'));
        $this->admin->refresh();
        $this->assertSame('Andi Baru', $this->admin->name);
        Storage::disk('unggahan')->assertExists($this->admin->foto);

        // Ganti sandi wajib sandi lama yang benar; role tidak bisa diubah lewat form.
        $this->put(route('admin.profil.update'), ['name' => 'A', 'email' => 'andi@isyaratkita.id', 'sandi_baru' => 'baru12345', 'sandi_baru_confirmation' => 'baru12345'])
            ->assertSessionHasErrors('sandi_sekarang');
        $this->put(route('admin.profil.update'), ['name' => 'Andi Baru', 'email' => 'andi@isyaratkita.id', 'role' => 'user',
            'sandi_sekarang' => 'admin12345', 'sandi_baru' => 'baru12345', 'sandi_baru_confirmation' => 'baru12345'])->assertRedirect();
        $this->admin->refresh();
        $this->assertTrue(password_verify('baru12345', $this->admin->password));
        $this->assertSame('admin', $this->admin->role);
        $this->assertNotNull($this->admin->sandi_diubah_pada);

        $this->biasa(); // pastikan akun andi@email.com ada, supaya emailnya benar-benar bentrok
        $this->put(route('admin.profil.update'), ['name' => 'Andi Baru', 'email' => 'andi@email.com'])->assertSessionHasErrors('email');
    }
}
