<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $pengaturan = Pengaturan::current();
        return view('auth.login', compact('pengaturan'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Cek apakah ada Tahun Ajaran Aktif (F-AUTH-02)
        $taAktif = TahunAjaran::active();
        if (!$taAktif) {
            return back()->withErrors([
                'login' => 'Login ditolak: Belum ada Tahun Ajaran yang aktif. Silakan hubungi Administrator.'
            ])->onlyInput('username');
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            session(['tahun_ajaran_id' => $taAktif->id]);
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'Username atau password yang Anda masukkan salah.'
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}