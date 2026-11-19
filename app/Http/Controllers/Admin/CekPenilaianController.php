<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuruMapelKelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;

class CekPenilaianController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');

        $guruList = User::where('role', 'guru')
            ->whereHas('pengampu', fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with(['pengampu' => fn($q) => $q->where('tahun_ajaran_id', $ta->id)->with(['kelasMapel.kelas', 'kelasMapel.mapel'])])
            ->orderBy('nama_lengkap')
            ->get();

        $monitoringGuru = [];

        foreach ($guruList as $guru) {
            $rincian = [];
            $totalKelasMapel = $guru->pengampu->count();
            $jumlahKelasLengkap = 0;
            $totalNilaiMasuk = 0;

            foreach ($guru->pengampu as $p) {
                $namaKelas = $p->kelasMapel->kelas->nama_kelas;
                $totalSiswa = Siswa::where('tahun_ajaran_id', $ta->id)
                    ->where('kelas_asli', $namaKelas)
                    ->count();

                $terisi = $p->nilai()
                    ->whereHas('siswa', fn($q) => $q->where('kelas_asli', $namaKelas))
                    ->count();

                $lengkap = $totalSiswa > 0 && $terisi >= $totalSiswa;
                if ($lengkap) {
                    $jumlahKelasLengkap++;
                }
                $totalNilaiMasuk += $terisi;

                $rincian[] = [
                    'nama_kelas'  => $namaKelas,
                    'nama_mapel'  => $p->kelasMapel->mapel->nama_mapel,
                    'terisi'      => $terisi,
                    'total_siswa' => $totalSiswa,
                    'lengkap'     => $lengkap,
                ];
            }

            // Indikator Warna: Hijau / Kuning / Merah (F-CP-01 & AC-5)
            if ($totalKelasMapel > 0 && $jumlahKelasLengkap === $totalKelasMapel) {
                $statusWarna = 'hijau';
            } elseif ($totalNilaiMasuk > 0) {
                $statusWarna = 'kuning';
            } else {
                $statusWarna = 'merah';
            }

            $monitoringGuru[] = [
                'guru'        => $guru,
                'status'      => $statusWarna,
                'rincian'     => $rincian,
                'jml_lengkap' => $jumlahKelasLengkap,
                'total_ampu'  => $totalKelasMapel,
            ];
        }

        return view('admin.cek_penilaian.index', compact('monitoringGuru'));
    }
}