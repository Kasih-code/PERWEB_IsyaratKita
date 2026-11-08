<?php

namespace App\Http\Controllers;

use App\Models\Modul;

class BerandaController extends Controller
{
    public function index()
    {
        return view('beranda', [
            'daftarModulPratinjau' => self::modulPratinjau(),
        ]);
    }

    /** Tiga modul publik pertama untuk pratinjau di Beranda (dari database). */
    public static function modulPratinjau(): array
    {
        return Modul::publik()->urut()->take(3)->get()
            ->map(fn (Modul $m) => ['id' => $m->id,'nomor' => $m->nomor, 'judul' => $m->judul, 'deskripsi' => (string) $m->deskripsi, 'gambar' => $m->cover_url])
            ->all();
    }
}
