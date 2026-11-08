<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsetBisindo;
use App\Support\Unggahan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Kelola Asset BISINDO: gambar isyarat per huruf/kata yang tampil di Kamus Visual.
 * Berkas disimpan pada disk "unggahan" (public/uploads/asset).
 */
class AsetController extends Controller
{
    public function index(Request $request)
    {
        $cari = trim((string) $request->query('cari', ''));
        $status = $request->query('status');

        $daftar = AsetBisindo::query()
            ->when($cari !== '', function ($q) use ($cari) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $cari).'%';
                $q->where(fn ($w) => $w->where('kata', 'like', $like)->orWhere('nama_berkas', 'like', $like));
            })
            ->when(in_array($status, ['aktif', 'nonaktif'], true), fn ($q) => $q->where('status', $status))
            ->orderBy('kata')->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.asset.index', [
            'daftar'    => $daftar,
            'total'     => AsetBisindo::count(),
            'cari'      => $cari,
            'status'    => $status,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('asset', $this->aturan(berkasWajib: true), [], $this->nama());

        $berkas = $request->file('berkas');
        AsetBisindo::create([
            'kata'          => trim($data['kata']),
            'slug'          => Str::slug($data['kata']),
            'berkas'        => Unggahan::simpan($berkas, 'asset'),
            'nama_berkas'   => $this->namaTampil($data['kata'], $berkas->getClientOriginalExtension()),
            'status'        => $data['status'],
            'diunggah_oleh' => $request->user()->id,
        ]);

        return redirect()->route('admin.asset.index')->with('sukses', 'Asset "'.trim($data['kata']).'" berhasil ditambahkan.');
    }

    public function update(Request $request, AsetBisindo $aset): RedirectResponse
    {
        $data = $request->validateWithBag('asset', $this->aturan(berkasWajib: false), [], $this->nama());

        $perubahan = [
            'kata'   => trim($data['kata']),
            'slug'   => Str::slug($data['kata']),
            'status' => $data['status'],
        ];

        if ($berkas = $request->file('berkas')) {
            $lama = $aset->berkas;
            $perubahan['berkas'] = Unggahan::simpan($berkas, 'asset');
            $perubahan['nama_berkas'] = $this->namaTampil($data['kata'], $berkas->getClientOriginalExtension());
            Unggahan::hapus($lama);
        }

        $aset->update($perubahan);

        return redirect()->route('admin.asset.index', $request->only('page'))
            ->with('sukses', 'Asset "'.$aset->kata.'" berhasil diperbarui.');
    }

    public function destroy(AsetBisindo $aset): RedirectResponse
    {
        $kata = $aset->kata;
        $aset->delete(); // berkas ikut dihapus oleh model

        return redirect()->route('admin.asset.index')->with('sukses', 'Asset "'.$kata.'" berhasil dihapus.');
    }

    private function aturan(bool $berkasWajib): array
    {
        return [
            'kata'   => ['required', 'string', 'max:60'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'berkas' => [$berkasWajib ? 'required' : 'nullable', ...Unggahan::aturanGambar(5120)],
        ];
    }

    private function nama(): array
    {
        return ['kata' => 'kata / huruf', 'berkas' => 'gambar'];
    }

    /** Nama berkas untuk tampilan, mis. "bisindo-terima-kasih-9f3a1c.webp" (bukan nama asli pengguna). */
    private function namaTampil(string $kata, string $ekstensi): string
    {
        $ekstensi = strtolower($ekstensi) ?: 'png';

        return 'bisindo-'.(Str::slug($kata) ?: 'asset').'-'.Str::lower(Str::random(5)).'.'.$ekstensi;
    }
}
