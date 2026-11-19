<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Services\AcademicSyncService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');

        $mapelList = MataPelajaran::with([
            'mapelJenjang' => fn($q) => $q->where('tahun_ajaran_id', $ta->id)->orderBy('tingkat')
        ])->orderBy('kode_mapel')->get();

        return view('admin.mapel.index', compact('mapelList'));
    }

    public function store(Request $request, AcademicSyncService $syncService)
    {
        $ta = $request->attributes->get('ta_aktif');

        $validated = $request->validate([
            'kode_mapel' => ['required', 'string', 'max:20', 'unique:mata_pelajaran,kode_mapel'],
            'nama_mapel' => ['required', 'string', 'max:255'],
            'tingkat'    => ['required', 'array', 'min:1'],
            'tingkat.*'  => ['integer', 'between:1,6'],
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => strtoupper($validated['kode_mapel']),
            'nama_mapel' => $validated['nama_mapel'],
        ]);

        $syncService->syncTingkatForMapel($mapel, $validated['tingkat'], $ta->id);

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan dan ditautkan ke kelas terkait.');
    }

    public function update(Request $request, MataPelajaran $mapel, AcademicSyncService $syncService)
    {
        $ta = $request->attributes->get('ta_aktif');

        $validated = $request->validate([
            'kode_mapel' => ['required', 'string', 'max:20', Rule::unique('mata_pelajaran', 'kode_mapel')->ignore($mapel->id)],
            'nama_mapel' => ['required', 'string', 'max:255'],
            'tingkat'    => ['required', 'array', 'min:1'],
            'tingkat.*'  => ['integer', 'between:1,6'],
        ]);

        $mapel->update([
            'kode_mapel' => strtoupper($validated['kode_mapel']),
            'nama_mapel' => $validated['nama_mapel'],
        ]);

        $syncService->syncTingkatForMapel($mapel, $validated['tingkat'], $ta->id);

        return back()->with('success', 'Mata pelajaran dan pemetaan tingkat berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mapel)
    {
        // Cascade delete otomatis menghapus mapel_jenjang, kelas_mapel, pengampu, dan nilai (F-MP-04)
        $mapel->delete();
        return back()->with('success', 'Mata pelajaran beserta seluruh relasi dan nilainya berhasil dihapus.');
    }
}