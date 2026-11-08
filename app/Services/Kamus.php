<?php

namespace App\Services;

use App\Data\DataDummy;
use App\Models\AsetBisindo;
use Illuminate\Support\Str;

/**
 * Daftar kata Kamus BISINDO = kata bawaan (DataDummy) + kata dari asset aktif
 * yang ditambahkan Admin lewat menu Kelola Asset (selain asset satu huruf).
 * Menonaktifkan/menghapus asset otomatis menghilangkan kata tambahan tersebut.
 */
class Kamus
{
    /** @return array<string, array<int, array{kata:string, slug:string, huruf:string}>> */
    public function daftarKata(): array
    {
        $hasil = DataDummy::daftarKata();

        $kataAset = AsetBisindo::aktif()->orderBy('kata')->get()
            ->filter(fn (AsetBisindo $a) => $a->jenis === 'kata');

        foreach ($kataAset as $aset) {
            $huruf = Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', Str::ascii($aset->kata)) ?: '', 0, 1));
            if ($huruf === '' || ! isset($hasil[$huruf])) {
                continue;
            }

            $slug = Str::slug($aset->kata);
            $sudahAda = collect($hasil[$huruf])->contains(fn ($k) => $k['slug'] === $slug);
            if (! $sudahAda) {
                $hasil[$huruf][] = ['kata' => $aset->kata, 'slug' => $slug, 'huruf' => $huruf];
            }
        }

        return $hasil;
    }

    public function cariBerdasarkanSlug(string $slug): ?array
    {
        foreach ($this->daftarKata() as $kumpulan) {
            foreach ($kumpulan as $kata) {
                if ($kata['slug'] === $slug) {
                    return $kata;
                }
            }
        }

        return null;
    }

    public function cari(string $teks): array
    {
        $teks = Str::lower(trim($teks));

        return collect($this->daftarKata())
            ->flatten(1)
            ->filter(fn ($kata) => Str::contains(Str::lower($kata['kata']), $teks))
            ->values()
            ->all();
    }
}
