<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Penyimpanan berkas unggahan Admin pada disk "unggahan" (folder public/uploads).
 * Nama berkas dibuat acak oleh Laravel (hashName) sehingga tidak bisa ditebak
 * dan nama asli dari pengguna tidak pernah dipakai sebagai jalur berkas.
 */
class Unggahan
{
    public const DISK = 'unggahan';

    /** Aturan validasi gambar: JPG/PNG/WebP, maksimal 5 MB (SVG sengaja tidak diizinkan). */
    public static function aturanGambar(int $maksKb = 5120): array
    {
        return ['image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:'.$maksKb];
    }

    public static function simpan(UploadedFile $berkas, string $folder): string
    {
        return $berkas->store($folder, self::DISK);
    }

    public static function hapus(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        return $path ? Storage::disk(self::DISK)->url($path) : null;
    }
}
