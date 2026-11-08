<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MateriBlok;
use App\Models\Modul;
use App\Models\Pertanyaan;
use App\Support\Unggahan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Kelola Modul (daftar) dan Editor Modul (informasi, pengaturan, cover, blok materi).
 * Perubahan langsung memengaruhi halaman Modul Guest/User karena datanya dari database.
 */
class ModulAdminController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('cari', ''));
        $status = $request->query('status');
        $urut = $request->query('urut', 'diperbarui');

        $daftar = Modul::query()
            ->withCount('blok')
            ->when($cari !== '', function ($q) use ($cari) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $cari).'%';
                $q->where(function ($w) use ($like, $cari) {
                    $w->where('judul', 'like', $like)->orWhere('deskripsi', 'like', $like);
                    // Pencarian "modul 2" / "2" juga cocok dengan nomor modul.
                    if (preg_match('/(\d+)\s*$/', $cari, $m)) {
                        $w->orWhere('nomor', (int) $m[1]);
                    }
                });
            })
            ->when(in_array($status, ['terbit', 'draft'], true), fn ($q) => $q->where('status', $status))
            ->when($urut === 'nomor', fn ($q) => $q->urut())
            ->when($urut === 'judul', fn ($q) => $q->orderBy('judul'))
            ->when(! in_array($urut, ['nomor', 'judul'], true), fn ($q) => $q->orderByDesc('updated_at')->orderByDesc('id'))
            ->paginate(10)
            ->withQueryString();

        // Jumlah soal per modul (satu query untuk halaman ini).
        $jumlahSoal = Pertanyaan::query()
            ->join('kuis', 'kuis.id', '=', 'pertanyaan.kuis_id')
            ->whereIn('kuis.modul_id', $daftar->pluck('id'))
            ->groupBy('kuis.modul_id')
            ->selectRaw('kuis.modul_id as modul_id, count(*) as total')
            ->pluck('total', 'modul_id');

        return view('admin.modul.index', compact('daftar', 'jumlahSoal', 'cari', 'status', 'urut'));
    }

    public function create()
    {
        return view('admin.modul.editor', $this->dataEditor(new Modul([
            'status' => 'draft', 'tampil_publik' => true, 'nomor' => Modul::count() + 1,
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        $modul = DB::transaction(function () use ($request, $data) {
            $modul = Modul::create([
                'nomor'         => Modul::count() + 1,
                'judul'         => trim($data['judul']),
                'deskripsi'     => $data['deskripsi'] ?? null,
                'status'        => $data['status'],
                'tampil_publik' => $request->boolean('tampil_publik'),
                'dibuat_oleh'   => $request->user()->id,
            ]);

            $this->simpanCover($request, $modul);
            $this->simpanBlok($request, $modul);
            Modul::susunUlangNomor($modul->id, (int) $data['nomor']);

            return $modul;
        });

        return redirect()->route('admin.modul.edit', $modul)
            ->with('sukses', 'Modul "'.$modul->judul.'" berhasil dibuat.');
    }

    public function edit(Modul $modul)
    {
        return view('admin.modul.editor', $this->dataEditor($modul));
    }

    public function update(Request $request, Modul $modul): RedirectResponse
    {
        $data = $this->validasi($request, $modul);

        DB::transaction(function () use ($request, $modul, $data) {
            $modul->update([
                'judul'         => trim($data['judul']),
                'deskripsi'     => $data['deskripsi'] ?? null,
                'status'        => $data['status'],
                'tampil_publik' => $request->boolean('tampil_publik'),
            ]);

            $this->simpanCover($request, $modul);
            $this->simpanBlok($request, $modul);

            if ((int) $data['nomor'] !== (int) $modul->nomor) {
                Modul::susunUlangNomor($modul->id, (int) $data['nomor']);
            }
        });

        $modul->refresh();

        return redirect()->route('admin.modul.edit', $modul)
            ->with('sukses', 'Perubahan Modul '.$modul->nomor.' berhasil disimpan.');
    }

    public function destroy(Modul $modul): RedirectResponse
    {
        $label = $modul->label;
        $modul->delete(); // blok, kuis, soal, pilihan ikut terhapus (cascade) + berkas dibersihkan oleh model
        Modul::susunUlangNomor();

        return redirect()->route('admin.modul.index')->with('sukses', $label.' berhasil dihapus.');
    }

    /* ------------------------------------------------------------------ */

    private function dataEditor(Modul $modul): array
    {
        $modul->loadMissing(['blok', 'kuis']);
        $jumlahModul = Modul::count() + ($modul->exists ? 0 : 1);

        return [
            'modul'       => $modul,
            'jumlahSoal'  => $modul->exists ? (int) $modul->kuis?->pertanyaan()->count() : 0,
            'jumlahModul' => $jumlahModul,
            // Blok awal editor: dari old() jika validasi gagal, selain itu dari database.
            'blokAwal'    => $this->blokUntukEditor($modul),
        ];
    }

    private function blokUntukEditor(Modul $modul): array
    {
        $lama = old('blok');
        if (is_array($lama)) {
            return collect($lama)->map(function ($b) use ($modul) {
                $id = isset($b['id']) ? (int) $b['id'] : null;
                $ada = $id ? $modul->blok->firstWhere('id', $id) : null;

                return [
                    'id'        => $ada?->id,
                    'tipe'      => $b['tipe'] ?? 'teks',
                    'teks'      => $b['teks'] ?? '',
                    'alt'       => $b['alt'] ?? '',
                    'video_url' => $b['video_url'] ?? '',
                    'durasi'    => $b['durasi'] ?? '',
                    'gambar_url' => $ada?->gambar_url,
                ];
            })->values()->all();
        }

        return $modul->blok->map(fn (MateriBlok $b) => [
            'id'        => $b->id,
            'tipe'      => $b->tipe,
            'teks'      => (string) $b->teks,
            'alt'       => (string) $b->alt,
            'video_url' => (string) $b->video_url,
            'durasi'    => (string) $b->durasi,
            'gambar_url' => $b->gambar_url,
        ])->values()->all();
    }

    private function validasi(Request $request, ?Modul $modul = null): array
    {
        $maksNomor = Modul::count() + ($modul ? 0 : 1);

        $data = $request->validate([
            'judul'     => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status'    => ['required', Rule::in(['draft', 'terbit'])],
            'nomor'     => ['required', 'integer', 'min:1', 'max:'.$maksNomor],
            'cover'     => ['nullable', ...Unggahan::aturanGambar(5120)],
            'blok'                => ['nullable', 'array', 'max:100'],
            'blok.*.id'           => ['nullable', 'integer'],
            'blok.*.salin_dari'   => ['nullable', 'integer'],
            'blok.*.tipe'         => ['required', Rule::in(MateriBlok::TIPE)],
            'blok.*.teks'         => ['nullable', 'string', 'max:10000'],
            'blok.*.alt'          => ['nullable', 'string', 'max:200'],
            'blok.*.video_url'    => ['nullable', 'url:http,https', 'max:255'],
            'blok.*.durasi'       => ['nullable', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'blok.*.gambar'       => ['nullable', ...Unggahan::aturanGambar(5120)],
        ], [
            'blok.*.durasi.regex'      => 'Durasi video harus berformat mm:ss, contoh 03:24.',
            'blok.*.video_url.url'     => 'URL video harus diawali http:// atau https://.',
            'blok.*.gambar.max'        => 'Ukuran gambar maksimal 5 MB.',
        ], [
            'judul'  => 'judul modul',
            'nomor'  => 'urutan modul',
            'cover'  => 'cover modul',
            'blok.*.teks'      => 'isi blok',
            'blok.*.video_url' => 'URL video',
            'blok.*.durasi'    => 'durasi video',
            'blok.*.gambar'    => 'gambar',
        ]);

        // Isi setiap blok harus lengkap sesuai tipenya.
        $galat = [];
        foreach ((array) $request->input('blok', []) as $i => $blok) {
            $nomor = $i + 1;
            $tipe = $blok['tipe'] ?? '';
            $id = isset($blok['id']) ? (int) $blok['id'] : null;
            $punyaGambar = $request->hasFile("blok.$i.gambar")
                || ($id && $modul?->blok->firstWhere('id', $id)?->gambar)
                || (! empty($blok['salin_dari']) && $modul?->blok->firstWhere('id', (int) $blok['salin_dari'])?->gambar);

            if (in_array($tipe, ['heading', 'teks'], true) && trim((string) ($blok['teks'] ?? '')) === '') {
                $galat["blok.$i.teks"] = "Isi blok {$nomor} (".($tipe === 'heading' ? 'heading' : 'teks').') tidak boleh kosong.';
            }
            if ($tipe === 'gambar' && ! $punyaGambar) {
                $galat["blok.$i.gambar"] = "Blok {$nomor} (gambar) membutuhkan gambar.";
            }
            if ($tipe === 'video' && trim((string) ($blok['video_url'] ?? '')) === '') {
                $galat["blok.$i.video_url"] = "Blok {$nomor} (video) membutuhkan URL video.";
            }
        }
        if ($galat) {
            throw ValidationException::withMessages($galat);
        }

        return $data;
    }

    private function simpanCover(Request $request, Modul $modul): void
    {
        if ($file = $request->file('cover')) {
            $lama = $modul->cover;
            $modul->update(['cover' => Unggahan::simpan($file, 'cover')]);
            Unggahan::hapus($lama);
        }
    }

    /**
     * Sinkronkan blok materi dengan isi form (urutan = urutan di form).
     * Blok lama yang tidak dikirim lagi dihapus beserta berkasnya.
     * Id blok dari form hanya dipercaya jika memang milik modul ini.
     */
    private function simpanBlok(Request $request, Modul $modul): void
    {
        $milikModul = $modul->blok()->get()->keyBy('id');
        $dipakai = [];
        $berkasHapus = [];

        foreach ((array) $request->input('blok', []) as $i => $blok) {
            $tipe = $blok['tipe'];
            $ada = isset($blok['id']) ? $milikModul->get((int) $blok['id']) : null;

            $isi = [
                'urutan'    => $i + 1,
                'tipe'      => $tipe,
                'teks'      => in_array($tipe, ['heading', 'teks'], true) ? trim((string) ($blok['teks'] ?? '')) : null,
                'alt'       => $tipe === 'gambar' ? ($blok['alt'] ?? null) : null,
                'video_url' => $tipe === 'video' ? ($blok['video_url'] ?? null) : null,
                'durasi'    => $tipe === 'video' ? ($blok['durasi'] ?? null) : null,
            ];

            if ($tipe === 'gambar') {
                $gambar = $ada?->gambar;
                if ($file = $request->file("blok.$i.gambar")) {
                    if ($gambar) {
                        $berkasHapus[] = $gambar;
                    }
                    $gambar = Unggahan::simpan($file, 'materi');
                } elseif (! $ada && ! empty($blok['salin_dari'])) {
                    // Blok hasil duplikasi: salin berkas sumber agar tiap blok punya berkas sendiri.
                    $sumber = $milikModul->get((int) $blok['salin_dari']);
                    $gambar = $sumber?->gambar ? $this->salinBerkas($sumber->gambar) : null;
                }
                $isi['gambar'] = $gambar;
            } else {
                if ($ada?->gambar) {
                    $berkasHapus[] = $ada->gambar;
                }
                $isi['gambar'] = null;
            }

            if ($ada) {
                $ada->update($isi);
                $dipakai[] = $ada->id;
            } else {
                $dipakai[] = $modul->blok()->create($isi)->id;
            }
        }

        foreach ($milikModul as $id => $blokLama) {
            if (! in_array($id, $dipakai, true)) {
                $berkasHapus[] = $blokLama->gambar;
                $blokLama->delete();
            }
        }

        foreach ($berkasHapus as $path) {
            Unggahan::hapus($path);
        }
    }

    private function salinBerkas(string $path): ?string
    {
        $disk = Storage::disk(Unggahan::DISK);
        if (! $disk->exists($path)) {
            return null;
        }

        $baru = 'materi/'.bin2hex(random_bytes(16)).'.'.pathinfo($path, PATHINFO_EXTENSION);
        $disk->copy($path, $baru);

        return $baru;
    }
}
