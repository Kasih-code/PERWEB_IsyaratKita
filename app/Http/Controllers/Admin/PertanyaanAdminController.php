<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsetBisindo;
use App\Models\Modul;
use App\Models\Pertanyaan;
use App\Support\Unggahan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Editor Soal Quiz: pertanyaan, gambar pendukung, pilihan jawaban (teks/gambar),
 * jawaban benar, poin, kesulitan, acak pilihan, dan pembahasan.
 */
class PertanyaanAdminController extends Controller
{
    public const MAKS_PILIHAN = 6;

    public function create(Modul $modul)
    {
        $kuis = $modul->kuis()->firstOrCreate([]);

        return view('admin.kuis.editor', $this->dataEditor($modul, new Pertanyaan([
            'kuis_id' => $kuis->id, 'tipe_jawaban' => 'teks', 'poin' => 10, 'kesulitan' => 'dasar', 'acak_pilihan' => true,
        ]), $kuis->pertanyaan()->count() + 1));
    }

    public function store(Request $request, Modul $modul): RedirectResponse
    {
        $kuis = $modul->kuis()->firstOrCreate([]);
        $data = $this->validasi($request);

        DB::transaction(function () use ($request, $kuis, $data) {
            $pertanyaan = $kuis->pertanyaan()->create([
                'urutan' => ($kuis->pertanyaan()->max('urutan') ?? 0) + 1,
            ] + $this->isiPertanyaan($request, $data));

            $this->simpanPilihan($request, $pertanyaan, $data);
        });

        return redirect()->route('admin.kuis.index', $modul)->with('sukses', 'Soal berhasil ditambahkan.');
    }

    public function edit(Modul $modul, Pertanyaan $pertanyaan)
    {
        $this->pastikanMilik($modul, $pertanyaan);
        $nomor = $modul->kuis->pertanyaan()->where(fn ($q) => $q->where('urutan', '<', $pertanyaan->urutan)
            ->orWhere(fn ($w) => $w->where('urutan', $pertanyaan->urutan)->where('id', '<=', $pertanyaan->id)))->count();

        return view('admin.kuis.editor', $this->dataEditor($modul, $pertanyaan->load('pilihan'), $nomor));
    }

    public function update(Request $request, Modul $modul, Pertanyaan $pertanyaan): RedirectResponse
    {
        $this->pastikanMilik($modul, $pertanyaan);
        $data = $this->validasi($request, $pertanyaan);

        DB::transaction(function () use ($request, $pertanyaan, $data) {
            $pertanyaan->update($this->isiPertanyaan($request, $data, $pertanyaan));
            $this->simpanPilihan($request, $pertanyaan, $data);
        });

        return redirect()->route('admin.kuis.index', $modul)->with('sukses', 'Soal berhasil diperbarui.');
    }

    public function destroy(Modul $modul, Pertanyaan $pertanyaan): RedirectResponse
    {
        $this->pastikanMilik($modul, $pertanyaan);
        $pertanyaan->delete();

        return redirect()->route('admin.kuis.index', $modul)->with('sukses', 'Soal berhasil dihapus.');
    }

    /** Pindah urutan soal satu langkah: arah = naik | turun. */
    public function pindah(Request $request, Modul $modul, Pertanyaan $pertanyaan): RedirectResponse
    {
        $this->pastikanMilik($modul, $pertanyaan);
        $arah = $request->validate(['arah' => ['required', Rule::in(['naik', 'turun'])]])['arah'];

        DB::transaction(function () use ($modul, $pertanyaan, $arah) {
            $urut = $modul->kuis->pertanyaan()->get()->values();
            $posisi = $urut->search(fn ($p) => $p->id === $pertanyaan->id);
            $tujuan = $arah === 'naik' ? $posisi - 1 : $posisi + 1;

            if ($posisi === false || $tujuan < 0 || $tujuan >= $urut->count()) {
                return;
            }

            $item = $urut->pull($posisi);
            $urut = $urut->values();
            $urut->splice($tujuan, 0, [$item]);

            foreach ($urut->values() as $i => $p) {
                $p->forceFill(['urutan' => $i + 1])->saveQuietly();
            }
        });

        return redirect()->route('admin.kuis.index', $modul);
    }

