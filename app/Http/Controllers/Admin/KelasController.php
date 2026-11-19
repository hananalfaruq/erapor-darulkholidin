<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\AcademicSyncService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');

        $kelasList = Kelas::withCount('kelasMapel')
            ->where('tahun_ajaran_id', $ta->id)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get()
            ->map(function ($k) use ($ta) {
                $k->jumlah_siswa = Siswa::where('tahun_ajaran_id', $ta->id)
                    ->where('kelas_asli', $k->nama_kelas)
                    ->count();
                return $k;
            });

        return view('admin.kelas.index', compact('kelasList'));
    }

    public function store(Request $request, AcademicSyncService $syncService)
    {
        $ta = $request->attributes->get('ta_aktif');

        $validated = $request->validate([
            'nama_kelas' => [
                'required', 'string', 'max:50',
                Rule::unique('kelas', 'nama_kelas')->where('tahun_ajaran_id', $ta->id),
            ],
            'tingkat' => ['required', 'integer', 'between:1,6'],
        ]);

        $kelas = Kelas::create([
            'nama_kelas'      => $validated['nama_kelas'],
            'tingkat'         => $validated['tingkat'],
            'tahun_ajaran_id' => $ta->id,
        ]);

        // Auto-sync mapel untuk tingkat ini (F-KLS-02)
        $syncService->syncMapelForKelas($kelas);

        return back()->with('success', 'Kelas berhasil ditambahkan dan mata pelajaran telah ditautkan otomatis.');
    }

    public function update(Request $request, Kelas $kelas, AcademicSyncService $syncService)
    {
        $ta = $request->attributes->get('ta_aktif');

        $validated = $request->validate([
            'nama_kelas' => [
                'required', 'string', 'max:50',
                Rule::unique('kelas', 'nama_kelas')->where('tahun_ajaran_id', $ta->id)->ignore($kelas->id),
            ],
            'tingkat' => ['required', 'integer', 'between:1,6'],
        ]);

        $oldNama = $kelas->nama_kelas;
        $oldTingkat = (int) $kelas->tingkat;

        $kelas->update($validated);

        // Jika nama kelas berubah, perbarui juga kolom kelas_asli pada siswa (Fix R4)
        if ($oldNama !== $kelas->nama_kelas) {
            Siswa::where('tahun_ajaran_id', $ta->id)
                ->where('kelas_asli', $oldNama)
                ->update(['kelas_asli' => $kelas->nama_kelas, 'kelas_id' => $kelas->id]);
        }

        // Sinkronisasi ulang kelas-mapel jika tingkat berubah (Fix R6)
        $syncService->syncMapelForKelas($kelas, $oldTingkat);

        return back()->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return back()->with('success', 'Kelas beserta relasinya berhasil dihapus.');
    }
}