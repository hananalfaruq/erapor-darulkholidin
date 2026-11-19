<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\MapelJenjang;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class AcademicSyncService
{
    /**
     * Dipanggil saat Kelas dibuat atau diubah tingkatnya (Fix R6)
     */
    public function syncMapelForKelas(Kelas $kelas, ?int $oldTingkat = null): void
    {
        DB::transaction(function () use ($kelas, $oldTingkat) {
            // Jika tingkat berubah, hapus kelas_mapel lama yang belum punya pengampu
            if ($oldTingkat !== null && $oldTingkat != $kelas->tingkat) {
                KelasMapel::where('kelas_id', $kelas->id)
                    ->where('tahun_ajaran_id', $kelas->tahun_ajaran_id)
                    ->whereDoesntHave('pengampu')
                    ->delete();
            }

            // Ambil semua mapel untuk tingkat kelas ini pada TA terkait
            $mapelIds = MapelJenjang::where('tingkat', $kelas->tingkat)
                ->where('tahun_ajaran_id', $kelas->tahun_ajaran_id)
                ->pluck('mapel_id');

            foreach ($mapelIds as $mapelId) {
                KelasMapel::firstOrCreate([
                    'kelas_id'        => $kelas->id,
                    'mapel_id'        => $mapelId,
                    'tahun_ajaran_id' => $kelas->tahun_ajaran_id,
                ]);
            }

            // Sinkronkan juga siswa yang memiliki nama kelas_asli sama (Fix R4)
            Siswa::where('tahun_ajaran_id', $kelas->tahun_ajaran_id)
                ->where('kelas_asli', $kelas->nama_kelas)
                ->update(['kelas_id' => $kelas->id]);
        });
    }

    /**
     * Dipanggil saat Mata Pelajaran ditambah atau diubah tingkatnya (F-MP-02 & F-MP-03)
     */
    public function syncTingkatForMapel(MataPelajaran $mapel, array $tingkatBaru, int $taId): void
    {
        DB::transaction(function () use ($mapel, $tingkatBaru, $taId) {
            $tingkatBaru = array_map('intval', $tingkatBaru);

            $tingkatLama = MapelJenjang::where('mapel_id', $mapel->id)
                ->where('tahun_ajaran_id', $taId)
                ->pluck('tingkat')
                ->map(fn($t) => (int) $t)
                ->toArray();

            // 1. Tingkat yang dihapus
            $tingkatDihapus = array_diff($tingkatLama, $tingkatBaru);
            if (!empty($tingkatDihapus)) {
                MapelJenjang::where('mapel_id', $mapel->id)
                    ->where('tahun_ajaran_id', $taId)
                    ->whereIn('tingkat', $tingkatDihapus)
                    ->delete();

                $kelasIds = Kelas::where('tahun_ajaran_id', $taId)
                    ->whereIn('tingkat', $tingkatDihapus)
                    ->pluck('id');

                // Hanya hapus kelas_mapel jika BELUM punya pengampu (Aturan F-MP-03)
                KelasMapel::where('mapel_id', $mapel->id)
                    ->where('tahun_ajaran_id', $taId)
                    ->whereIn('kelas_id', $kelasIds)
                    ->whereDoesntHave('pengampu')
                    ->delete();
            }

            // 2. Pastikan tingkat terpilih terdaftar dan kelas_mapel terbentuk
            foreach ($tingkatBaru as $tingkat) {
                MapelJenjang::firstOrCreate([
                    'mapel_id'        => $mapel->id,
                    'tingkat'         => $tingkat,
                    'tahun_ajaran_id' => $taId,
                ]);

                $kelasList = Kelas::where('tahun_ajaran_id', $taId)
                    ->where('tingkat', $tingkat)
                    ->get();

                foreach ($kelasList as $kelas) {
                    KelasMapel::firstOrCreate([
                        'kelas_id'        => $kelas->id,
                        'mapel_id'        => $mapel->id,
                        'tahun_ajaran_id' => $taId,
                    ]);
                }
            }
        });
    }
}