<?php

/**
 * Pintu masuk untuk hosting di Vercel (runtime vercel-php).
 *
 * Di Vercel, folder project bersifat read-only; hanya /tmp yang dapat
 * ditulis. Karena itu cache view disimpan di /tmp (lihat vercel.json).
 * Di server biasa / lokal berkas ini tidak dipakai (gunakan public/index.php).
 */

$folderSementara = ['/tmp/views'];

foreach ($folderSementara as $folder) {
    if (! is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
}

require __DIR__.'/../public/index.php';
