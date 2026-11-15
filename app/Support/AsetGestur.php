<?php

namespace App\Support;

use App\Models\AsetBisindo;

/**
 * Mencari gambar isyarat BISINDO untuk satu huruf atau satu kata.
 *
 * Urutan pencarian:
 *  1. Asset aktif dari menu Kelola Asset (tabel aset_bisindo) - dikelola Admin.
 *  2. Berkas bawaan di public/images/gestur/{huruf}.png|jpg|jpeg|webp|svg (Fase 1).
 *  3. null -> tampilan memakai kotak placeholder sesuai desain.
 */
class AsetGestur
{
    private const EKSTENSI = ['png', 'jpg', 'jpeg', 'webp', 'svg'];

    public static function url(string $kata): ?string
    {
        $asetDb = self::dariDatabase($kata);
        if ($asetDb) {
            return $asetDb;
        }

        // Berkas bawaan hanya untuk satu huruf.
        if (preg_match('/^[A-Za-z]$/', $kata)) {
            $nama = strtolower($kata);
            foreach (self::EKSTENSI as $ekstensi) {
                $berkas = "images/gestur/{$nama}.{$ekstensi}";
                if (file_exists(public_path($berkas))) {
                    return asset($berkas);
                }
            }
        }

        return null;
    }

    private static array $memo = [];

    private static function dariDatabase(string $kata): ?string
    {
        $kunci = mb_strtolower(trim($kata));

        if (! array_key_exists($kunci, self::$memo)) {
            $aset = AsetBisindo::aktif()
                ->whereRaw('LOWER(kata) = ?', [$kunci])
                ->latest('updated_at')
                ->first();

            self::$memo[$kunci] = $aset?->url;
        }

        return self::$memo[$kunci];
    }
}
