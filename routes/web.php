<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\KelasMapelController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\AturPengampuController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\ImportNilaiController;
use App\Http\Controllers\Admin\CetakNilaiController;
use App\Http\Controllers\Admin\CekPenilaianController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Guru\NilaiController;
use App\Http\Controllers\Guru\StatusPenilaianController;
use App\Http\Controllers\Guru\AbsensiController;
use App\Http\Controllers\Guru\CekPenilaianKelasController;
use Illuminate\Support\Facades\Route;

// Guest / Auth Routes
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (Wajib Login & Memiliki Tahun Ajaran Aktif)
Route::middleware(['auth', 'academic'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================
    // MODUL ADMINISTRATOR
    // ==========================================
    Route::middleware('role:administrator')->prefix('admin')->name('admin.')->group(function () {
        // 1. Tahun Ajaran
        Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun-ajaran.index');
        Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store'])->name('tahun-ajaran.store');
        Route::post('/tahun-ajaran/{tahunAjaran}/activate', [TahunAjaranController::class, 'activate'])->name('tahun-ajaran.activate');
        Route::delete('/tahun-ajaran/{tahunAjaran}', [TahunAjaranController::class, 'destroy'])->name('tahun-ajaran.destroy');

        // 2. Kelas
        Route::get('/kelas', [KelasController::class, 'index'])->name('kelas.index');
        Route::post('/kelas', [KelasController::class, 'store'])->name('kelas.store');
        Route::put('/kelas/{kelas}', [KelasController::class, 'update'])->name('kelas.update');
        Route::delete('/kelas/{kelas}', [KelasController::class, 'destroy'])->name('kelas.destroy');

        // 3. Mata Pelajaran & Kelas-Mapel (View Only)
        Route::get('/mapel', [MapelController::class, 'index'])->name('mapel.index');
        Route::post('/mapel', [MapelController::class, 'store'])->name('mapel.store');
        Route::put('/mapel/{mapel}', [MapelController::class, 'update'])->name('mapel.update');
        Route::delete('/mapel/{mapel}', [MapelController::class, 'destroy'])->name('mapel.destroy');
        Route::get('/kelas-mapel', [KelasMapelController::class, 'index'])->name('kelas-mapel.index');

        // 4. Guru & Wali Kelas (Dilengkapi Template, Impor, & Ekspor Excel)
        Route::get('/guru', [GuruController::class, 'index'])->name('guru.index');
        Route::get('/guru/template', [GuruController::class, 'downloadTemplate'])->name('guru.template');
        Route::get('/guru/export', [GuruController::class, 'export'])->name('guru.export');
        Route::post('/guru/import', [GuruController::class, 'import'])->name('guru.import');
        Route::post('/guru', [GuruController::class, 'store'])->name('guru.store');
        Route::put('/guru/{guru}', [GuruController::class, 'update'])->name('guru.update');
        Route::post('/guru/{guru}/reset-password', [GuruController::class, 'resetPassword'])->name('guru.reset-password');
        Route::post('/guru/{guru}/wali-kelas', [GuruController::class, 'setWaliKelas'])->name('guru.wali-kelas');
        Route::delete('/guru/{guru}', [GuruController::class, 'destroy'])->name('guru.destroy');

        // 5. Atur Pengampu
        Route::get('/atur-pengampu', [AturPengampuController::class, 'index'])->name('atur-pengampu.index');
        Route::post('/atur-pengampu', [AturPengampuController::class, 'store'])->name('atur-pengampu.store');
        Route::delete('/atur-pengampu/{pengampu}', [AturPengampuController::class, 'destroy'])->name('atur-pengampu.destroy');

        // 6. Siswa & Impor Siswa (Dengan Unduh Template)
        Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
        Route::get('/siswa/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
        Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
        Route::put('/siswa/{siswa}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
        Route::post('/siswa/import', [SiswaController::class, 'import'])->name('siswa.import');

        // 7. Import Nilai Massal per Tingkat
        Route::get('/import-nilai', [ImportNilaiController::class, 'index'])->name('import-nilai.index');
        Route::get('/import-nilai/template/{tingkat}', [ImportNilaiController::class, 'downloadTemplate'])->name('import-nilai.template');
        Route::post('/import-nilai', [ImportNilaiController::class, 'import'])->name('import-nilai.import');

        // 8. Cetak Nilai (Ekspor Leger Excel)
        Route::get('/cetak-nilai', [CetakNilaiController::class, 'index'])->name('cetak-nilai.index');
        Route::get('/cetak-nilai/export', [CetakNilaiController::class, 'export'])->name('cetak-nilai.export');

        // 9. Cek Penilaian (Monitoring Semua Guru)
        Route::get('/cek-penilaian', [CekPenilaianController::class, 'index'])->name('cek-penilaian.index');

        // 10. Pengaturan Sekolah & Ganti Password Admin
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('/pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::get('/ganti-password', [PengaturanController::class, 'editPassword'])->name('password.edit');
        Route::put('/ganti-password', [PengaturanController::class, 'updatePassword'])->name('password.update');
    });

    // ==========================================
    // MODUL GURU & WALI KELAS
    // ==========================================
    Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {
        // Input Nilai & Unduh/Impor Template Nilai Kelas
        Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
        Route::get('/nilai/{pengampu}', [NilaiController::class, 'detail'])->name('nilai.detail');
        Route::get('/nilai/{pengampu}/template', [NilaiController::class, 'downloadTemplate'])->name('nilai.template');
        Route::post('/nilai/{pengampu}', [NilaiController::class, 'save'])->name('nilai.save');
        Route::post('/nilai/{pengampu}/import', [NilaiController::class, 'importUjian'])->name('nilai.import');

        // Status Penilaian Guru
        Route::get('/status-penilaian', [StatusPenilaianController::class, 'index'])->name('status-penilaian.index');

        // Khusus Wali Kelas Aktif
        Route::middleware('wali_kelas')->group(function () {
            Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
            Route::post('/absensi', [AbsensiController::class, 'save'])->name('absensi.save');
            Route::get('/cek-penilaian-kelas', [CekPenilaianKelasController::class, 'index'])->name('cek-penilaian-kelas.index');
        });
    });
});