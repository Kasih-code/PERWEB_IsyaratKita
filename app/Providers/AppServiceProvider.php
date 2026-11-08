<?php

namespace App\Providers;

use App\Models\Donasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Kompatibel dengan MySQL/MariaDB versi lama (hindari error "key too long").
        Schema::defaultStringLength(191);

        // Di server produksi, semua link dibuat dengan https (dibutuhkan Midtrans dan browser).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        // Status login dibagikan ke semua view (dipakai Navbar, layout, dan halaman).
        // Dihitung per-view (bukan View::share di boot) karena sesi baru tersedia
        // setelah middleware berjalan.
        //   $statusLogin   : true jika User sudah login, false untuk Guest
        //   $penggunaLogin : model User yang sedang login (atau null)
        View::composer('*', function ($view) {
            $view->with('statusLogin', Auth::check());
            $view->with('penggunaLogin', Auth::user());
        });

        // Notifikasi lonceng pada header Admin: 5 donasi berhasil terbaru (data nyata).
        View::composer('layouts.admin', function ($view) {
            $view->with('notifDonasi', Donasi::berhasil()->latest('dibayar_pada')->latest('id')->take(5)->get());
        });
    }
}
