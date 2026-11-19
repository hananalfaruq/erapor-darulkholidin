<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');

        $guruList = User::where('role', 'guru')
            ->withCount(['pengampu as jumlah_ampu' => fn($q) => $q->where('tahun_ajaran_id', $ta->id)])
            ->with(['waliKelas' => fn($q) => $q->where('tahun_ajaran_id', $ta->id)->with('kelas')])
            ->orderBy('nama_lengkap')
            ->get();

        $kelasList = Kelas::where('tahun_ajaran_id', $ta->id)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.guru.index', compact('guruList', 'kelasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'     => ['required', 'string', 'max:100', 'unique:users,username'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'password'     => ['required', 'string', 'min:4'],
        ]);

        User::create([
            'username'     => trim($validated['username']),
            'nama_lengkap' => trim($validated['nama_lengkap']),
            'password'     => Hash::make($validated['password']),
            'role'         => 'guru',
        ]);

        return back()->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function update(Request $request, User $guru)
    {
        $validated = $request->validate([
            'username'     => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($guru->id)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
        ]);

        $guru->update([
            'username'     => trim($validated['username']),
            'nama_lengkap' => trim($validated['nama_lengkap']),
        ]);

        return back()->with('success', 'Data guru berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $guru)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:4'],
        ]);

        $guru->update(['password' => Hash::make($request->password)]);
        return back()->with('success', "Password untuk {$guru->nama_lengkap} berhasil direset.");
    }

    public function setWaliKelas(Request $request, User $guru)
    {
        $ta = $request->attributes->get('ta_aktif');
        $kelasId = $request->input('kelas_id');

        if (empty($kelasId)) {
            WaliKelas::where('guru_id', $guru->id)->where('tahun_ajaran_id', $ta->id)->delete();
            return back()->with('success', "Penugasan wali kelas untuk {$guru->nama_lengkap} dihapus.");
        }

        $existing = WaliKelas::where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $ta->id)
            ->where('guru_id', '!=', $guru->id)
            ->with('guru')
            ->first();

        if ($existing) {
            return back()->with('error', "Gagal: Kelas tersebut sudah memiliki wali kelas ({$existing->guru->nama_lengkap}).");
        }

        WaliKelas::updateOrCreate(
            ['guru_id' => $guru->id, 'tahun_ajaran_id' => $ta->id],
            ['kelas_id' => $kelasId]
        );

        return back()->with('success', 'Wali kelas berhasil ditetapkan.');
    }

    public function destroy(User $guru)
    {
        $guru->delete();
        return back()->with('success', 'Guru berhasil dihapus.');
    }

    /**
     * 1. Unduh Template Excel Impor Guru (Username, Nama Guru, Password Awal)
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();

        // SHEET 1: DATA GURU
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Guru');

        $headers = [
            'A1' => 'Username',
            'B1' => 'Nama Guru',
            'C1' => 'Password Awal',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2EFDA');

        // Contoh baris pengisian pada baris 2
        $sheet->setCellValue('A2', 'ustadz_yusuf');
        $sheet->setCellValue('B2', 'Ustadz M. Yusuf, S.Pd.I (Hapus/Ganti Baris Contoh Ini)');
        $sheet->setCellValue('C2', 'guru123');

        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // SHEET 2: PETUNJUK
        $sheetInfo = $spreadsheet->createSheet();
        $sheetInfo->setTitle('Petunjuk');
        $sheetInfo->setCellValue('A1', 'PETUNJUK IMPOR DATA GURU');
        $sheetInfo->getStyle('A1')->getFont()->setBold(true);
        $sheetInfo->setCellValue('A3', '1. Isi daftar guru mulai baris ke-2 pada sheet "Data Guru" (jangan ubah judul kolom di baris 1).');
        $sheetInfo->setCellValue('A4', '2. Kolom A (Username): Wajib unik, tanpa spasi (contoh: fatimah_spd atau guru01).');
        $sheetInfo->setCellValue('A5', '3. Kolom B (Nama Guru): Nama lengkap beserta gelar.');
        $sheetInfo->setCellValue('A6', '4. Kolom C (Password Awal): Minimal 4 karakter. Jika dikosongkan, sistem otomatis mengisi password default "guru123".');
        $sheetInfo->setCellValue('A7', '5. Pengaturan Wali Kelas dilakukan secara manual melalui pilihan dropdown di halaman web.');
        $sheetInfo->getColumnDimension('A')->setAutoSize(true);

        $fileName = 'Template_Import_Guru.xlsx';
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }

    /**
     * 2. Impor Excel Guru (Username, Nama Guru, Password Awal)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $spreadsheet = IOFactory::load($request->file('file_excel')->getPathname());
        $sheet = $spreadsheet->getSheetByName('Data Guru') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $berhasil = 0;
        $dilewati = 0;

        DB::transaction(function () use ($rows, &$berhasil, &$dilewati) {
            foreach ($rows as $index => $row) {
                if ($index === 1) continue; // Baris 1 = Header

                $username = trim((string) ($row['A'] ?? ''));
                $namaGuru = trim((string) ($row['B'] ?? ''));
                $password = trim((string) ($row['C'] ?? ''));

                if ($username === '' || $namaGuru === '') {
                    continue;
                }

                // Jika password kosong di Excel, beri default "guru123"
                if ($password === '') {
                    $password = 'guru123';
                }

                // Lewati jika username sudah terdaftar
                if (User::where('username', $username)->exists()) {
                    $dilewati++;
                    continue;
                }

                User::create([
                    'username'     => $username,
                    'nama_lengkap' => $namaGuru,
                    'password'     => Hash::make($password),
                    'role'         => 'guru',
                ]);
                $berhasil++;
            }
        });

        return back()->with('success', "Impor data guru selesai: {$berhasil} guru baru ditambahkan, {$dilewati} username duplikat dilewati.");
    }

    /**
     * 3. Ekspor Data Guru ke Excel (Format sama dengan Template: Username, Nama Guru, Password Awal)
     */
    public function export()
    {
        $guruList = User::where('role', 'guru')->orderBy('nama_lengkap')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Guru');

        $headers = [
            'A1' => 'Username',
            'B1' => 'Nama Guru',
            'C1' => 'Password Awal',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2EFDA');

        $rowNum = 2;
        foreach ($guruList as $guru) {
            $sheet->setCellValue("A{$rowNum}", $guru->username);
            $sheet->setCellValue("B{$rowNum}", $guru->nama_lengkap);
            // Password di database sudah terenkripsi (hash), diisi keterangan default
            $sheet->setCellValue("C{$rowNum}", 'guru123');
            $rowNum++;
        }

        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Data_Guru_' . date('Y-m-d') . '.xlsx';
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }
}