<?php

namespace Database\Seeders;

use App\Models\Kuis;
use App\Models\Modul;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Data awal IsyaratKita:
     *
     *  - Admin  : dibuat dari .env (ADMIN_EMAIL, ADMIN_PASSWORD, ADMIN_NAME)
     *  - 5 modul contoh beserta materi dan quiz-nya (dapat diubah Admin lewat CMS)
     *
     * Tidak ada akun User demo; User mendaftar sendiri lewat website.
     * Tidak dijalankan pada server non-lokal.
     * Seeder tidak mengubah akun, progres, atau modul yang sudah tersimpan.
     *
     * Donasi TIDAK di-seed. Untuk data donasi contoh (bertanda simulasi) jalankan:
     *   php artisan db:seed --class=DonasiContohSeeder
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing', 'production')) {
            $this->command?->warn('Seeder dilewati: hanya untuk APP_ENV local, testing, atau production.');

            return;
        }

        $email = config('services.admin.email');
        $sandi = config('services.admin.password');

        if (! $email || ! $sandi) {
            $this->command?->error('ADMIN_EMAIL dan ADMIN_PASSWORD wajib diisi di .env sebelum db:seed.');

            return;
        }

        $admin = $this->buatAkun(
            strtolower($email),
            config('services.admin.name', 'Admin IsyaratKita'),
            $sandi,
            User::PERAN_ADMIN
        );

        $this->seedModul($admin);
    }

    private function buatAkun(string $email, string $nama, string $sandi, string $peran, ?string $whatsapp = null): User
    {
        $akun = User::firstOrNew(['email' => $email]);
        if (! $akun->exists) {
            $akun->forceFill([
                'name'     => $nama,
                'password' => $sandi,
                'role'     => $peran, // "role" tidak mass-assignable; khusus akun baru.
                'whatsapp' => $whatsapp,
            ])->save();
        }

        return $akun;
    }

    private function seedModul(User $admin): void
    {
        // Modul adalah konten CMS. Jangan menyisipkan kembali modul yang pernah
        // dihapus atau menimpa isi/urutan modul ketika seeder dijalankan ulang.
        if (Modul::query()->exists()) {
            return;
        }

        $semua = [
            1 => ['Pengenalan Abjad A-Z', 'Langkah pertama mengenal isyarat visual dengan mengeja abjad satu tangan.', [
                ['heading', 'Pengertian Bisindo'],
                ['teks', 'Bahasa Isyarat Indonesia (BISINDO) adalah bahasa isyarat yang berlaku di Indonesia. Berbeda dari SIBI yang merupakan sistem buatan dan bukan merupakan bahasa, BISINDO merupakan bahasa ibu yang tumbuh secara alami pada kalangan komunitas Tuli di Indonesia. Perbedaan lainnya adalah SIBI menggunakan isyarat khusus untuk morfem imbuhan mengikuti bahasa Indonesia, sehingga kata-katanya jauh lebih panjang daripada kata-kata dalam bahasa isyarat alami seperti BISINDO. BISINDO kemudian diteliti dan dikembangkan oleh Pusat Bahasa Isyarat Indonesia (PUSBISINDO) serta Laboratorium Riset Bahasa Isyarat FIB UI.'],
                ['heading', 'Ciri-ciri BISINDO'],
                ['teks', 'BISINDO bersifat komunikatif dan ekspresif. Bahasa isyarat alami ini menggunakan dua tangan secara aktif serta ekspresi wajah yang natural untuk menyampaikan makna.'],
                ['heading', 'Mengeja dengan Abjad'],
                ['teks', 'Setiap huruf A sampai Z memiliki bentuk tangannya sendiri. Latih tiap huruf dengan tempo yang konsisten dan pastikan telapak tangan menghadap ke lawan bicara. Buka menu Kamus untuk melihat gambar isyarat tiap huruf.'],
            ]],
            2 => ['Kosa Kata & Sapaan Dasar', "Pelajari bagaimana menyapa 'Halo', 'Pagi', dan menanyakan kabar.", [
                ['heading', 'Sapaan Dasar dalam BISINDO'],
                ['teks', 'Sapaan adalah bagian penting saat memulai percakapan. Perhatikan posisi tangan, arah gerak, dan ekspresi wajah saat mempraktikkan setiap isyarat.'],
                ['heading', 'Mengeja Kata'],
                ['teks', 'Kata baru yang belum punya isyarat khusus dapat dieja huruf demi huruf, misalnya HALO dieja H - A - L - O.'],
            ]],
            3 => ['Angka & Waktu', 'Mengekspresikan nominal angka, hari, bulan, dan waktu secara natural.', [
                ['heading', 'Angka dan Hitungan'],
                ['teks', 'Angka diisyaratkan dengan bentuk jari yang berbeda untuk tiap bilangan. Mulailah dari satu sampai sepuluh, lalu lanjutkan ke bilangan yang lebih besar.'],
                ['heading', 'Hari, Bulan, dan Waktu'],
                ['teks', 'Nama hari dan bulan dapat dieja atau memakai isyarat khusus. Ekspresi wajah membantu menyampaikan keterangan waktu seperti kemarin, hari ini, dan besok.'],
            ]],
            4 => ['Hubungan Keluarga', 'Mengenal isyarat penting seperti Ayah, Ibu, Kakak, Adik, dan Teman.', [
                ['heading', 'Anggota Keluarga'],
                ['teks', 'Isyarat untuk anggota keluarga seperti Ayah, Ibu, Kakak, dan Adik sering dipakai dalam perkenalan. Pelajari gerakan tiap isyarat dan latih bersama teman belajarmu.'],
                ['heading', 'Teman dan Orang Terdekat'],
                ['teks', 'Selain keluarga, kamu juga perlu mengenal isyarat untuk Teman dan orang-orang di sekitarmu agar percakapan terasa lebih akrab.'],
            ]],
            5 => ['Percakapan Sederhana', 'Latihan merajut kalimat penuh untuk memulai obrolan dengan Teman Tuli.', [
                ['heading', 'Memulai Percakapan'],
                ['teks', 'Mulailah dengan sapaan yang ramah, jaga kontak mata, dan beri ruang bagi lawan bicara untuk menjawab. Jika belum paham, mintalah lawan bicara mengulang dengan sopan.'],
                ['heading', 'Menyusun Kalimat Sederhana'],
                ['teks', 'Susun kalimat pendek seperti "Nama saya Andi". Ekspresi wajah membawa bagian dari makna kalimat, jadi jangan diabaikan.'],
            ]],
        ];

        $soalAwal = KuisAwal::semuaSoal();

        foreach ($semua as $nomor => [$judul, $deskripsi, $blok]) {
            $modul = Modul::find($nomor) ?? new Modul();
            if (! $modul->exists) {
                $modul->forceFill(['id' => $nomor]);
            }
            $modul->fill([
                'nomor' => $nomor, 'judul' => $judul, 'deskripsi' => $deskripsi,
                'status' => 'terbit', 'tampil_publik' => true, 'dibuat_oleh' => $admin->id,
            ])->save();

            // Isi materi & soal hanya jika modul masih kosong (agar perubahan Admin tidak tertimpa).
            if ($modul->blok()->doesntExist()) {
                foreach ($blok as $i => [$tipe, $teks]) {
                    $modul->blok()->create(['urutan' => $i + 1, 'tipe' => $tipe, 'teks' => $teks]);
                }
            }

            /** @var Kuis $kuis */
            $kuis = $modul->kuis()->firstOrCreate([]);
            if ($kuis->pertanyaan()->doesntExist()) {
                foreach ($soalAwal[$nomor] ?? [] as $i => $butir) {
                    $p = $kuis->pertanyaan()->create([
                        'urutan'       => $i + 1,
                        'teks'         => $butir['teks'],
                        'huruf_aset'   => $butir['gambar_huruf'] ?? null,
                        'tipe_jawaban' => 'teks',
                        'poin'         => 10,
                        'kesulitan'    => 'dasar',
                        'acak_pilihan' => false,
                    ]);
                    foreach ($butir['pilihan'] as $j => $teksPilihan) {
                        $p->pilihan()->create(['urutan' => $j + 1, 'teks' => $teksPilihan, 'benar' => $j === $butir['benar']]);
                    }
                }
            }
        }
    }
}
