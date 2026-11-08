<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donasi;
use App\Models\Modul;
use App\Models\User;

/** Dashboard: ringkasan nyata dari database (tanpa angka karangan). */
class DashboardController extends Controller
{
    public function __invoke()
    {
        $totalDonasi = (int) Donasi::berhasil()->sum('nominal');

        return view('admin.dashboard', [
            'totalPengguna'  => User::where('role', User::PERAN_USER)->count(),
            'totalModul'     => Modul::count(),
            'modulTerbit'    => Modul::publik()->count(),
            'totalDonasi'    => $totalDonasi,
            'totalDonasiTeks' => self::rupiahSingkat($totalDonasi),
            'adaSimulasi'    => Donasi::berhasil()->where('simulasi', true)->exists(),
            'donasiTerbaru'  => Donasi::berhasil()->latest('dibayar_pada')->latest('id')->take(5)->get(),
        ]);
    }

    /** 84650000 -> "Rp 84,6 jt"; di bawah sejuta ditulis lengkap: "Rp 750.000". */
    public static function rupiahSingkat(int $angka): string
    {
        if ($angka >= 1_000_000_000) {
            return 'Rp '.number_format(floor($angka / 100_000_000) / 10, 1, ',', '.').' M';
        }
        if ($angka >= 1_000_000) {
            return 'Rp '.number_format(floor($angka / 100_000) / 10, 1, ',', '.').' jt';
        }

        return Donasi::rupiah($angka);
    }
}
