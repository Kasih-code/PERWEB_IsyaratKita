<?php

namespace Database\Seeders;

/**
 * Soal quiz awal (bahan seeder). Sejak Fase 3 soal disimpan di database dan dikelola
 * Admin lewat CMS; berkas ini hanya dipakai DatabaseSeeder untuk mengisi data contoh.
 *
 * Format satu soal:
 *   'teks'         => pertanyaan
 *   'gambar_huruf' => (opsional) huruf/kata asset yang ditampilkan sebagai gambar isyarat
 *   'pilihan'      => daftar pilihan jawaban
 *   'benar'        => indeks (0-based) pilihan yang benar
 */
class KuisAwal
{
    private static function soalHuruf(string $huruf, array $pilihan, int $benar): array
    {
        return [
            'teks'         => 'Apa abjad BISINDO yang diisyaratkan pada gambar di bawah ini?',
            'gambar_huruf' => $huruf,
            'pilihan'      => $pilihan,
            'benar'        => $benar,
        ];
    }

    public static function semuaSoal(): array
    {
        return [
            // ---------- Modul 1: Mengenal BISINDO Lebih Jauh (10 soal) ----------
            1 => [
                [
                    'teks'    => 'BISINDO adalah singkatan dari ...',
                    'pilihan' => ['Bahasa Isyarat Indonesia', 'Bahasa Isyarat Internasional', 'Bahasa Indonesia Dasar', 'Bahasa Isyarat Dunia'],
                    'benar'   => 0,
                ],
                [
                    'teks'    => 'BISINDO tumbuh secara alami pada kalangan ...',
                    'pilihan' => ['Komunitas Tuli di Indonesia', 'Pelajar sekolah dasar', 'Penerjemah resmi pemerintah', 'Pekerja pariwisata'],
                    'benar'   => 0,
                ],
                [
                    'teks'    => 'Apa kepanjangan dari SIBI?',
                    'pilihan' => ['Sistem Isyarat Bahasa Indonesia', 'Sandi Isyarat Bahasa Indonesia', 'Sistem Informasi Bahasa Isyarat', 'Standar Isyarat Bahasa Internasional'],
                    'benar'   => 0,
                ],
                [
                    'teks'    => 'Menurut materi, SIBI berbeda dari BISINDO karena SIBI merupakan ...',
                    'pilihan' => ['Bahasa ibu komunitas Tuli', 'Sistem buatan dan bukan merupakan bahasa', 'Bahasa isyarat alami', 'Bahasa lisan'],
                    'benar'   => 1,
                ],
                [
                    'teks'    => 'Lembaga yang meneliti dan mengembangkan BISINDO menurut materi adalah ...',
                    'pilihan' => ['Kementerian Perhubungan', 'Perpustakaan Nasional', 'PUSBISINDO dan Laboratorium Riset Bahasa Isyarat FIB UI', 'Badan Pusat Statistik'],
                    'benar'   => 2,
                ],
                [
                    'teks'    => 'Mengapa kata-kata pada SIBI lebih panjang daripada BISINDO?',
                    'pilihan' => ['Karena SIBI hanya memakai satu tangan', 'Karena SIBI tidak memakai ekspresi wajah', 'Karena SIBI memakai gambar', 'Karena SIBI memakai isyarat khusus untuk imbuhan mengikuti bahasa Indonesia'],
                    'benar'   => 3,
                ],
                [
                    'teks'    => 'BISINDO disebut bahasa ibu bagi komunitas Tuli karena ...',
                    'pilihan' => ['Dibuat oleh pemerintah dalam satu hari', 'Diciptakan khusus untuk pelajaran sekolah', 'Tumbuh secara alami dalam komunitasnya', 'Diambil dari bahasa asing'],
                    'benar'   => 2,
                ],
                self::soalHuruf('A', ['Huruf B', 'Huruf A', 'Huruf C', 'Huruf D'], 1),
                self::soalHuruf('M', ['Huruf N', 'Huruf W', 'Huruf M', 'Huruf K'], 2),
                self::soalHuruf('A', ['Huruf A', 'Huruf B', 'Huruf C', 'Huruf D'], 0),
            ],

            // ---------- Modul 2: Kosa Kata & Sapaan Dasar (5 soal) ----------
            2 => [
                [
                    'teks'    => "Kata 'HALO' dieja dengan urutan huruf ...",
                    'pilihan' => ['H - L - A - O', 'A - H - L - O', 'H - A - L - O', 'H - A - O - L'],
                    'benar'   => 2,
                ],
                [
                    'teks'    => "Berapa jumlah huruf pada kata 'SELAMAT'?",
                    'pilihan' => ['5', '6', '8', '7'],
                    'benar'   => 3,
                ],
                [
                    'teks'    => "Kata 'PAGI' dimulai dengan huruf ...",
                    'pilihan' => ['P', 'G', 'A', 'I'],
                    'benar'   => 0,
                ],
                [
                    'teks'    => 'Selain gerakan tangan, apa lagi yang penting untuk menyampaikan makna dalam BISINDO?',
                    'pilihan' => ['Suara yang keras', 'Ekspresi wajah', 'Tulisan di kertas', 'Bunyi tepukan'],
                    'benar'   => 1,
                ],
                [
                    'teks'    => "Berapa jumlah kata pada ungkapan 'TERIMA KASIH'?",
                    'pilihan' => ['Dua kata', 'Satu kata', 'Tiga kata', 'Empat kata'],
                    'benar'   => 0,
                ],
            ],

            // ---------- Modul 3: Angka & Waktu (5 soal) ----------
            3 => [
                [
                    'teks'    => 'Urutan angka yang benar adalah ...',
                    'pilihan' => ['Satu, Tiga, Dua', 'Dua, Satu, Tiga', 'Tiga, Dua, Satu', 'Satu, Dua, Tiga'],
                    'benar'   => 3,
                ],
                [
                    'teks'    => 'Hari setelah Senin adalah ...',
                    'pilihan' => ['Selasa', 'Rabu', 'Minggu', 'Jumat'],
                    'benar'   => 0,
                ],
                [
                    'teks'    => 'Berapa jumlah bulan dalam satu tahun?',
                    'pilihan' => ['Sepuluh', 'Dua belas', 'Sebelas', 'Tiga belas'],
                    'benar'   => 1,
                ],
                [
                    'teks'    => "Kata 'ENAM' terdiri dari berapa huruf?",
                    'pilihan' => ['3', '5', '4', '6'],
                    'benar'   => 2,
                ],
                [
                    'teks'    => 'Berapa jam dalam satu hari?',
                    'pilihan' => ['12 jam', '24 jam', '48 jam', '60 jam'],
                    'benar'   => 1,
                ],
            ],

            // ---------- Modul 4: Hubungan Keluarga (5 soal) ----------
            4 => [
                [
                    'teks'    => 'Orang tua laki-laki biasa disebut ...',
                    'pilihan' => ['Ayah', 'Ibu', 'Adik', 'Kakak'],
                    'benar'   => 0,
                ],
                [
                    'teks'    => 'Saudara yang usianya lebih muda disebut ...',
                    'pilihan' => ['Kakak', 'Paman', 'Adik', 'Nenek'],
                    'benar'   => 2,
                ],
                [
                    'teks'    => "Kata 'IBU' dieja dengan huruf ...",
                    'pilihan' => ['I - B - U', 'B - I - U', 'U - B - I', 'I - U - B'],
                    'benar'   => 0,
                ],
                [
                    'teks'    => "Kata 'KAKAK' dimulai dengan huruf ...",
                    'pilihan' => ['A', 'K', 'N', 'M'],
                    'benar'   => 1,
                ],
                [
                    'teks'    => 'Sebutan untuk orang yang berhubungan akrab dengan kita di luar keluarga adalah ...',
                    'pilihan' => ['Teman', 'Guru', 'Tetangga jauh', 'Orang asing'],
                    'benar'   => 0,
                ],
            ],

            // ---------- Modul 5: Percakapan Sederhana (5 soal) ----------
            5 => [
                [
                    'teks'    => 'Jika belum memahami isyarat lawan bicara, sebaiknya kita ...',
                    'pilihan' => ['Pergi tanpa berkata apa-apa', 'Berpura-pura paham', 'Meminta lawan bicara mengulang dengan sopan', 'Menutup mata'],
                    'benar'   => 2,
                ],
                [
                    'teks'    => 'Saat berbicara dengan Teman Tuli, sikap yang baik adalah ...',
                    'pilihan' => ['Menjaga kontak mata', 'Membelakangi lawan bicara', 'Sibuk memandang ponsel', 'Berbicara sambil menutup wajah'],
                    'benar'   => 0,
                ],
                [
                    'teks'    => "Pada kalimat 'Nama saya Andi', kata pertamanya adalah ...",
                    'pilihan' => ['Andi', 'Saya', 'Nama', 'Kalimat'],
                    'benar'   => 2,
                ],
                [
                    'teks'    => 'Mengapa ekspresi wajah penting dalam percakapan bahasa isyarat?',
                    'pilihan' => ['Tidak penting sama sekali', 'Hanya untuk berfoto', 'Agar lawan bicara mengantuk', 'Karena membawa bagian dari makna kalimat'],
                    'benar'   => 3,
                ],
                [
                    'teks'    => 'Untuk memulai obrolan dengan sopan, kita dapat memulainya dengan ...',
                    'pilihan' => ['Sapaan ramah', 'Memotong pembicaraan', 'Pergi menjauh', 'Marah-marah'],
                    'benar'   => 0,
                ],
            ],
        ];
    }
}
