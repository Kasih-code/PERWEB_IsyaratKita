
<?php

use App\Http\Controllers\Admin\AsetController;
use App\Http\Controllers\Admin\AuthAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonasiAdminController;
use App\Http\Controllers\Admin\KuisAdminController;
use App\Http\Controllers\Admin\ModulAdminController;
use App\Http\Controllers\Admin\PertanyaanAdminController;
use App\Http\Controllers\Admin\ProfilAdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DonasiController;
use App\Http\Controllers\KamusController;
use App\Http\Controllers\ModulController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Route Web IsyaratKita
|--------------------------------------------------------------------------
| Dibagi menjadi empat kelompok:
|
| 1. ROUTE PUBLIK (Guest + User)
|    Beranda, Kamus, Modul, dan Donasi dapat dibuka siapa pun.
|
| 2. ROUTE KHUSUS TAMU (middleware "guest")
|    Proses masuk dan daftar.
|
| 3. ROUTE USER (middleware "auth")
|    Keluar, edit profil, quiz dan hasil quiz, serta proses donasi.
|
| 4. ROUTE ADMIN (prefix /admin)
|    Halaman login Admin terbuka; semua route lainnya dilindungi
|    middleware "admin".
*/


/* ---------- 1. Publik ---------- */

Route::get('/', [BerandaController::class, 'index'])->name('beranda');

Route::prefix('kamus')->name('kamus.')->group(function () {
    Route::get('/', [KamusController::class, 'index'])->name('index');
    Route::get('/{slug}', [KamusController::class, 'detail'])->name('detail');
});

Route::prefix('modul')->name('modul.')->group(function () {
    Route::get('/', [ModulController::class, 'index'])->name('index');
    Route::get('/{id}', [ModulController::class, 'detail'])
        ->whereNumber('id')
        ->name('detail');
});

Route::get('/donasi', [DonasiController::class, 'index'])->name('donasi.index');

// Notifikasi pembayaran dari Midtrans
Route::post('/midtrans/notifikasi', [DonasiController::class, 'notifikasi'])
    ->name('midtrans.notifikasi');


/* ---------- 2. Khusus tamu: masuk & daftar ---------- */

Route::middleware('guest')->group(function () {

    // Nama "login" dipakai middleware auth.
    Route::get('/masuk', [AuthController::class, 'formMasuk'])->name('login');

    Route::post('/masuk', [AuthController::class, 'masuk'])
        ->middleware('throttle:10,1')
        ->name('login.proses');

    Route::post('/daftar', [AuthController::class, 'daftar'])
        ->middleware('throttle:10,1')
        ->name('daftar.proses');
});


/* ---------- 3. User (wajib login) ---------- */

Route::middleware('auth')->group(function () {

    Route::post('/keluar', [AuthController::class, 'keluar'])->name('logout');

    // Fitur khusus User: akun Admin tidak boleh memakainya (middleware "bukan-admin").
    Route::middleware('bukan-admin')->group(function () {

    Route::patch('/profil', [ProfilController::class, 'update'])->name('profil.update');


    // Donasi: menyimpan data donasi dan mengecek status pembayaran.
    Route::post('/donasi', [DonasiController::class, 'simpan'])
        ->name('donasi.simpan');

    Route::get('/donasi/{kode}/status', [DonasiController::class, 'status'])
        ->name('donasi.status');


    // Modul tanpa quiz aktif: tandai selesai setelah materi dibaca.
    Route::post('/modul/{id}/selesai', [ModulController::class, 'selesai'])
        ->whereNumber('id')
        ->name('modul.selesai');


    Route::prefix('modul/{id}/quiz')
        ->whereNumber('id')
        ->name('modul.quiz.')
        ->group(function () {

            Route::get('/', [QuizController::class, 'tampil'])->name('tampil');

            Route::post('/', [QuizController::class, 'kirim'])->name('kirim');

            Route::get('/hasil', [QuizController::class, 'hasil'])->name('hasil');
        });
    });
});


/* ---------- 4. Admin (CMS) ---------- */

