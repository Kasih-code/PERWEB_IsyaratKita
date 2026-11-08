<?php

namespace App\Http\Controllers;

use App\Models\Modul;
use App\Services\ProgresBelajar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModulController extends Controller
{
    public function __construct(private ProgresBelajar $progres)
    {
    }

    /**
     * Daftar modul (dari database, hanya yang terbit dan tampil publik).
     * - Guest : semua modul terkunci + modal login terbuka otomatis.
     * - User  : status modul dan progres dihitung dari progres belajar User.
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return view('modul.index', [
                'daftarModul'         => $this->progres->daftarModulGuest(),
                'progresBelajar'      => ['persen' => 0],
                'tampilkanModalLogin' => true,
            ]);
        }

        return view('modul.index', [
            'daftarModul'         => $this->progres->daftarModul($user),
            'progresBelajar'      => [
                'persen'  => $this->progres->persen($user),
                'selesai' => $this->progres->jumlahSelesai($user),
                'total'   => $this->progres->totalModul(),
            ],
            'tampilkanModalLogin' => false,
        ]);
    }

    /**
     * Detail modul: materi dibagi per "Materi" (setiap blok heading memulai materi baru).
     * - Guest : pratinjau materi + modal login.
     * - User  : hanya modul yang sudah terbuka; modul terkunci dialihkan ke daftar modul.
     * - Admin : boleh membuka modul draft sebagai pratinjau.
     */
    public function detail(Request $request, int $id)
    {
        $modul = Modul::with(['blok', 'kuis'])->findOrFail($id);
        $user = Auth::user();

        $adalahAdmin = $user && $user->adalahAdmin();
        // Admin selalu melihat modul sebagai pratinjau (tidak mencatat progres, tidak terkena kunci modul).
        $pratinjau = $adalahAdmin || ! $modul->dapatDilihatPublik();
        abort_if($pratinjau && ! $adalahAdmin, 404);

        if ($user && ! $pratinjau && ! $this->progres->modulTerbuka($user, $modul)) {
            $sebelumnya = $this->progres->modulSebelumnya($modul);

            return redirect()
                ->route('modul.index')
                ->with('galat', 'Modul ini masih terkunci. Selesaikan '.($sebelumnya?->label ?? 'modul sebelumnya').' terlebih dahulu.');
        }

        if ($user && ! $pratinjau) {
            $this->progres->mulai($user, $modul);
        }

        $kelompok = $this->kelompokMateri($modul);
        $totalMateri = count($kelompok);
        $halaman = max(1, min((int) $request->query('materi', 1), $totalMateri));

        return view('modul.detail', [
            'modul'               => $modul,
            'blokMateri'          => $kelompok[$halaman - 1],
            'halaman'             => $halaman,
            'totalMateri'         => $totalMateri,
            'adaQuiz'             => (bool) $modul->kuis?->bisaDikerjakan(),
            'pratinjau'           => $pratinjau,
            'tampilkanModalLogin' => ! $user,
        ]);
    }

    /** Modul tanpa quiz aktif dianggap selesai setelah materinya dibaca sampai akhir. */
    public function selesai(Request $request, int $id): RedirectResponse
    {
        $modul = Modul::with('kuis')->findOrFail($id);
        abort_unless($modul->dapatDilihatPublik(), 404);

        $user = $request->user();
        abort_unless($this->progres->modulTerbuka($user, $modul), 403);

        if ($modul->kuis?->bisaDikerjakan()) {
            return redirect()->route('modul.quiz.tampil', $modul->id);
        }

        $this->progres->tandaiSelesai($user, $modul);

        return redirect()->route('modul.index')->with('sukses', $modul->label.' selesai. Kerja bagus!');
    }

    /**
     * Bagi blok materi menjadi kelompok: tiap blok heading memulai kelompok baru.
     * Selalu menghasilkan minimal satu kelompok (boleh kosong).
     *
     * @return array<int, array<int, \App\Models\MateriBlok>>
     */
    private function kelompokMateri(Modul $modul): array
    {
        $kelompok = [];
        $saatIni = [];

        foreach ($modul->blok as $blok) {
            if ($blok->tipe === 'heading' && $saatIni !== []) {
                $kelompok[] = $saatIni;
                $saatIni = [];
            }
            $saatIni[] = $blok;
        }

        $kelompok[] = $saatIni;

        return $kelompok;
    }
}
