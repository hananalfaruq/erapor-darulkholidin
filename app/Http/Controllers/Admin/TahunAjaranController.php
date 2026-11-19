<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjaran = TahunAjaran::orderByDesc('id')->get();
        return view('admin.tahun_ajaran.index', compact('tahunAjaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun' => ['required', 'string', 'max:20', 'regex:/^\d{4}\/\d{4}$/'], // Contoh: 2026/2027
        ], [
            'tahun.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2026/2027).'
        ]);

        $ganjil = "{$request->tahun} - Ganjil";
        $genap  = "{$request->tahun} - Genap";

        if (TahunAjaran::whereIn('nama_tahun_ajaran', [$ganjil, $genap])->exists()) {
            return back()->with('error', "Tahun Ajaran {$request->tahun} sudah ada.");
        }

        DB::transaction(function () use ($ganjil, $genap) {
            TahunAjaran::create(['nama_tahun_ajaran' => $ganjil, 'is_active' => false]);
            TahunAjaran::create(['nama_tahun_ajaran' => $genap, 'is_active' => false]);
        });

        return back()->with('success', "Tahun Ajaran {$request->tahun} (Ganjil & Genap) berhasil ditambahkan.");
    }

    public function activate(TahunAjaran $tahunAjaran)
    {
        DB::transaction(function () use ($tahunAjaran) {
            TahunAjaran::query()->update(['is_active' => false]);
            $tahunAjaran->update(['is_active' => true]);
        });

        session(['tahun_ajaran_id' => $tahunAjaran->id]);
        return back()->with('success', "Tahun Ajaran {$tahunAjaran->nama_tahun_ajaran} berhasil diaktifkan.");
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->is_active) {
            return back()->with('error', 'Tahun Ajaran yang sedang aktif tidak dapat dihapus.');
        }

        $tahunAjaran->delete();
        return back()->with('success', 'Tahun Ajaran berhasil dihapus.');
    }
}