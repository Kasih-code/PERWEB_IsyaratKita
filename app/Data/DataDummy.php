<?php

namespace App\Data;

use Illuminate\Support\Str;

/**
 * Pusat data dummy IsyaratKita (Fase 1 - Guest).
 *
 * Data tetap (menu, footer, nominal donasi, kata bawaan kamus).
 * Sejak Fase 3, modul, materi, quiz, dan donasi berasal dari database
 * dan dikelola lewat CMS Admin; kata kamus tambahan berasal dari Kelola Asset.
 */
class DataDummy
{
    /**
     * Menu utama pada navbar.
     * 'pola' dipakai untuk menandai menu aktif (request()->routeIs()).
     */
    public static function menuNavbar(): array
    {
        return [
            ['nama' => 'Beranda',            'rute' => 'beranda',      'pola' => 'beranda'],
            ['nama' => 'Modul Pembelajaran', 'rute' => 'modul.index',  'pola' => 'modul.*'],
            ['nama' => 'Kamus',              'rute' => 'kamus.index',  'pola' => 'kamus.*'],
            ['nama' => 'Donasi',             'rute' => 'donasi.index', 'pola' => 'donasi.*'],
        ];
    }

    /**
     * Kolom tautan pada footer.
     */
    public static function kolomFooter(): array
    {
        return [
            'Belajar' => [
                ['nama' => 'Modul Dasar',     'rute' => 'modul.index'],
                ['nama' => 'Alfabet BISINDO', 'rute' => 'kamus.index'],
                ['nama' => 'Kosa Kata',       'rute' => 'kamus.index'],
                ['nama' => 'Kamus Visual',    'rute' => 'kamus.index'],
                ['nama' => 'Latihan Quiz',    'rute' => 'modul.index'],
            ],
            'Komunitas' => [
                ['nama' => 'Program Donasi',   'rute' => 'donasi.index'],
                ['nama' => 'Relawan Pengajar', 'rute' => null],
                ['nama' => 'Kisah Setara',     'rute' => null],
                ['nama' => 'Partner Sekolah',  'rute' => null],
            ],
            'Tentang' => [
                ['nama' => 'Visi & Misi',   'rute' => null],
                ['nama' => 'Tim Kami',      'rute' => null],
                ['nama' => 'Hubungi Kami',  'rute' => null],
                ['nama' => 'F.A.Q',         'rute' => null],
            ],
        ];
    }

    /**
     * Nominal cepat pada formulir donasi (dalam rupiah).
     */
    public static function pilihanNominalDonasi(): array
    {
        return [50000, 100000, 250000, 500000];
    }

    /**
     * Daftar kata kamus, dikelompokkan per huruf awal.
     * Setiap huruf A-Z dijamin ada (boleh kosong) agar tombol abjad tidak error.
     */
    public static function daftarKata(): array
    {
        $kataPerHuruf = [
            'A' => ['Aku', 'Anak', 'Air', 'Adik', 'Apa', 'Alat', 'Ayah', 'Atas', 'Awal', 'Akan', 'Ada', 'Antar', 'Anggur', 'Acara', 'Asin'],
            'B' => ['Baca', 'Bapak', 'Baik', 'Bantu', 'Besar', 'Bisa', 'Buku', 'Buka', 'Belajar'],
            'C' => ['Cari', 'Cepat', 'Cantik', 'Coba', 'Cuci', 'Cuaca'],
            'D' => ['Dengar', 'Datang', 'Dingin', 'Dua', 'Dapur', 'Di mana'],
            'E' => ['Es', 'Empat', 'Enak', 'Emas', 'Ekor'],
            'F' => ['Foto', 'Fokus', 'Fakta', 'Februari'],
            'G' => ['Guru', 'Gembira', 'Gelas', 'Gerak', 'Gigi', 'Garam'],
            'H' => ['Halo', 'Hari', 'Hati', 'Hujan', 'Hadir', 'Hitam'],
            'I' => ['Ibu', 'Ikan', 'Ingat', 'Isyarat', 'Indonesia', 'Istirahat'],
            'J' => ['Jalan', 'Jam', 'Jangan', 'Juga', 'Jawab', 'Jumat'],
            'K' => ['Kakak', 'Kamu', 'Kabar', 'Keluarga', 'Kemarin', 'Kerja', 'Kosong'],
            'L' => ['Lagi', 'Lama', 'Lapar', 'Lihat', 'Lima', 'Lupa'],
            'M' => ['Makan', 'Minum', 'Maaf', 'Mandi', 'Main', 'Malam', 'Mau'],
            'N' => ['Nama', 'Nanti', 'Nasi', 'Nol', 'Nyaman'],
            'O' => ['Orang', 'Obat', 'Olahraga', 'Oke'],
            'P' => ['Pagi', 'Pergi', 'Pulang', 'Pintar', 'Pelan', 'Panas', 'Pasar'],
            'Q' => ['Quran', 'Quiz'],
            'R' => ['Rumah', 'Rajin', 'Ramai', 'Rasa', 'Rabu', 'Roti'],
            'S' => ['Saya', 'Sama-sama', 'Selamat Pagi', 'Sekolah', 'Senang', 'Sakit', 'Sabar', 'Satu'],
            'T' => ['Terima Kasih', 'Teman', 'Tidur', 'Tolong', 'Tiga', 'Tuli', 'Tunggu'],
            'U' => ['Ubah', 'Ulang', 'Umur', 'Untuk', 'Udara'],
            'V' => ['Video', 'Vokal', 'Variasi'],
            'W' => ['Waktu', 'Warna', 'Wajah', 'Wanita'],
            'X' => ['Xilofon'],
            'Y' => ['Yang', 'Yakin', 'Yoga'],
            'Z' => ['Zebra', 'Zaman', 'Zat'],
        ];

        $hasil = [];
        foreach ($kataPerHuruf as $huruf => $kumpulanKata) {
            $hasil[$huruf] = array_map(fn ($kata) => [
                'kata'  => $kata,
                'slug'  => Str::slug($kata),
                'huruf' => $huruf,
            ], $kumpulanKata);
        }

        return $hasil;
    }

    /**
     * Daftar semua huruf A-Z.
     */
    public static function daftarHuruf(): array
    {
        return range('A', 'Z');
    }
}
