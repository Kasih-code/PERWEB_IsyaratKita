<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Pengecualian CSRF untuk notifikasi pembayaran Midtrans.
        $middleware->validateCsrfTokens(except: [
            'midtrans/notifikasi',
        ]);
        
        // Alias "admin": hanya akun dengan role admin yang boleh mengakses CMS.
        $middleware->alias([
            'admin' => \App\Http\Middleware\PastikanAdmin::class,
            'bukan-admin' => \App\Http\Middleware\PastikanBukanAdmin::class, 
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
