<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Login dan logout khusus Admin. Akun biasa (role user) tidak dapat masuk dari sini. */
class AuthAdminController extends Controller
{
    public function formMasuk()
    {
        if (Auth::check() && Auth::user()->adalahAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.masuk');
    }

    public function masuk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'      => ['required', 'email'],
            'kata_sandi' => ['required'],
        ], [], ['email' => 'alamat email', 'kata_sandi' => 'kata sandi']);

        // "role" ikut diperiksa saat autentikasi: akun user biasa selalu gagal di sini.
        $berhasil = Auth::attempt(
            ['email' => $data['email'], 'password' => $data['kata_sandi'], 'role' => User::PERAN_ADMIN],
            $request->boolean('ingat')
        );

        if (! $berhasil) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi tidak cocok, atau akun ini bukan Admin.']);
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['terakhir_login_pada' => now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('sukses', 'Kamu sudah keluar dari Admin CMS.');
    }
}
