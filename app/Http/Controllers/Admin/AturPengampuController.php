<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuruMapelKelas;
use App\Models\KelasMapel;
use App\Models\User;
use Illuminate\Http\Request;

class AturPengampuController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');

        $pengampuList = GuruMapelKelas::with(['guru', 'kelasMapel.kelas', 'kelasMapel.mapel'])
            ->where('tahun_ajaran_id', $ta->id)
            ->latest()
            ->get();

        $guruList = User::where('role', 'guru')->orderBy('nama_lengkap')->get();

        // Hanya tampilkan kelas-mapel yang belum punya pengampu (F-PG-02)
        $availableKelasMapel = KelasMapel::with(['kelas', 'mapel'])
            ->where('tahun_ajaran_id', $ta->id)
            ->whereDoesntHave('pengampu')
            ->get()
            ->sortBy(fn($km) => $km->kelas->tingkat . '-' . $km->kelas->nama_kelas . '-' . $km->mapel->nama_mapel);

        return view('admin.atur_pengampu.index', compact('pengampuList', 'guruList', 'availableKelasMapel'));
    }

    public function store(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');

        $validated = $request->validate([
            'guru_id'          => ['required', 'exists:users,id'],
            'kelas_mapel_ids'  => ['required', 'array', 'min:1'],
            'kelas_mapel_ids.*'=> ['integer', 'exists:kelas_mapel,id'],
        ]);

        $berhasil = 0;
        $dilewati = 0;

        foreach ($validated['kelas_mapel_ids'] as $kmId) {
            $exists = GuruMapelKelas::where('kelas_mapel_id', $kmId)
                ->where('tahun_ajaran_id', $ta->id)
                ->exists();

            if ($exists) {
                $dilewati++;
                continue;
            }

            GuruMapelKelas::create([
                'guru_id'         => $validated['guru_id'],
                'kelas_mapel_id'  => $kmId,
                'tahun_ajaran_id' => $ta->id,
            ]);
            $berhasil++;
        }

        return back()->with('success', "Penugasan selesai: {$berhasil} kelas-mapel berhasil ditambahkan, {$dilewati} dilewati.");
    }

    public function destroy(GuruMapelKelas $pengampu)
    {
        $pengampu->delete();
        return back()->with('success', 'Penugasan guru pengampu berhasil dihapus.');
    }
}