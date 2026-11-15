<?php

namespace App\Http\Controllers;

use App\Data\DataDummy;
use App\Services\Kamus;
use App\Support\AsetGestur;
use Illuminate\Http\Request;
use Illuminate\Support\Str; 

class KamusController extends Controller
{
    public function __construct(private Kamus $kamus)
    {
    }

    /**
     * Daftar kata berdasarkan huruf (?huruf=B) atau hasil pencarian (?cari=aku).
     */
    public function index(Request $request)
    {
        $daftarHuruf = DataDummy::daftarHuruf();
        $kataPencarian = trim((string) $request->query('cari', ''));

        $hurufAktif = strtoupper((string) $request->query('huruf', 'A'));
        if (! in_array($hurufAktif, $daftarHuruf, true)) {
            $hurufAktif = 'A';
        }

        $hurufHasil = [];
        $gambarKataCari = null;

        if ($kataPencarian !== '') {
            $daftarKata = array_values(array_filter(
                $this->kamus->cari($kataPencarian),
                fn ($k) => mb_strtolower($k['kata']) !== mb_strtolower($kataPencarian)
            ));
            $judulHasil = "Hasil pencarian untuk '{$kataPencarian}'";
            $hurufAktif = null;

            // Eja langsung: ambil huruf A-Z saja (spasi/tanda baca diabaikan), maksimal 30 huruf.
            $hurufSaja = substr(strtoupper(preg_replace('/[^A-Za-z]/', '', Str::ascii($kataPencarian))), 0, 30);
            $hurufHasil = $hurufSaja === '' ? [] : str_split($hurufSaja);
            $gambarKataCari = AsetGestur::url($kataPencarian);
        } else {
            $daftarKata = $this->kamus->daftarKata()[$hurufAktif] ?? [];
            $judulHasil = "Hasil Pencarian untuk huruf '{$hurufAktif}'";
        }

        return view('kamus.index', compact(
            'daftarHuruf',
            'hurufAktif',
            'kataPencarian',
            'daftarKata',
            'hurufHasil',
            'gambarKataCari',
            'judulHasil'
        ));
    }

    /**
     * Detail kata: setiap huruf ditampilkan dengan gambar isyarat BISINDO.
     * Jika Admin mengunggah asset untuk kata itu sendiri, gambarnya ikut ditampilkan.
     */
    public function detail(string $slug)
    {
        $kata = $this->kamus->cariBerdasarkanSlug($slug);
        abort_if($kata === null, 404);

        // Ambil hanya huruf (abaikan spasi/tanda hubung) lalu pecah per huruf.
        $daftarHurufKata = str_split(
            strtoupper(preg_replace('/[^A-Za-z]/', '', $kata['kata']))
        );

        return view('kamus.detail', [
            'kata'            => $kata,
            'kataBesar'       => strtoupper($kata['kata']),
            'daftarHurufKata' => $daftarHurufKata,
            'gambarKata'      => AsetGestur::url($kata['kata']),
        ]);
    }
}
