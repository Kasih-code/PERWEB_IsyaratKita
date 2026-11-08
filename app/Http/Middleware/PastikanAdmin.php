<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Melindungi seluruh route CMS Admin di sisi server (bukan sekadar menyembunyikan menu).
 *  - Belum login          -> diarahkan ke halaman login Admin.
 *  - Login tapi bukan admin -> 403 (User biasa tidak boleh masuk CMS).
 */
class PastikanAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->guest(route('admin.login'));
        }

        if (! Auth::user()->adalahAdmin()) {
            abort(403, 'Halaman ini hanya untuk Admin IsyaratKita.');
        }

        return $next($request);
    }
}