    /* ------------------------------------------------------------------ */

    private function pastikanMilik(Modul $modul, Pertanyaan $pertanyaan): void
    {
        abort_unless($modul->kuis && $pertanyaan->kuis_id === $modul->kuis->id, 404);
    }

    private function dataEditor(Modul $modul, Pertanyaan $pertanyaan, int $nomor): array
    {
        $pilihan = old('pilihan');
        if (is_array($pilihan)) {
            $pilihan = collect($pilihan)->map(function ($o) use ($pertanyaan) {
                $id = isset($o['id']) ? (int) $o['id'] : null;
                $ada = $id ? $pertanyaan->pilihan->firstWhere('id', $id) : null;

                return ['id' => $ada?->id, 'teks' => $o['teks'] ?? '', 'gambar_url' => $ada?->gambar_url];
            })->values()->all();
            $benar = (int) old('benar', 0);
        } elseif ($pertanyaan->exists) {
            $pilihan = $pertanyaan->pilihan->map(fn ($o) => ['id' => $o->id, 'teks' => (string) $o->teks, 'gambar_url' => $o->gambar_url])->all();
            $benar = max(0, (int) $pertanyaan->pilihan->search(fn ($o) => $o->benar));
        } else {
            $pilihan = array_fill(0, 4, ['id' => null, 'teks' => '', 'gambar_url' => null]);
            $benar = 0;
        }

        return [
            'modul'       => $modul,
            'pertanyaan'  => $pertanyaan,
            'nomor'       => $nomor,
            'pilihan'     => $pilihan,
            'benar'       => $benar,
            'daftarAset'  => AsetBisindo::aktif()->orderBy('kata')->pluck('kata')->unique()->values(),
            'maksPilihan' => self::MAKS_PILIHAN,
        ];
    }

    private function validasi(Request $request, ?Pertanyaan $pertanyaan = null): array
    {
        $data = $request->validate([
            'teks'            => ['required', 'string', 'max:1000'],
            'gambar'          => ['nullable', ...Unggahan::aturanGambar(5120)],
            'hapus_gambar'    => ['nullable', 'boolean'],
            'huruf_aset'      => ['nullable', 'string', 'max:60'],
            'tipe_jawaban'    => ['required', Rule::in(array_keys(Pertanyaan::TIPE_JAWABAN))],
            'poin'            => ['required', 'integer', 'between:1,100'],
            'kesulitan'       => ['required', Rule::in(array_keys(Pertanyaan::KESULITAN))],
            'pembahasan'      => ['nullable', 'string', 'max:2000'],
            'benar'           => ['required', 'integer', 'min:0'],
            'pilihan'         => ['required', 'array', 'min:2', 'max:'.self::MAKS_PILIHAN],
            'pilihan.*.id'    => ['nullable', 'integer'],
            'pilihan.*.teks'  => ['nullable', 'string', 'max:255'],
            'pilihan.*.gambar' => ['nullable', ...Unggahan::aturanGambar(5120)],
        ], [
            'pilihan.min'    => 'Minimal harus ada 2 pilihan jawaban.',
            'pilihan.max'    => 'Maksimal '.self::MAKS_PILIHAN.' pilihan jawaban.',
            'pilihan.required' => 'Minimal harus ada 2 pilihan jawaban.',
        ], [
            'teks' => 'teks pertanyaan', 'poin' => 'poin', 'gambar' => 'gambar pertanyaan',
            'pilihan.*.teks' => 'teks pilihan', 'pilihan.*.gambar' => 'gambar pilihan',
        ]);

        $jumlah = count($data['pilihan']);
        $galat = [];

        if ($data['benar'] >= $jumlah) {
            $galat['benar'] = 'Pilih satu jawaban yang benar.';
        }

        // Isi tiap pilihan harus sesuai tipe jawaban.
        foreach (array_values($request->input('pilihan', [])) as $i => $o) {
            $huruf = chr(65 + $i);
            $id = isset($o['id']) ? (int) $o['id'] : null;
            $punyaTeks = trim((string) ($o['teks'] ?? '')) !== '';
            $punyaGambar = $request->hasFile("pilihan.$i.gambar")
                || ($id && $pertanyaan?->pilihan->firstWhere('id', $id)?->gambar);

            if (in_array($data['tipe_jawaban'], ['teks', 'teks_gambar'], true) && ! $punyaTeks) {
                $galat["pilihan.$i.teks"] = "Teks pilihan {$huruf} wajib diisi.";
            }
            if (in_array($data['tipe_jawaban'], ['gambar', 'teks_gambar'], true) && ! $punyaGambar) {
                $galat["pilihan.$i.gambar"] = "Pilihan {$huruf} membutuhkan gambar.";
            }
        }

        if ($galat) {
            throw ValidationException::withMessages($galat);
        }

        return $data;
    }

