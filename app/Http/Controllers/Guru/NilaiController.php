<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\GuruMapelKelas;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');
        $user = $request->user();

        $groupedPengampu = GuruMapelKelas::with(['kelasMapel.kelas', 'kelasMapel.mapel'])
            ->where('guru_id', $user->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->get()
            ->groupBy(fn($item) => $item->kelasMapel->mapel->nama_mapel);

        return view('guru.nilai.index', compact('groupedPengampu'));
    }

    public function detail(Request $request, GuruMapelKelas $pengampu)
    {
        $ta = $request->attributes->get('ta_aktif');

        if ($pengampu->guru_id !== $request->user()->id || $pengampu->tahun_ajaran_id !== $ta->id) {
            return redirect()->route('guru.nilai.index')
                ->with('error', 'Anda tidak berhak mengakses kelas-mapel tersebut.');
        }

        $pengampu->load(['kelasMapel.kelas', 'kelasMapel.mapel']);
        $namaKelas = $pengampu->kelasMapel->kelas->nama_kelas;

        $siswaList = Siswa::where('tahun_ajaran_id', $ta->id)
            ->where('kelas_asli', $namaKelas)
            ->orderBy('nama_lengkap')
            ->get();

        $nilaiMap = Nilai::where('guru_mapel_kelas_id', $pengampu->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->pluck('nilai_akhir', 'siswa_id');

        return view('guru.nilai.detail', compact('pengampu', 'siswaList', 'nilaiMap'));
    }

    public function save(Request $request, GuruMapelKelas $pengampu)
    {
        $ta = $request->attributes->get('ta_aktif');

        if ($pengampu->guru_id !== $request->user()->id || $pengampu->tahun_ajaran_id !== $ta->id) {
            return redirect()->route('guru.nilai.index');
        }

        $nilaiInputs = $request->input('nilai', []);
        $disimpan = 0;
        $dihapus = 0;

        DB::transaction(function () use ($nilaiInputs, $pengampu, $ta, &$disimpan, &$dihapus) {
            foreach ($nilaiInputs as $siswaId => $val) {
                $valTrimmed = trim((string) $val);

                if ($valTrimmed === '') {
                    $deleted = Nilai::where('siswa_id', $siswaId)
                        ->where('guru_mapel_kelas_id', $pengampu->id)
                        ->where('tahun_ajaran_id', $ta->id)
                        ->delete();
                    if ($deleted) $dihapus++;
                    continue;
                }

                if (is_numeric($valTrimmed)) {
                    $angka = (int) $valTrimmed;
                    if ($angka >= 2 && $angka <= 9) {
                        Nilai::updateOrCreate(
                            [
                                'siswa_id'            => $siswaId,
                                'guru_mapel_kelas_id' => $pengampu->id,
                                'tahun_ajaran_id'     => $ta->id,
                            ],
                            ['nilai_akhir' => $angka]
                        );
                        $disimpan++;
                    }
                }
            }
        });

        return back()->with('success', "Nilai berhasil diperbarui ({$disimpan} disimpan, {$dihapus} dikosongkan/dihapus).");
    }

    public function downloadTemplate(Request $request, GuruMapelKelas $pengampu)
    {
        $ta = $request->attributes->get('ta_aktif');

        if ($pengampu->guru_id !== $request->user()->id || $pengampu->tahun_ajaran_id !== $ta->id) {
            return redirect()->route('guru.nilai.index');
        }

        $pengampu->load(['kelasMapel.kelas', 'kelasMapel.mapel']);
        $namaKelas = $pengampu->kelasMapel->kelas->nama_kelas;
        $namaMapel = $pengampu->kelasMapel->mapel->nama_mapel;

        $siswaList = Siswa::where('tahun_ajaran_id', $ta->id)
            ->where('kelas_asli', $namaKelas)
            ->orderBy('nama_lengkap')
            ->get();

        $nilaiMap = Nilai::where('guru_mapel_kelas_id', $pengampu->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->pluck('nilai_akhir', 'siswa_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Input Nilai');

        $sheet->setCellValue('A1', "TEMPLATE INPUT NILAI GURU — {$namaMapel}");
        $sheet->setCellValue('A2', "Kelas: {$namaKelas} | Tahun Ajaran: {$ta->nama_tahun_ajaran}");
        $sheet->setCellValue('A3', "Petunjuk: Isi kolom Nilai Akhir (Kolom E) dengan angka bulat 2 sampai 9. Jangan mengubah kolom NIS.");
        $sheet->getStyle('A1:A2')->getFont()->setBold(true);

        $headers = [
            'A5' => 'No',
            'B5' => 'NIS',
            'C5' => 'Nama Siswa',
            'D5' => 'Kelas',
            'E5' => 'Nilai Akhir (2-9)',
        ];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A5:E5')->getFont()->setBold(true);
        $sheet->getStyle('A5:E5')->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2EFDA');

        $rowNum = 6;
        foreach ($siswaList as $idx => $s) {
            $sheet->setCellValue("A{$rowNum}", $idx + 1);
            $sheet->setCellValue("B{$rowNum}", $s->nis);
            $sheet->setCellValue("C{$rowNum}", $s->nama_lengkap);
            $sheet->setCellValue("D{$rowNum}", $s->kelas_asli);
            $sheet->setCellValue("E{$rowNum}", $nilaiMap[$s->id] ?? '');
            $rowNum++;
        }

        $lastRow = max(6, $rowNum - 1);

        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword('darulkholidin');

        $sheet->getStyle("E6:E{$lastRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');
        $sheet->getStyle("E6:E{$lastRow}")->getProtection()
            ->setLocked(Protection::PROTECTION_UNPROTECTED);

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $safeMapel = preg_replace('/[^A-Za-z0-9]/', '_', $namaMapel);
        $fileName = "Template_Nilai_Kelas_{$namaKelas}_{$safeMapel}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }

    public function importUjian(Request $request, GuruMapelKelas $pengampu)
    {
        $ta = $request->attributes->get('ta_aktif');

        if ($pengampu->guru_id !== $request->user()->id || $pengampu->tahun_ajaran_id !== $ta->id) {
            return redirect()->route('guru.nilai.index');
        }

        $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $spreadsheet = IOFactory::load($request->file('file_excel')->getPathname());
        $sheet = $spreadsheet->getSheetByName('Input Nilai') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $namaKelas = $pengampu->kelasMapel->kelas->nama_kelas;
        $berhasil = 0;
        $dilewati = 0;

        // Ambil peta NIS -> siswa_id sekaligus di memori agar tidak query berulang di dalam loop
        $siswaByNis = Siswa::where('tahun_ajaran_id', $ta->id)
            ->where('kelas_asli', $namaKelas)
            ->pluck('id', 'nis');

        DB::transaction(function () use ($rows, $siswaByNis, $pengampu, $ta, &$berhasil, &$dilewati) {
            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex < 6) continue;

                $nis = trim((string) ($row['B'] ?? ''));
                $rawNilai = trim((string) ($row['E'] ?? ''));

                if ($nis === '' || $rawNilai === '') {
                    continue;
                }

                if (!is_numeric($rawNilai)) {
                    $dilewati++;
                    continue;
                }

                $nilaiInt = (int) round((float) $rawNilai);
                if ($nilaiInt < 2 || $nilaiInt > 9) {
                    $dilewati++;
                    continue;
                }

                $siswaId = $siswaByNis[$nis] ?? null;
                if ($siswaId) {
                    Nilai::updateOrCreate(
                        [
                            'siswa_id'            => $siswaId,
                            'guru_mapel_kelas_id' => $pengampu->id,
                            'tahun_ajaran_id'     => $ta->id,
                        ],
                        ['nilai_akhir' => $nilaiInt]
                    );
                    $berhasil++;
                } else {
                    $dilewati++;
                }
            }
        });

        return back()->with('success', "Impor template nilai selesai: {$berhasil} nilai disimpan, {$dilewati} baris di luar rentang (2-9) dilewati.");
    }
}