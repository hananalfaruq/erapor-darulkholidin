<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WaliKelasMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->attributes->get('wali_kelas_aktif')) {
            return redirect()->route('dashboard')
                ->with('error', 'Menu tersebut hanya dapat diakses oleh Wali Kelas pada Tahun Ajaran aktif.');
        }

        return $next($request);
    }
}