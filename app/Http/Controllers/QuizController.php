<?php

namespace App\Http\Controllers;

use App\Models\Modul;
use App\Models\PercobaanKuis;
use App\Services\ProgresBelajar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Quiz modul (khusus User, dilindungi middleware auth pada routes/web.php).
 * Alur: Modul -> Quiz -> kirim jawaban -> hasil quiz.
 * Soal, pilihan, nilai lulus, urutan, dan durasi berasal dari database (diatur Admin).
 */
class QuizController extends Controller
{
    public function __construct(private ProgresBelajar $progres)
    {
    }

    public function tampil(Request $request, int $id)
    {
        [$modul, $kuis, $tolak] = $this->siapkan($request, $id);
        if ($tolak) {
            return $tolak;
        }

        $pertanyaan = $kuis->pertanyaan()->with('pilihan')->get();
        if ($kuis->urutan_soal === 'acak') {
            $pertanyaan = $pertanyaan->shuffle();
        }

        $soal = $pertanyaan->values()->map(function ($p) {
            $pilihan = $p->acak_pilihan ? $p->pilihan->shuffle() : $p->pilihan;

            return [
                'id'      => $p->id,
                'teks'    => $p->teks,
                'poin'    => $p->poin,
                'gambar'  => $p->gambar_url,
                'bergambar' => $p->gambar !== null || $p->huruf_aset !== null,
                'benar'   => optional($p->pilihan->firstWhere('benar', true))->id,
                'pilihan' => $pilihan->values()->map(fn ($o) => [
                    'id' => $o->id, 'teks' => $o->teks, 'gambar' => $o->gambar_url,
                ])->all(),
            ];
        })->all();

        return view('quiz.index', [
            'modul'     => $modul,
            'kuis'      => $kuis,
            'soal'      => $soal,
            'totalSoal' => count($soal),
            'totalPoin' => max(1, $pertanyaan->sum('poin')),
            'detikBatas' => $kuis->durasi_menit ? $kuis->durasi_menit * 60 : null,
        ]);
    }

    public function kirim(Request $request, int $id): RedirectResponse
    {
        [$modul, $kuis, $tolak] = $this->siapkan($request, $id);
        if ($tolak) {
            return $tolak;
        }

        $pertanyaan = $kuis->pertanyaan()->with('pilihan')->get();
        $jawaban = (array) $request->input('jawaban', []);
        $waktuHabis = $request->boolean('waktu_habis');

        $benar = 0;
        $poinBenar = 0;
        $catatan = [];

        foreach ($pertanyaan as $p) {
            $dipilih = $jawaban[$p->id] ?? null;
            $pilihan = is_numeric($dipilih) ? $p->pilihan->firstWhere('id', (int) $dipilih) : null;

            if (! $pilihan) {
                // Semua soal wajib dijawab, kecuali waktu quiz sudah habis.
                if (! $waktuHabis || $dipilih !== null) {
                    return back()->with('galat', 'Jawab semua pertanyaan sebelum mengirim quiz.');
                }
                continue;
            }

            $catatan[$p->id] = $pilihan->id;
            if ($pilihan->benar) {
                $benar++;
                $poinBenar += $p->poin;
            }
        }

        $totalPoin = (int) $pertanyaan->sum('poin');
        $nilai = $totalPoin > 0 ? (int) round($poinBenar / $totalPoin * 100) : 0;
        $lulus = $pertanyaan->isNotEmpty() && $nilai >= $kuis->nilai_lulus;

        PercobaanKuis::create([
            'user_id'      => $request->user()->id,
            'kuis_id'      => $kuis->id,
            'jumlah_benar' => $benar,
            'total_soal'   => $pertanyaan->count(),
            'nilai'        => $nilai,
            'lulus'        => $lulus,
            'jawaban'      => $catatan,
        ]);

        $this->progres->catatHasilQuiz($request->user(), $modul, $benar, $pertanyaan->count(), $lulus);

        return redirect()->route('modul.quiz.hasil', $modul->id);
    }

    public function hasil(Request $request, int $id)
    {
        [$modul, $kuis, $tolak] = $this->siapkan($request, $id);
        if ($tolak) {
            return $tolak;
        }

        $user = $request->user();
        $percobaan = PercobaanKuis::where('user_id', $user->id)->where('kuis_id', $kuis->id)->latest('id')->first();

        // Belum pernah mengerjakan quiz: arahkan ke halaman quiz.
        if (! $percobaan) {
            return redirect()->route('modul.quiz.tampil', $modul->id);
        }

        // Pembahasan: soal yang dijawab salah (atau tidak sempat dijawab), beserta penjelasannya.
        $jawabanUser = $percobaan->jawaban ?? [];
        $pembahasan = [];
        foreach ($kuis->pertanyaan()->with('pilihan')->get() as $indeks => $p) {
            $dipilih = $p->pilihan->firstWhere('id', $jawabanUser[$p->id] ?? 0);
            $benar = $p->pilihan->firstWhere('benar', true);
            if ($dipilih && $dipilih->benar) {
                continue;
            }
            $pembahasan[] = [
                'nomor'     => $indeks + 1,
                'teks'      => $p->teks,
                'jawabanmu' => $dipilih?->ringkas ?? 'Tidak dijawab',
                'benar'     => $benar?->ringkas,
                'penjelasan' => $p->pembahasan,
            ];
        }

        $berikutnya = $this->progres->modulBerikutnya($modul);

        return view('quiz.hasil', [
            'modul'         => $modul,
            'benar'         => $percobaan->jumlah_benar,
            'total'         => $percobaan->total_soal,
            'nilai'         => $percobaan->nilai,
            'lulus'         => $percobaan->lulus,
            'labelNilai'    => $this->labelNilai($percobaan->nilai, $percobaan->lulus),
            'batasLulus'    => $kuis->nilai_lulus,
            'persenProgres' => $this->progres->persen($user),
            'berikutnya'    => $berikutnya,
            'pembahasan'    => $pembahasan,
        ]);
    }

    private function labelNilai(int $nilai, bool $lulus): string
    {
        return match (true) {
            $nilai >= 100 => 'Sempurna!',
            $lulus && $nilai >= 80 => 'Sangat Bagus!',
            $lulus => 'Bagus!',
            default => 'Ayo Coba Lagi',
        };
    }

    /**
     * Memuat modul + kuis dan memeriksa akses.
     * Mengembalikan [modul, kuis, responsPenolakan|null].
     */
    private function siapkan(Request $request, int $id): array
    {
        $modul = Modul::with('kuis')->findOrFail($id);
        abort_unless($modul->dapatDilihatPublik(), 404);

        if (! $this->progres->modulTerbuka($request->user(), $modul)) {
            $sebelumnya = $this->progres->modulSebelumnya($modul);

            return [$modul, null, redirect()
                ->route('modul.index')
                ->with('galat', 'Modul ini masih terkunci. Selesaikan '.($sebelumnya?->label ?? 'modul sebelumnya').' terlebih dahulu.')];
        }

        $kuis = $modul->kuis;
        if (! $kuis || ! $kuis->bisaDikerjakan()) {
            return [$modul, $kuis, redirect()
                ->route('modul.detail', $modul->id)
                ->with('galat', 'Quiz untuk modul ini belum tersedia.')];
        }

        return [$modul, $kuis, null];
    }
}