    private function isiPertanyaan(Request $request, array $data, ?Pertanyaan $pertanyaan = null): array
    {
        $isi = [
            'teks'         => trim($data['teks']),
            'tipe_jawaban' => $data['tipe_jawaban'],
            'poin'         => $data['poin'],
            'kesulitan'    => $data['kesulitan'],
            'acak_pilihan' => $request->boolean('acak_pilihan'),
            'pembahasan'   => $data['pembahasan'] ?? null,
            'huruf_aset'   => ($data['huruf_aset'] ?? '') !== '' ? $data['huruf_aset'] : null,
        ];

        $gambar = $pertanyaan?->gambar;
        if ($file = $request->file('gambar')) {
            Unggahan::hapus($gambar);
            $gambar = Unggahan::simpan($file, 'quiz');
        } elseif ($request->boolean('hapus_gambar')) {
            Unggahan::hapus($gambar);
            $gambar = null;
        }
        $isi['gambar'] = $gambar;

        return $isi;
    }

    /** Sinkronkan pilihan jawaban dengan form; "benar" berisi indeks (0-based) pilihan yang benar. */
    private function simpanPilihan(Request $request, Pertanyaan $pertanyaan, array $data): void
    {
        $milik = $pertanyaan->pilihan()->get()->keyBy('id');
        $dipakai = [];
        $berkasHapus = [];
        $pakaiGambar = in_array($data['tipe_jawaban'], ['gambar', 'teks_gambar'], true);
        $pakaiTeks = in_array($data['tipe_jawaban'], ['teks', 'teks_gambar'], true);

        foreach (array_values($request->input('pilihan', [])) as $i => $o) {
            $ada = isset($o['id']) ? $milik->get((int) $o['id']) : null;
            $gambar = $ada?->gambar;

            if ($pakaiGambar && ($file = $request->file("pilihan.$i.gambar"))) {
                if ($gambar) {
                    $berkasHapus[] = $gambar;
                }
                $gambar = Unggahan::simpan($file, 'quiz');
            } elseif (! $pakaiGambar && $gambar) {
                $berkasHapus[] = $gambar;
                $gambar = null;
            }

            $isi = [
                'urutan' => $i + 1,
                'teks'   => $pakaiTeks ? trim((string) ($o['teks'] ?? '')) : null,
                'gambar' => $gambar,
                'benar'  => $i === (int) $data['benar'],
            ];

            if ($ada) {
                $ada->update($isi);
                $dipakai[] = $ada->id;
            } else {
                $dipakai[] = $pertanyaan->pilihan()->create($isi)->id;
            }
        }

        foreach ($milik as $id => $lama) {
            if (! in_array($id, $dipakai, true)) {
                $berkasHapus[] = $lama->gambar;
                $lama->delete();
            }
        }

        foreach ($berkasHapus as $path) {
            Unggahan::hapus($path);
        }
    }
}
