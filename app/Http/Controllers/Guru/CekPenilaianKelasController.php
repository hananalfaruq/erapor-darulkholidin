<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\KelasMapel;
use App\Models\Siswa;
use Illuminate\Http\Request;

class CekPenilaianKelasController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');
        $waliKelas = $request->attributes->get('wali_kelas_aktif');
        $kelas = $waliKelas->kelas;

        $totalSiswa = Siswa::where('tahun_ajaran_id', $ta->id)
            ->where('kelas_asli', $kelas->nama_kelas)
            ->count();

        $progresMapel = KelasMapel::with(['mapel', 'pengampu.guru'])
            ->where('tahun_ajaran_id', $ta->id)
            ->where('kelas_id', $kelas->id)
            ->get()
            ->map(function ($km) use ($kelas, $totalSiswa) {
                $terisi = $km->pengampu
                    ? $km->pengampu->nilai()->whereHas('siswa', fn($q) => $q->where('kelas_asli', $kelas->nama_kelas))->count()
                    : 0;

                return [
                    'kode_mapel' => $km->mapel->kode_mapel,
                    'nama_mapel' => $km->mapel->nama_mapel,
                    'nama_guru'  => $km->pengampu?->guru?->nama_lengkap ?? 'Belum Ada Pengampu',
                    'terisi'     => $terisi,
                    'total'      => $totalSiswa,
                    'lengkap'    => $totalSiswa > 0 && $terisi >= $totalSiswa,
                ];
            });

        return view('guru.cek_penilaian_kelas.index', compact('kelas', 'progresMapel', 'totalSiswa'));
    }
}