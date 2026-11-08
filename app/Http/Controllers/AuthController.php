<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Masuk, daftar, dan keluar.
 * Form berada di modal login (components/modal-login.blade.php).
 * Galat validasi disimpan pada error bag "login" agar tidak tercampur form lain.
 */
class AuthController extends Controller
{
    /**
     * Dipakai middleware auth: Guest yang membuka halaman User
     * dialihkan ke Beranda dengan modal login terbuka.
     */
    public function formMasuk()
    {
        return view('beranda', [
            'daftarModulPratinjau' => BerandaController::modulPratinjau(),
            'tampilkanModalLogin'  => true,
        ]);
    }

    public function masuk(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('login', [
            'email'      => ['required', 'email'],
            'kata_sandi' => ['required'],
        ], [], [
            'email'      => 'alamat email',
            'kata_sandi' => 'kata sandi',
        ]);

        $kredensial = ['email' => $data['email'], 'password' => $data['kata_sandi']];

        // Form ini khusus akun User ("role" ikut diperiksa, sama seperti form login Admin).
        $berhasil = Auth::attempt($kredensial + ['role' => User::PERAN_USER], $request->boolean('ingat'));

        if (! $berhasil) {
            // Akun Admin yang salah pintu: sandi sudah terbukti benar, tetapi sesi TIDAK dibuat.
            // Arahkan ke form login Admin (hanya email yang dibawa, sandi diketik ulang di sana).
            if (Auth::validate($kredensial + ['role' => User::PERAN_ADMIN])) {
                return redirect()
                    ->route('admin.login')
                    ->withInput($request->only('email'))
                    ->with('info_admin', 'Akun ini terdaftar sebagai Admin. Silakan masuk melalui halaman ini.');
            }

            return back()
                ->withInput($request->only('nama', 'email'))
                ->withErrors(['email' => 'Email atau kata sandi tidak cocok.'], 'login')
                ->with('mode_modal', 'masuk');
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['terakhir_login_pada' => now()])->save();

        return redirect()
            ->intended($this->tujuanAman($request))
            ->with('sukses', 'Selamat datang kembali, '.$request->user()->name.'!');
    }

    public function daftar(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('login', [
            'nama'       => ['required', 'string', 'min:2', 'max:100'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'kata_sandi' => ['required', 'string', 'min:8', 'max:255'],
        ], [], [
            'nama'       => 'nama lengkap',
            'email'      => 'alamat email',
            'kata_sandi' => 'kata sandi',
        ]);

        $user = User::create([
            'name'     => trim($data['nama']),
            'email'    => $data['email'],
            'password' => $data['kata_sandi'], // di-hash otomatis oleh cast pada model User
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->intended($this->tujuanAman($request))
            ->with('sukses', 'Akun berhasil dibuat. Selamat belajar, '.$user->name.'!');
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('sukses', 'Kamu sudah keluar. Sampai jumpa!');
    }

    /**
     * Tujuan setelah login: halaman asal form (hidden input "kembali").
     * Hanya alamat pada situs yang sama yang diterima (mencegah open redirect).
     */
    private function tujuanAman(Request $request): string
    {
        $url = trim((string) $request->input('kembali', ''));

        if ($url === '') {
            return route('beranda');
        }

        // Alamat relatif milik situs ini (tetapi bukan "//host-lain").
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return url($url);
        }

        $host = parse_url($url, PHP_URL_HOST);
        if ($host !== null && $host === $request->getHost()) {
            return $url;
        }

        return route('beranda');
    }
}
