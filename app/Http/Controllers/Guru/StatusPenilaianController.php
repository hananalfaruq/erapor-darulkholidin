<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\GuruMapelKelas;
use App\Models\Siswa;
use Illuminate\Http\Request;

class StatusPenilaianController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');
        $user = $request->user();

        $statusList = GuruMapelKelas::with(['kelasMapel.kelas', 'kelasMapel.mapel'])
            ->where('guru_id', $user->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->get()
            ->map(function ($p) use ($ta) {
                $namaKelas = $p->kelasMapel->kelas->nama_kelas;
                $totalSiswa = Siswa::where('tahun_ajaran_id', $ta->id)->where('kelas_asli', $namaKelas)->count();
                $terisi = $p->nilai()->whereHas('siswa', fn($q) => $q->where('kelas_asli', $namaKelas))->count();

                return [
                    'pengampu_id' => $p->id,
                    'nama_kelas'  => $namaKelas,
                    'nama_mapel'  => $p->kelasMapel->mapel->nama_mapel,
                    'terisi'      => $terisi,
                    'total_siswa' => $totalSiswa,
                    'lengkap'     => $totalSiswa > 0 && $terisi >= $totalSiswa,
                ];
            });

        return view('guru.status_penilaian.index', compact('statusList'));
    }
}