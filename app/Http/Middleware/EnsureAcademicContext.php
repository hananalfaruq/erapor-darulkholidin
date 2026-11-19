<?php

namespace App\Http\Middleware;

use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureAcademicContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $taAktif = TahunAjaran::active();

        if (!$taAktif) {
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('login')
                ->withErrors(['login' => 'Belum ada Tahun Ajaran yang aktif. Silakan hubungi Administrator.']);
        }

        $user = Auth::user();
        $waliKelas = $user->role === 'guru' ? $user->waliKelasAktif($taAktif->id) : null;
        $pengaturan = Pengaturan::current();

        // Simpan ke request attributes agar mudah diakses di Controller
        $request->attributes->set('ta_aktif', $taAktif);
        $request->attributes->set('wali_kelas_aktif', $waliKelas);
        $request->attributes->set('pengaturan', $pengaturan);

        // Share otomatis ke semua Blade View
        View::share('taAktif', $taAktif);
        View::share('waliKelasAktif', $waliKelas);
        View::share('pengaturanSekolah', $pengaturan);

        return $next($request);
    }
}