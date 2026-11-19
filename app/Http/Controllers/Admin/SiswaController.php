<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');

        $siswaList = Siswa::where('tahun_ajaran_id', $ta->id)
            ->orderBy('kelas_asli')
            ->orderBy('nama_lengkap')
            ->get();

        $kelasList = Kelas::where('tahun_ajaran_id', $ta->id)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.siswa.index', compact('siswaList', 'kelasList'));
    }

    public function store(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');

        $validated = $request->validate([
            'nis'           => ['required', 'string', 'max:50', Rule::unique('siswa', 'nis')->where('tahun_ajaran_id', $ta->id)],
            'nama_lengkap'  => ['required', 'string', 'max:255'],
            'nama_arab'     => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'kelas_asli'    => ['required', 'string', 'max:50'],
            'kelas_dapodik' => ['nullable', 'string', 'max:50'],
        ]);

        $kelas = Kelas::where('tahun_ajaran_id', $ta->id)
            ->where('nama_kelas', $validated['kelas_asli'])
            ->first();

        Siswa::create([
            ...$validated,
            'kelas_id'        => $kelas?->id,
            'tahun_ajaran_id' => $ta->id,
        ]);

        return back()->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Siswa $siswa)
    {
        $ta = $request->attributes->get('ta_aktif');

        $validated = $request->validate([
            'nis'           => ['required', 'string', 'max:50', Rule::unique('siswa', 'nis')->where('tahun_ajaran_id', $ta->id)->ignore($siswa->id)],
            'nama_lengkap'  => ['required', 'string', 'max:255'],
            'nama_arab'     => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'kelas_asli'    => ['required', 'string', 'max:50'],
            'kelas_dapodik' => ['nullable', 'string', 'max:50'],
        ]);

        $kelas = Kelas::where('tahun_ajaran_id', $ta->id)
            ->where('nama_kelas', $validated['kelas_asli'])
            ->first();

        $siswa->update([
            ...$validated,
            'kelas_id' => $kelas?->id,
        ]);

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return back()->with('success', 'Data siswa berhasil dihapus.');
    }

    /**
     * Unduh Template Excel Impor Siswa
     */
    public function downloadTemplate(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');
        $kelasList = Kelas::where('tahun_ajaran_id', $ta->id)->orderBy('tingkat')->orderBy('nama_kelas')->get();

        $spreadsheet = new Spreadsheet();

        // SHEET 1: DATA SISWA
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        $headers = [
            'A1' => 'NIS',
            'B1' => 'Nama Lengkap',
            'C1' => 'Nama Arab',
            'D1' => 'Kelas Asli',
            'E1' => 'Kelas Dapodik',
            'F1' => 'Jenis Kelamin (L/P)',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E1F2');

        // Contoh baris pengisian di baris 2
        $contohKelas = $kelasList->first()?->nama_kelas ?? '1A';
        $sheet->setCellValue('A2', '202601001');
        $sheet->setCellValue('B2', 'Ahmad Fauzi (Hapus/Ganti Baris Contoh Ini)');
        $sheet->setCellValue('C2', 'أحمد فوزي');
        $sheet->setCellValue('D2', $contohKelas);
        $sheet->setCellValue('E2', 'VII-A');
        $sheet->setCellValue('F2', 'L');

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // SHEET 2: PETUNJUK & DAFTAR KELAS VALID
        $sheetInfo = $spreadsheet->createSheet();
        $sheetInfo->setTitle('Petunjuk & Daftar Kelas');
        $sheetInfo->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE SISWA');
        $sheetInfo->setCellValue('A3', '1. Isi data siswa pada sheet "Data Siswa" mulai dari baris ke-2 (jangan ubah judul kolom di baris 1).');
        $sheetInfo->setCellValue('A4', '2. Kolom NIS, Nama Lengkap, Kelas Asli, dan Jenis Kelamin (L atau P) wajib diisi.');
        $sheetInfo->setCellValue('A5', '3. Pastikan penulisan "Kelas Asli" sama persis dengan daftar kelas yang tersedia di bawah ini:');

        $sheetInfo->setCellValue('A7', 'Daftar Nama Kelas Asli (TA ' . $ta->nama_tahun_ajaran . ')');
        $sheetInfo->setCellValue('B7', 'Tingkat / Jenjang');
        $sheetInfo->getStyle('A7:B7')->getFont()->setBold(true);

        $r = 8;
        foreach ($kelasList as $k) {
            $sheetInfo->setCellValue("A{$r}", $k->nama_kelas);
            $sheetInfo->setCellValue("B{$r}", $k->jenjang_label);
            $r++;
        }
        $sheetInfo->getColumnDimension('A')->setAutoSize(true);
        $sheetInfo->getColumnDimension('B')->setAutoSize(true);

        $fileName = 'Template_Import_Siswa.xlsx';
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }

    /**
     * Impor Excel Siswa dari Template Sistem
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $ta = $request->attributes->get('ta_aktif');
        $spreadsheet = IOFactory::load($request->file('file_excel')->getPathname());
        $sheet = $spreadsheet->getSheetByName('Data Siswa') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $kelasMap = Kelas::where('tahun_ajaran_id', $ta->id)->pluck('id', 'nama_kelas');
        $berhasil = 0;
        $dilewati = 0;

        foreach ($rows as $index => $row) {
            if ($index === 1) continue; // Lewati baris 1 (Header)

            $nis          = trim((string) ($row['A'] ?? ''));
            $nama         = trim((string) ($row['B'] ?? ''));
            $namaArab     = trim((string) ($row['C'] ?? ''));
            $kelasAsli    = trim((string) ($row['D'] ?? ''));
            $kelasDapodik = trim((string) ($row['E'] ?? ''));
            $jk           = strtoupper(trim((string) ($row['F'] ?? 'L')));

            if ($nis === '' || $nama === '' || $kelasAsli === '') {
                continue;
            }

            if (Siswa::where('tahun_ajaran_id', $ta->id)->where('nis', $nis)->exists()) {
                $dilewati++;
                continue;
            }

            Siswa::create([
                'nis'             => $nis,
                'nama_lengkap'    => $nama,
                'nama_arab'       => $namaArab ?: null,
                'kelas_asli'      => $kelasAsli,
                'kelas_id'        => $kelasMap[$kelasAsli] ?? null,
                'kelas_dapodik'   => $kelasDapodik ?: null,
                'jenis_kelamin'   => in_array($jk, ['L', 'P']) ? $jk : 'L',
                'tahun_ajaran_id' => $ta->id,
            ]);
            $berhasil++;
        }

        return back()->with('success', "Impor siswa selesai: {$berhasil} ditambahkan, {$dilewati} NIS duplikat dilewati.");
    }
}