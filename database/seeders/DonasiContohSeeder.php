<?php

namespace Database\Seeders;

use App\Models\Donasi;
use Illuminate\Database\Seeder;

/**
 * Data donasi CONTOH untuk mencoba halaman Laporan Donasi.
 * Semua baris bertanda simulasi = true (bukan transaksi nyata, tidak ada uang yang dipindahkan).
 * Tidak dijalankan otomatis: php artisan db:seed --class=DonasiContohSeeder
 */
class DonasiContohSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('Donasi contoh dilewati: hanya untuk APP_ENV=local/testing.');

            return;
        }

        $contoh = [
            ['Donatur Contoh Satu', 'donatur1@contoh.test', '081200000001', 500000],
            ['Donatur Contoh Dua', 'donatur2@contoh.test', '081200000002', 250000],
            ['Sekolah Contoh', 'sekolah@contoh.test', '021000000003', 100000],
            ['Donatur Contoh Tiga', 'donatur3@contoh.test', '081200000004', 100000],
            ['Donatur Contoh Empat', 'donatur4@contoh.test', '081200000005', 50000],
            ['Donatur Contoh Lima', 'donatur5@contoh.test', '081200000006', 250000],
        ];

        foreach ($contoh as $i => [$nama, $email, $wa, $nominal]) {
            // Hindari duplikasi jika seeder contoh dipanggil berulang kali.
            if (Donasi::where('email', $email)->where('simulasi', true)->exists()) {
                continue;
            }

            $waktu = now()->subDays($i * 3)->setTime(9 + $i, 15);
            $donasi = Donasi::create([
                'kode'         => Donasi::buatKode(),
                'nama'         => $nama,
                'email'        => $email,
                'whatsapp'     => $wa,
                'nominal'      => $nominal,
                'metode'       => 'QRIS',
                'status'       => 'berhasil',
                'simulasi'     => true,
                'dibayar_pada' => $waktu,
            ]);
            $donasi->update(['referensi' => 'INV/IK/'.$donasi->id.'/QRIS']);
        }
    }
}
