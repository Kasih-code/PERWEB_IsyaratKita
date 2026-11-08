<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Unggahan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Profil Admin: lihat, edit data pribadi + foto, dan ubah kata sandi. */
class ProfilAdminController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $agen = (string) $request->userAgent();

        return view('admin.profil.show', [
            'admin'  => $user,
            'sesi'   => ['perangkat' => self::namaPerangkat($agen), 'ip' => $request->ip()],
        ]);
    }

    public function edit(Request $request)
    {
        return view('admin.profil.edit', ['admin' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'                  => ['required', 'string', 'min:2', 'max:100'],
            'email'                 => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'whatsapp'              => ['nullable', 'regex:/^[0-9+\s-]{8,20}$/'],
            'foto'                  => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'mimetypes:image/jpeg,image/png', 'max:2048'],
            'sandi_sekarang'        => ['nullable', 'required_with:sandi_baru', 'current_password'],
            'sandi_baru'            => ['nullable', 'string', 'min:8', 'max:255', 'confirmed:sandi_baru_confirmation'],
        ], [
            'whatsapp.regex'                  => 'Nomor WhatsApp tidak valid.',
            'sandi_sekarang.current_password' => 'Kata sandi saat ini tidak cocok.',
            'sandi_sekarang.required_with'    => 'Isi kata sandi saat ini untuk mengganti kata sandi.',
            'sandi_baru.confirmed'            => 'Konfirmasi kata sandi tidak sama.',
        ], [
            'name'     => 'nama lengkap',
            'whatsapp' => 'nomor WhatsApp',
            'foto'     => 'foto profil',
            'sandi_baru' => 'kata sandi baru',
        ]);

        $perubahan = [
            'name'     => trim($data['name']),
            'email'    => $data['email'],
            'whatsapp' => $data['whatsapp'] ?? null,
        ];

        if ($foto = $request->file('foto')) {
            Unggahan::hapus($user->foto);
            $perubahan['foto'] = Unggahan::simpan($foto, 'profil');
        }

        if (! empty($data['sandi_baru'])) {
            $perubahan['password'] = $data['sandi_baru'];          // di-hash otomatis oleh cast
            $perubahan['sandi_diubah_pada'] = now();
        }

        $user->forceFill($perubahan)->save();

        if (! empty($data['sandi_baru'])) {
            $request->session()->regenerate();
        }

        return redirect()->route('admin.profil')->with('sukses', 'Profil berhasil diperbarui.');
    }

    /** "Chrome · Windows" dari User-Agent (cukup untuk tampilan, bukan identifikasi). */
    public static function namaPerangkat(string $agen): string
    {
        $browser = match (true) {
            str_contains($agen, 'Edg/')    => 'Edge',
            str_contains($agen, 'OPR/')    => 'Opera',
            str_contains($agen, 'Firefox') => 'Firefox',
            str_contains($agen, 'Chrome')  => 'Chrome',
            str_contains($agen, 'Safari')  => 'Safari',
            default                        => 'Peramban',
        };

        $os = match (true) {
            str_contains($agen, 'Windows')                              => 'Windows',
            str_contains($agen, 'iPhone') || str_contains($agen, 'iPad') => 'iOS',
            str_contains($agen, 'Android')                              => 'Android',
            str_contains($agen, 'Mac OS')                               => 'macOS',
            str_contains($agen, 'Linux')                                => 'Linux',
            default                                                     => 'perangkat ini',
        };

        return $browser.' · '.$os;
    }
}