Route::prefix('admin')->name('admin.')->group(function () {

    // Login Admin (tanpa middleware admin).
    Route::get('/login', [AuthAdminController::class, 'formMasuk'])
        ->name('login');

    Route::post('/login', [AuthAdminController::class, 'masuk'])
        ->middleware('throttle:5,1')
        ->name('login.proses');


    // Semua route di bawah ini hanya untuk akun dengan role admin.
    Route::middleware('admin')->group(function () {

        Route::redirect('/', '/admin/dashboard');

        Route::post('/logout', [AuthAdminController::class, 'keluar'])
            ->name('logout');


        Route::get('/dashboard', DashboardController::class)->name('dashboard');


        // Kelola Asset BISINDO
        Route::get('/asset', [AsetController::class, 'index'])->name('asset.index');

        Route::post('/asset', [AsetController::class, 'store'])->name('asset.store');

        Route::put('/asset/{aset}', [AsetController::class, 'update'])
            ->whereNumber('aset')
            ->name('asset.update');

        Route::delete('/asset/{aset}', [AsetController::class, 'destroy'])
            ->whereNumber('aset')
            ->name('asset.destroy');


        // Kelola Modul + Editor Modul
        Route::get('/modul', [ModulAdminController::class, 'index'])
            ->name('modul.index');

        Route::get('/modul/baru', [ModulAdminController::class, 'create'])
            ->name('modul.create');

        Route::post('/modul', [ModulAdminController::class, 'store'])
            ->name('modul.store');

        Route::get('/modul/{modul}/edit', [ModulAdminController::class, 'edit'])
            ->whereNumber('modul')
            ->name('modul.edit');

        Route::put('/modul/{modul}', [ModulAdminController::class, 'update'])
            ->whereNumber('modul')
            ->name('modul.update');

        Route::delete('/modul/{modul}', [ModulAdminController::class, 'destroy'])
            ->whereNumber('modul')
            ->name('modul.destroy');


        // Quiz per modul + Editor Soal
        Route::prefix('modul/{modul}/quiz')
            ->whereNumber('modul')
            ->name('kuis.')
            ->group(function () {

                Route::get('/', [KuisAdminController::class, 'index'])->name('index');

                Route::put('/', [KuisAdminController::class, 'update'])->name('update');

                Route::get('/soal/baru', [PertanyaanAdminController::class, 'create'])
                    ->name('soal.create');

                Route::post('/soal', [PertanyaanAdminController::class, 'store'])
                    ->name('soal.store');

                Route::get('/soal/{pertanyaan}/edit', [PertanyaanAdminController::class, 'edit'])
                    ->whereNumber('pertanyaan')
                    ->name('soal.edit');

                Route::put('/soal/{pertanyaan}', [PertanyaanAdminController::class, 'update'])
                    ->whereNumber('pertanyaan')
                    ->name('soal.update');

                Route::delete('/soal/{pertanyaan}', [PertanyaanAdminController::class, 'destroy'])
                    ->whereNumber('pertanyaan')
                    ->name('soal.destroy');

                Route::post('/soal/{pertanyaan}/pindah', [PertanyaanAdminController::class, 'pindah'])
                    ->whereNumber('pertanyaan')
                    ->name('soal.pindah');
            });


        // Laporan Donasi Masuk
        Route::get('/donasi', [DonasiAdminController::class, 'index'])
            ->name('donasi.index');

        Route::get('/donasi/export', [DonasiAdminController::class, 'export'])
            ->name('donasi.export');

        Route::get('/donasi/{donasi}/bukti', [DonasiAdminController::class, 'bukti'])
            ->whereNumber('donasi')
            ->name('donasi.bukti');


        // Profil Admin
        Route::get('/profil', [ProfilAdminController::class, 'show'])
            ->name('profil');

        Route::get('/profil/edit', [ProfilAdminController::class, 'edit'])
            ->name('profil.edit');

        Route::put('/profil', [ProfilAdminController::class, 'update'])
            ->name('profil.update');
    });
});
