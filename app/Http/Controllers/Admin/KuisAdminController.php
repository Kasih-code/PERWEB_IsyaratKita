<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modul;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Quiz Modul: pengaturan kuis (status, urutan, nilai lulus, durasi) + daftar soal. */
class KuisAdminController extends Controller
{
    public function index(Modul $modul)
    {
        $kuis = $modul->kuis()->firstOrCreate([]);
        $soal = $kuis->pertanyaan()->with('pilihan')->get();

        return view('admin.kuis.index', compact('modul', 'kuis', 'soal'));
    }

    public function update(Request $request, Modul $modul): RedirectResponse
    {
        $data = $request->validate([
            'status'       => ['required', Rule::in(['aktif', 'nonaktif'])],
            'urutan_soal'  => ['required', Rule::in(['berurutan', 'acak'])],
            'nilai_lulus'  => ['required', 'integer', 'between:1,100'],
            'durasi_menit' => ['nullable', 'integer', 'between:1,600'],
        ], [], [
            'nilai_lulus'  => 'nilai lulus',
            'durasi_menit' => 'durasi',
        ]);

        $modul->kuis()->firstOrCreate([])->update([
            'status'       => $data['status'],
            'urutan_soal'  => $data['urutan_soal'],
            'nilai_lulus'  => $data['nilai_lulus'],
            'durasi_menit' => $data['durasi_menit'] ?? null,
        ]);

        return redirect()->route('admin.kuis.index', $modul)->with('sukses', 'Pengaturan quiz '.$modul->label.' berhasil disimpan.');
    }
}
