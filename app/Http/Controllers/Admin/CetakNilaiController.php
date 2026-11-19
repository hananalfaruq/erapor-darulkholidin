<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\Nilai;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CetakNilaiController extends Controller
{
    public function index(Request $request)
    {
        $taAktif = $request->attributes->get('ta_aktif');
        $selectedTaId = $request->query('tahun_ajaran_id', $taAktif->id);

        $tahunAjaranList = TahunAjaran::orderByDesc('id')->get();
        $kelasList = Kelas::where('tahun_ajaran_id', $selectedTaId)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.cetak_nilai.index', compact('tahunAjaranList', 'kelasList', 'selectedTaId'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'kelas_id'        => ['required', 'exists:kelas,id'],
        ]);

        $ta = TahunAjaran::findOrFail($request->tahun_ajaran_id);
        $kelas = Kelas::findOrFail($request->kelas_id);
        $pengaturan = Pengaturan::current(); // Mengambil nama sekolah dari tabel pengaturan (Fix R8)

        $siswaList = Siswa::with('absensi')
            ->where('tahun_ajaran_id', $ta->id)
            ->where('kelas_asli', $kelas->nama_kelas)
            ->orderBy('nama_lengkap')
            ->get();

        $kelasMapelList = KelasMapel::with(['mapel', 'pengampu'])
            ->where('tahun_ajaran_id', $ta->id)
            ->where('kelas_id', $kelas->id)
            ->get()
            ->sortBy('mapel.kode_mapel')
            ->values();

        // Ambil semua nilai siswa di kelas ini
        $nilaiMap = [];
        $pengampuIds = $kelasMapelList->pluck('pengampu.id')->filter()->values();
        $allNilai = Nilai::where('tahun_ajaran_id', $ta->id)
            ->whereIn('guru_mapel_kelas_id', $pengampuIds)
            ->whereIn('siswa_id', $siswaList->pluck('id'))
            ->get();

        foreach ($allNilai as $n) {
            $nilaiMap[$n->siswa_id][$n->guru_mapel_kelas_id] = $n->nilai_akhir;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leger Nilai');

        $sheet->setCellValue('A1', strtoupper($pengaturan->nama_sekolah));
        $sheet->setCellValue('A2', "LEGER NILAI KELAS {$kelas->nama_kelas} — TA {$ta->nama_tahun_ajaran}");

        // Header Kolom (F-CN-02)
        $baseHeaders = ['No', 'NIS', 'Nama', 'Nama Arab', 'Kelas Asli', 'Kelas Dapodik'];
        $colIdx = 1;
        foreach ($baseHeaders as $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . '4', $h);
        }

        foreach ($kelasMapelList as $km) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . '4', $km->mapel->nama_mapel);
        }

        foreach (['Rata-rata', 'S', 'I', 'A'] as $tailHeader) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIdx++) . '4', $tailHeader);
        }

        // Baris Data Siswa
        $rowNum = 5;
        foreach ($siswaList as $idx => $siswa) {
            $c = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $idx + 1);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $siswa->nis);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $siswa->nama_lengkap);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $siswa->nama_arab);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $siswa->kelas_asli);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $siswa->kelas_dapodik);

            $terisiValues = [];
            foreach ($kelasMapelList as $km) {
                $pId = $km->pengampu?->id;
                $val = ($pId && isset($nilaiMap[$siswa->id][$pId])) ? $nilaiMap[$siswa->id][$pId] : null;
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $val ?? '');
                if ($val !== null) {
                    $terisiValues[] = $val;
                }
            }

            // Rata-rata 2 desimal hanya dari mapel yang terisi (F-CN-02 & AC-6)
            $rataRata = count($terisiValues) > 0
                ? number_format(array_sum($terisiValues) / count($terisiValues), 2, '.', '')
                : '-';

            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $rataRata);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $siswa->absensi?->sakit ?? 0);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $siswa->absensi?->izin ?? 0);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($c++) . $rowNum, $siswa->absensi?->alfa ?? 0);

            $rowNum++;
        }

        $fileName = "Leger_Nilai_{$kelas->nama_kelas}.xlsx";
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }
}