<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Edit profil User. Sesuai desain, hanya Nama Lengkap yang dapat diubah;
 * Alamat Email ditampilkan sebagai informasi (tidak dapat diubah).
 */
class ProfilController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('profil', [
            'nama' => ['required', 'string', 'min:2', 'max:100'],
        ], [], [
            'nama' => 'nama lengkap',
        ]);

        $request->user()->update(['name' => trim($data['nama'])]);

        return back()->with('sukses', 'Nama berhasil diperbarui.');
    }
}