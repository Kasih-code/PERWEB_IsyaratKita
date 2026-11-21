<?php

namespace App\Support;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Penyimpanan berkas unggahan Admin.
 * - UNGGAHAN_DRIVER=local      : disk "unggahan" (public/uploads), untuk laptop dan tes.
 * - UNGGAHAN_DRIVER=cloudinary : Cloudinary (server). Yang disimpan di database adalah URL https.
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
        if (! self::cloudinaryAktif()) {
            return $berkas->store($folder, self::DISK);
        }

        return self::kirimKeCloudinary($berkas->getRealPath(), $folder);
    }

    public static function hapus(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (self::adalahUrl($path)) {
            $publicId = self::publicId($path);

            if (self::cloudinaryAktif() && $publicId) {
                try {
                    self::siapkanCloudinary();
                    (new UploadApi())->destroy($publicId, ['resource_type' => 'image', 'invalidate' => true]);
                } catch (\Throwable $e) {
                    Log::warning('Gagal menghapus berkas di Cloudinary: '.$e->getMessage());
                }
            }

            return;
        }

        Storage::disk(self::DISK)->delete($path);
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return self::adalahUrl($path) ? $path : Storage::disk(self::DISK)->url($path);
    }

    /** Menyalin berkas (dipakai saat modul diduplikasi). Mengembalikan jalur/URL baru, atau null. */
    public static function salin(string $path, string $folder): ?string
    {
        if (self::adalahUrl($path)) {
            return self::cloudinaryAktif() ? self::kirimKeCloudinary($path, $folder) : null;
        }

        $disk = Storage::disk(self::DISK);
        if (! $disk->exists($path)) {
            return null;
        }

        $baru = $folder.'/'.bin2hex(random_bytes(16)).'.'.pathinfo($path, PATHINFO_EXTENSION);
        $disk->copy($path, $baru);

        return $baru;
    }

    private static function cloudinaryAktif(): bool
    {
        return config('filesystems.unggahan_driver') === 'cloudinary';
    }

    private static function siapkanCloudinary(): void
    {
        Configuration::instance(config('filesystems.cloudinary_url'));
    }

    /** $sumber: jalur berkas lokal atau URL publik. */
    private static function kirimKeCloudinary(string $sumber, string $folder): string
    {
        self::siapkanCloudinary();

        $hasil = (new UploadApi())->upload($sumber, [
            'asset_folder'     => 'isyaratkita/'.$folder,
            'public_id_prefix' => 'isyaratkita/'.$folder,
            'resource_type'   => 'image',
            'unique_filename' => true,
            'overwrite'       => false,
        ]);

        return $hasil['secure_url'];
    }

    private static function adalahUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    /** Mengambil public_id dari URL Cloudinary: .../upload/v123/folder/nama.ext -> folder/nama */
    private static function publicId(string $url): ?string
    {
        return preg_match('#/upload/(?:v\d+/)?(.+)\.[A-Za-z0-9]+$#', $url, $cocok) ? $cocok[1] : null;
    }
}