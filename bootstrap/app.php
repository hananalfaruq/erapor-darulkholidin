<?php

use App\Http\Middleware\EnsureAcademicContext;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\WaliKelasMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Percayai semua Reverse Proxy (Cloudflare Tunnel & Jaringan Lokal)
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'academic'   => EnsureAcademicContext::class,
            'role'       => RoleMiddleware::class,
            'wali_kelas' => WaliKelasMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();