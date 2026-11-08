<?php

namespace App\Services;

use App\Models\Modul;
use App\Models\ProgresModul;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Logika progres belajar User (data modul berasal dari database).
 *
 * Aturan:
 *  - Hanya modul terbit + tampil publik yang ikut dihitung, berurutan menurut "nomor".
 *  - Modul publik pertama selalu terbuka; modul berikutnya terbuka jika modul publik
 *    sebelumnya sudah selesai.
 *  - Modul selesai jika quiz dikerjakan dengan nilai >= nilai lulus modul itu
 *    (atau, untuk modul tanpa quiz aktif, ketika User menekan "Selesaikan Modul").
 *  - Status tiap modul: selesai | berjalan | terkunci (berjalan = modul pertama yang belum selesai).
 */
class ProgresBelajar
{
    /** @return Collection<int, Modul> */
    public function modulPublik(): Collection
    {
        return Modul::publik()->urut()->get();
    }

    /**
     * Daftar modul lengkap dengan status & teks tombol untuk User.
     */
    public function daftarModul(User $user): array
    {
        $progres = $user->progresModul()->get()->keyBy('modul_id');
        $modulBerjalanSudahAda = false;
        $hasil = [];

        foreach ($this->modulPublik() as $modul) {
            $baris = $progres->get($modul->id);

            if ($baris && $baris->selesai()) {
                $status = 'selesai';
                $tombol = 'Ulangi Materi';
            } elseif (! $modulBerjalanSudahAda) {
                $status = 'berjalan';
                $tombol = $baris ? 'Lanjutkan' : 'Mulai Belajar';
                $modulBerjalanSudahAda = true;
            } else {
                $status = 'terkunci';
                $tombol = 'Buka Modul';
            }

            $hasil[] = $this->barisModul($modul, $status, $tombol);
        }

        return $hasil;
    }

    /** Daftar modul untuk Guest: semua terkunci. */
    public function daftarModulGuest(): array
    {
        return $this->modulPublik()
            ->map(fn (Modul $modul) => $this->barisModul($modul, 'terkunci', 'Buka Modul'))
            ->all();
    }

    private function barisModul(Modul $modul, string $status, string $tombol): array
    {
        return [
            'id'        => $modul->id,
            'nomor'     => $modul->nomor,
            'judul'     => $modul->judul_lengkap,
            'deskripsi' => (string) $modul->deskripsi,
            'status'    => $status,
            'tombol'    => $tombol,
        ];
    }

    public function jumlahSelesai(User $user): int
    {
        return $user->progresModul()
            ->where('status', 'selesai')
            ->whereIn('modul_id', Modul::publik()->pluck('id'))
            ->count();
    }

    public function totalModul(): int
    {
        return Modul::publik()->count();
    }

    public function persen(User $user): int
    {
        $total = $this->totalModul();

        return $total > 0 ? (int) round($this->jumlahSelesai($user) / $total * 100) : 0;
    }

    /** Modul publik sebelum $modul (urutan nomor), atau null jika $modul yang pertama. */
    public function modulSebelumnya(Modul $modul): ?Modul
    {
        return $this->modulPublik()->filter(fn (Modul $m) => $this->lebihAwal($m, $modul))->last();
    }

    public function modulBerikutnya(Modul $modul): ?Modul
    {
        return $this->modulPublik()->first(fn (Modul $m) => $this->lebihAwal($modul, $m));
    }

    private function lebihAwal(Modul $a, Modul $b): bool
    {
        return $a->nomor < $b->nomor || ($a->nomor === $b->nomor && $a->id < $b->id);
    }

    /**
     * Apakah modul boleh dibuka oleh User ini?
     */
    public function modulTerbuka(User $user, Modul $modul): bool
    {
        if (! $modul->dapatDilihatPublik()) {
            return false;
        }

        // Modul yang sudah pernah diselesaikan tetap boleh dibuka, walau urutan berubah.
        $sudahSelesai = $user->progresModul()
            ->where('modul_id', $modul->id)
            ->where('status', 'selesai')
            ->exists();
        if ($sudahSelesai) {
            return true;
        }

        $sebelumnya = $this->modulSebelumnya($modul);
        if (! $sebelumnya) {
            return true;
        }

        return $user->progresModul()
            ->where('modul_id', $sebelumnya->id)
            ->where('status', 'selesai')
            ->exists();
    }

    /**
     * Tandai modul mulai dipelajari (dibuat sekali, tidak menurunkan status selesai).
     */
    public function mulai(User $user, Modul $modul): ProgresModul
    {
        return ProgresModul::firstOrCreate(
            ['user_id' => $user->id, 'modul_id' => $modul->id],
            ['status' => 'berjalan']
        );
    }

    /**
     * Simpan hasil quiz. Modul menjadi selesai jika $lulus.
     * Modul yang sudah selesai tidak pernah kembali ke "berjalan".
     */
    public function catatHasilQuiz(User $user, Modul $modul, int $benar, int $total, bool $lulus): ProgresModul
    {
        $progres = $this->mulai($user, $modul);

        $progres->skor_terakhir = $benar;
        $progres->total_soal_terakhir = $total;
        $progres->skor_terbaik = max((int) $progres->skor_terbaik, $benar);
        $progres->jumlah_percobaan = $progres->jumlah_percobaan + 1;

        if (! $progres->selesai() && $lulus) {
            $progres->status = 'selesai';
            $progres->selesai_pada = now();
        }

        $progres->save();

        return $progres;
    }

    /** Untuk modul tanpa quiz aktif: selesai setelah materi dibaca sampai akhir. */
    public function tandaiSelesai(User $user, Modul $modul): ProgresModul
    {
        $progres = $this->mulai($user, $modul);

        if (! $progres->selesai()) {
            $progres->status = 'selesai';
            $progres->selesai_pada = now();
            $progres->save();
        }

        return $progres;
    }
}
