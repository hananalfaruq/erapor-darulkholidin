<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Siswa;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');
        $waliKelas = $request->attributes->get('wali_kelas_aktif');

        $siswaList = Siswa::with(['absensi' => fn($q) => $q->where('tahun_ajaran_id', $ta->id)])
            ->where('tahun_ajaran_id', $ta->id)
            ->where('kelas_asli', $waliKelas->kelas->nama_kelas)
            ->orderBy('nama_lengkap')
            ->get();

        return view('guru.absensi.index', compact('waliKelas', 'siswaList'));
    }

    public function save(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');
        $absensiInputs = $request->input('absensi', []);

        foreach ($absensiInputs as $siswaId => $data) {
            Absensi::updateOrCreate(
                ['siswa_id' => $siswaId, 'tahun_ajaran_id' => $ta->id],
                [
                    'sakit' => max(0, (int) ($data['sakit'] ?? 0)),
                    'izin'  => max(0, (int) ($data['izin'] ?? 0)),
                    'alfa'  => max(0, (int) ($data['alfa'] ?? 0)),
                ]
            );
        }

        return back()->with('success', 'Rekap absensi kelas berhasil disimpan.');
    }
}