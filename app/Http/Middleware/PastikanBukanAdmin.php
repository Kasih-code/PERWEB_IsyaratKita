<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fitur khusus User (quiz, progres modul, donasi, edit profil User) tidak
 * boleh dipakai akun Admin, sesuai rancangan: Admin hanya mengelola CMS.
 * Admin yang mencoba diarahkan kembali ke Dashboard Admin.
 */
class PastikanBukanAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->adalahAdmin()) {
            $pesan = 'Fitur ini hanya untuk User. Akun Admin mengelola konten lewat CMS.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $pesan], 403);
            }

            return redirect()->route('admin.dashboard')->with('galat', $pesan);
        }

        return $next($request);
    }
}