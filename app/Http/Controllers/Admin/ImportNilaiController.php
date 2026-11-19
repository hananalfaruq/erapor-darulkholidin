<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\GuruMapelKelas;
use App\Models\Kelas;
use App\Models\KelasMapel;
use App\Models\MapelJenjang;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportNilaiController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');
        $ringkasanTingkat = [];

        for ($t = 1; $t <= 6; $t++) {
            $kelasNama = Kelas::where('tahun_ajaran_id', $ta->id)->where('tingkat', $t)->pluck('nama_kelas');
            $ringkasanTingkat[$t] = [
                'tingkat'      => $t,
                'jenjang'      => $t <= 3 ? 'SMP' : 'SMA',
                'jumlah_kelas' => $kelasNama->count(),
                'jumlah_siswa' => Siswa::where('tahun_ajaran_id', $ta->id)->whereIn('kelas_asli', $kelasNama)->count(),
                'jumlah_mapel' => MapelJenjang::where('tahun_ajaran_id', $ta->id)->where('tingkat', $t)->count(),
            ];
        }

        return view('admin.import_nilai.index', compact('ringkasanTingkat'));
    }

    public function downloadTemplate(Request $request, int $tingkat)
    {
        $ta = $request->attributes->get('ta_aktif');
        $pengaturan = $request->attributes->get('pengaturan');

        $kelasList = Kelas::where('tahun_ajaran_id', $ta->id)->where('tingkat', $tingkat)->orderBy('nama_kelas')->get();
        $siswaList = Siswa::with('absensi')
            ->where('tahun_ajaran_id', $ta->id)
            ->whereIn('kelas_asli', $kelasList->pluck('nama_kelas'))
            ->orderBy('kelas_asli')
            ->orderBy('nama_lengkap')
            ->get();

        $mapelList = MapelJenjang::with('mapel')
            ->where('tahun_ajaran_id', $ta->id)
            ->where('tingkat', $tingkat)
            ->get()
            ->pluck('mapel')
            ->sortBy('kode_mapel')
            ->values();

        $spreadsheet = new Spreadsheet();

        // SHEET 1: PETUNJUK (F-IN-02)
        $sheetPetunjuk = $spreadsheet->getActiveSheet();
        $sheetPetunjuk->setTitle('Petunjuk');
        $sheetPetunjuk->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE NILAI & ABSENSI PER TINGKAT');
        $sheetPetunjuk->setCellValue('A3', '1. Buka sheet "Data" untuk mengisi nilai akhir dan rekap absensi.');
        $sheetPetunjuk->setCellValue('A4', '2. Kolom identitas siswa dikunci (protected). Jangan mengubah baris metadata tersembunyi (baris 4 & 5).');
        $sheetPetunjuk->setCellValue('A5', '3. Kolom berwarna BIRU adalah kolom Nilai Akhir (isi bilangan bulat 2 sampai 9).');
        $sheetPetunjuk->setCellValue('A6', '4. Kolom berwarna KUNING adalah kolom Absensi (Sakit, Izin, Alfa).');

        // SHEET 2: DATA (F-IN-02, F-IN-03, F-IN-04)
        $sheetData = $spreadsheet->createSheet();
        $sheetData->setTitle('Data');

        $sheetData->setCellValue('A1', strtoupper($pengaturan->nama_sekolah));
        $sheetData->setCellValue('A2', "TEMPLATE IMPOR NILAI TINGKAT {$tingkat} — TA {$ta->nama_tahun_ajaran}");

        // Metadata Tersembunyi: Baris 4 = tingkat, Baris 5 = mapel_id (F-IN-04)
        $sheetData->setCellValue('A4', 'META_TINGKAT');
        $sheetData->setCellValue('B4', $tingkat);
        $sheetData->setCellValue('A5', 'META_MAPEL_ID');

        // Header Tabel di Baris 6
        $headers = ['No', 'NIS', 'Nama Lengkap', 'Kelas Asli'];
        $colIdx = 1;
        foreach ($headers as $h) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
            $sheetData->setCellValue("{$colLetter}6", $h);
            $colIdx++;
        }

        // Kolom Mapel (Mulai Kolom E / index 5)
        $mapelStartCol = $colIdx;
        foreach ($mapelList as $mapel) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
            $sheetData->setCellValue("{$colLetter}5", $mapel->id); // Metadata mapel_id di baris 5
            $sheetData->setCellValue("{$colLetter}6", "{$mapel->kode_mapel} - {$mapel->nama_mapel}");
            $colIdx++;
        }
        $mapelEndCol = $colIdx - 1;

        // Kolom Absensi (Sakit, Izin, Alfa)
        $absensiStartCol = $colIdx;
        foreach (['Sakit', 'Izin', 'Alfa'] as $absLabel) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
            $sheetData->setCellValue("{$colLetter}6", $absLabel);
            $colIdx++;
        }
        $lastColIdx = $colIdx - 1;
        $lastColLetter = Coordinate::stringFromColumnIndex($lastColIdx);

        // Sembunyikan baris metadata 4 dan 5
        $sheetData->getRowDimension(4)->setVisible(false);
        $sheetData->getRowDimension(5)->setVisible(false);

        // Isi Data Siswa mulai Baris 7
        $rowNum = 7;
        foreach ($siswaList as $no => $siswa) {
            $sheetData->setCellValue("A{$rowNum}", $no + 1);
            $sheetData->setCellValue("B{$rowNum}", $siswa->nis);
            $sheetData->setCellValue("C{$rowNum}", $siswa->nama_lengkap);
            $sheetData->setCellValue("D{$rowNum}", $siswa->kelas_asli);

            // Isi kolom absensi jika sudah ada
            $colS = Coordinate::stringFromColumnIndex($absensiStartCol);
            $colI = Coordinate::stringFromColumnIndex($absensiStartCol + 1);
            $colA = Coordinate::stringFromColumnIndex($absensiStartCol + 2);
            $sheetData->setCellValue("{$colS}{$rowNum}", $siswa->absensi?->sakit ?? 0);
            $sheetData->setCellValue("{$colI}{$rowNum}", $siswa->absensi?->izin ?? 0);
            $sheetData->setCellValue("{$colA}{$rowNum}", $siswa->absensi?->alfa ?? 0);

            $rowNum++;
        }
        $lastRow = max(7, $rowNum - 1);

        // Proteksi Sheet & Unlock Kolom Nilai (Biru) + Absensi (Kuning) (F-IN-03)
        $sheetData->getProtection()->setSheet(true);
        $sheetData->getProtection()->setPassword('darulkholidin');

        if ($mapelEndCol >= $mapelStartCol) {
            $startBlue = Coordinate::stringFromColumnIndex($mapelStartCol) . '7';
            $endBlue   = Coordinate::stringFromColumnIndex($mapelEndCol) . $lastRow;
            $sheetData->getStyle("{$startBlue}:{$endBlue}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');
            $sheetData->getStyle("{$startBlue}:{$endBlue}")->getProtection()
                ->setLocked(Protection::PROTECTION_UNPROTECTED);
        }

        $startYellow = Coordinate::stringFromColumnIndex($absensiStartCol) . '7';
        $endYellow   = "{$lastColLetter}{$lastRow}";
        $sheetData->getStyle("{$startYellow}:{$endYellow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF2CC');
        $sheetData->getStyle("{$startYellow}:{$endYellow}")->getProtection()
            ->setLocked(Protection::PROTECTION_UNPROTECTED);

        $fileName = "Template_Nilai_Tingkat_{$tingkat}.xlsx";
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $ta = $request->attributes->get('ta_aktif');
        $spreadsheet = IOFactory::load($request->file('file_excel')->getPathname());
        $sheet = $spreadsheet->getSheetByName('Data') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false); // 0-indexed columns

        $metaMapelRow = $rows[4] ?? []; // Baris 5 (index 4) menyimpan mapel_id
        $headerRow    = $rows[5] ?? []; // Baris 6 (index 5) menyimpan nama kolom

        // Petakan index kolom ke mapel_id
        $mapelCols = [];
        foreach ($metaMapelRow as $colIdx => $val) {
            if ($colIdx >= 4 && is_numeric($val)) {
                $mapelCols[$colIdx] = (int) $val;
            }
        }

        // Cari index kolom Sakit, Izin, Alfa
        $sakitCol = array_search('Sakit', $headerRow);
        $izinCol  = array_search('Izin', $headerRow);
        $alfaCol  = array_search('Alfa', $headerRow);

        $nilaiTersimpan = 0;
        $tanpaPengampuWarnings = [];

        // Baca data siswa mulai baris 7 (index 6)
        for ($i = 6; $i < count($rows); $i++) {
            $row = $rows[$i];
            $nis = trim((string) ($row[1] ?? ''));
            if ($nis === '') continue;

            $siswa = Siswa::where('tahun_ajaran_id', $ta->id)->where('nis', $nis)->first();
            if (!$siswa) continue; // Siswa tidak ditemukan dilewati (F-IN-05)

            $kelas = Kelas::where('tahun_ajaran_id', $ta->id)->where('nama_kelas', $siswa->kelas_asli)->first();
            if (!$kelas) continue;

            // 1. Simpan Nilai per Mapel
            foreach ($mapelCols as $colIdx => $mapelId) {
                $rawNilai = trim((string) ($row[$colIdx] ?? ''));
                if ($rawNilai === '' || !is_numeric($rawNilai)) continue;

                $nilaiInt = (int) $rawNilai;
                if ($nilaiInt < 2 || $nilaiInt > 9) continue; // Di luar 2-9 diabaikan (F-IN-05)

                $kelasMapel = KelasMapel::with(['mapel', 'pengampu'])
                    ->where('tahun_ajaran_id', $ta->id)
                    ->where('kelas_id', $kelas->id)
                    ->where('mapel_id', $mapelId)
                    ->first();

                if (!$kelasMapel || !$kelasMapel->pengampu) {
                    // Catat peringatan kelas-mapel tanpa pengampu (F-IN-06 & AC-4)
                    $namaMapel = $kelasMapel?->mapel?->nama_mapel ?? "Mapel ID {$mapelId}";
                    $key = "{$kelas->nama_kelas} - {$namaMapel}";
                    $tanpaPengampuWarnings[$key] = $key;
                    continue;
                }

                Nilai::updateOrCreate(
                    [
                        'siswa_id'            => $siswa->id,
                        'guru_mapel_kelas_id' => $kelasMapel->pengampu->id,
                        'tahun_ajaran_id'     => $ta->id,
                    ],
                    ['nilai_akhir' => $nilaiInt]
                );
                $nilaiTersimpan++;
            }

            // 2. Simpan Absensi jika kolom tersedia
            if ($sakitCol !== false && $izinCol !== false && $alfaCol !== false) {
                Absensi::updateOrCreate(
                    ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $ta->id],
                    [
                        'sakit' => max(0, (int) ($row[$sakitCol] ?? 0)),
                        'izin'  => max(0, (int) ($row[$izinCol] ?? 0)),
                        'alfa'  => max(0, (int) ($row[$alfaCol] ?? 0)),
                    ]
                );
            }
        }

        $redirect = back()->with('success', "Impor selesai: {$nilaiTersimpan} data nilai berhasil disimpan.");
        if (!empty($tanpaPengampuWarnings)) {
            $daftar = implode(', ', array_values($tanpaPengampuWarnings));
            $redirect->with('warning', "Perhatian: Nilai untuk kelas-mapel berikut dilewati karena belum memiliki guru pengampu: {$daftar}");
        }

        return $redirect;
    }
}