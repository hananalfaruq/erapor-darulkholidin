<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\GuruMapelKelas;
use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');
        $user = $request->user();

        if ($user->isAdmin()) {
            // 1. Statistik Dasar & Rincian
            $totalSiswa   = Siswa::where('tahun_ajaran_id', $ta->id)->count();
            $siswaL       = Siswa::where('tahun_ajaran_id', $ta->id)->where('jenis_kelamin', 'L')->count();
            $siswaP       = Siswa::where('tahun_ajaran_id', $ta->id)->where('jenis_kelamin', 'P')->count();

            $totalGuru    = User::where('role', 'guru')->count();
            $totalWali    = WaliKelas::where('tahun_ajaran_id', $ta->id)->count();

            $kelasList    = Kelas::with(['waliKelas.guru', 'kelasMapel.pengampu'])
                ->where('tahun_ajaran_id', $ta->id)
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get();

            $totalKelas   = $kelasList->count();
            $kelasSmp     = $kelasList->where('tingkat', '<=', 3)->count();
            $kelasSma     = $kelasList->where('tingkat', '>', 3)->count();

            $totalMapel      = MataPelajaran::count();
            $totalKelasMapel = KelasMapel::where('tahun_ajaran_id', $ta->id)->count();
            $totalPengampu   = GuruMapelKelas::where('tahun_ajaran_id', $ta->id)->count();
            $belumAdaPengampu = max(0, $totalKelasMapel - $totalPengampu);

            // 2. Rekap Progres per Kelas & Kelengkapan Nilai Nasional/Pondok
            $rekapKelas = [];
            $kmLengkap = 0;
            $kmSebagian = 0;
            $kmKosong = 0;

            foreach ($kelasList as $k) {
                $siswaIds = Siswa::where('tahun_ajaran_id', $ta->id)
                    ->where('kelas_asli', $k->nama_kelas)
                    ->pluck('id');
                $jmlSiswa = $siswaIds->count();

                $jmlMapel = $k->kelasMapel->count();
                $mapelSelesaiDiKelas = 0;

                foreach ($k->kelasMapel as $km) {
                    $terisi = 0;
                    if ($km->pengampu && $jmlSiswa > 0) {
                        $terisi = $km->pengampu->nilai()->whereIn('siswa_id', $siswaIds)->count();
                    }

                    if ($jmlSiswa > 0 && $terisi >= $jmlSiswa) {
                        $kmLengkap++;
                        $mapelSelesaiDiKelas++;
                    } elseif ($terisi > 0) {
                        $kmSebagian++;
                    } else {
                        $kmKosong++;
                    }
                }

                $absensiTerisi = $jmlSiswa > 0
                    ? Absensi::where('tahun_ajaran_id', $ta->id)->whereIn('siswa_id', $siswaIds)->count()
                    : 0;

                $rekapKelas[] = [
                    'id'             => $k->id,
                    'nama_kelas'     => $k->nama_kelas,
                    'jenjang'        => $k->tingkat <= 3 ? "SMP (Tingkat {$k->tingkat})" : "SMA (Tingkat {$k->tingkat})",
                    'wali_kelas'     => $k->waliKelas?->guru?->nama_lengkap,
                    'jml_siswa'      => $jmlSiswa,
                    'jml_mapel'      => $jmlMapel,
                    'mapel_selesai'  => $mapelSelesaiDiKelas,
                    'absensi_terisi' => $absensiTerisi,
                ];
            }

            $persenLengkap = $totalKelasMapel > 0 ? round(($kmLengkap / $totalKelasMapel) * 100) : 0;

            // 3. Ringkasan Status Guru Pengampu (Hijau / Kuning / Merah)
            $guruPengampu = User::where('role', 'guru')
                ->whereHas('pengampu', fn($q) => $q->where('tahun_ajaran_id', $ta->id))
                ->with(['pengampu' => fn($q) => $q->where('tahun_ajaran_id', $ta->id)->with('kelasMapel.kelas')])
                ->get();

            $guruHijau = 0;
            $guruKuning = 0;
            $guruMerah = 0;

            foreach ($guruPengampu as $g) {
                $ampuCount = $g->pengampu->count();
                $lengkapCount = 0;
                $nilaiCount = 0;

                foreach ($g->pengampu as $p) {
                    $namaKls = $p->kelasMapel->kelas->nama_kelas;
                    $jmlS = Siswa::where('tahun_ajaran_id', $ta->id)->where('kelas_asli', $namaKls)->count();
                    $isi  = $p->nilai()->whereHas('siswa', fn($q) => $q->where('kelas_asli', $namaKls))->count();

                    if ($jmlS > 0 && $isi >= $jmlS) $lengkapCount++;
                    $nilaiCount += $isi;
                }

                if ($ampuCount > 0 && $lengkapCount === $ampuCount) {
                    $guruHijau++;
                } elseif ($nilaiCount > 0) {
                    $guruKuning++;
                } else {
                    $guruMerah++;
                }
            }

            $stats = [
                'total_siswa'        => $totalSiswa,
                'siswa_l'            => $siswaL,
                'siswa_p'            => $siswaP,
                'total_guru'         => $totalGuru,
                'total_wali'         => $totalWali,
                'total_kelas'        => $totalKelas,
                'kelas_smp'          => $kelasSmp,
                'kelas_sma'          => $kelasSma,
                'total_mapel'        => $totalMapel,
                'total_kelas_mapel'  => $totalKelasMapel,
                'total_pengampu'     => $totalPengampu,
                'belum_ada_pengampu' => $belumAdaPengampu,
                'km_lengkap'         => $kmLengkap,
                'km_sebagian'        => $kmSebagian,
                'km_kosong'          => $kmKosong,
                'persen_lengkap'     => $persenLengkap,
                'guru_hijau'         => $guruHijau,
                'guru_kuning'        => $guruKuning,
                'guru_merah'         => $guruMerah,
            ];

            return view('dashboard.index', compact('stats', 'rekapKelas'));
        }

        // ==========================================
        // DATA DASHBOARD GURU & WALI KELAS
        // ==========================================
        $pengampuList = GuruMapelKelas::with(['kelasMapel.kelas', 'kelasMapel.mapel'])
            ->where('guru_id', $user->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->get();

        $progresKelasMapel = [];
        $jumlahLengkap = 0;

        foreach ($pengampuList as $p) {
            $kelas = $p->kelasMapel->kelas;
            $totalSiswa = Siswa::where('tahun_ajaran_id', $ta->id)
                ->where('kelas_asli', $kelas->nama_kelas)
                ->count();

            $terisi = $p->nilai()
                ->whereHas('siswa', fn($q) => $q->where('kelas_asli', $kelas->nama_kelas))
                ->count();

            $lengkap = $totalSiswa > 0 && $terisi >= $totalSiswa;
            if ($lengkap) {
                $jumlahLengkap++;
            }

            $progresKelasMapel[] = [
                'pengampu_id' => $p->id,
                'nama_kelas'  => $kelas->nama_kelas,
                'nama_mapel'  => $p->kelasMapel->mapel->nama_mapel,
                'terisi'      => $terisi,
                'total_siswa' => $totalSiswa,
                'lengkap'     => $lengkap,
            ];
        }

        $guruStats = [
            'jumlah_mapel'       => $pengampuList->pluck('kelasMapel.mapel_id')->unique()->count(),
            'jumlah_kelas'       => $pengampuList->pluck('kelasMapel.kelas_id')->unique()->count(),
            'jumlah_kelas_mapel' => $pengampuList->count(),
            'jumlah_lengkap'     => $jumlahLengkap,
        ];

        $waliKelas = $request->attributes->get('wali_kelas_aktif');
        $infoWali = null;
        if ($waliKelas) {
            $siswaIds = Siswa::where('tahun_ajaran_id', $ta->id)
                ->where('kelas_asli', $waliKelas->kelas->nama_kelas)
                ->pluck('id');

            $absensiTerisi = Absensi::where('tahun_ajaran_id', $ta->id)
                ->whereIn('siswa_id', $siswaIds)
                ->count();

            $infoWali = [
                'nama_kelas'     => $waliKelas->kelas->nama_kelas,
                'absensi_terisi' => $absensiTerisi,
                'total_siswa'    => $siswaIds->count(),
            ];
        }

        return view('dashboard.index', compact('guruStats', 'progresKelasMapel', 'infoWali'));
    }
}