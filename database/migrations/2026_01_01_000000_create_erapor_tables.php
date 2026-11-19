<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. PENGATURAN SEKOLAH
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah')->default('PONPES DARUL KHOLIDIN');
            $table->string('logo')->default('logo.png');
            $table->timestamps();
        });

        // 2. TAHUN AJARAN
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tahun_ajaran', 100)->unique(); // Contoh: "2025/2026 - Ganjil"
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        // 3. USERS (ADMIN & GURU)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('password');
            $table->string('nama_lengkap');
            $table->enum('role', ['administrator', 'guru'])->default('guru');
            $table->rememberToken();
            $table->timestamps();
        });

        // 4. KELAS (Tingkat 1-3 = SMP, 4-6 = SMA)
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas', 50);
            $table->unsignedTinyInteger('tingkat'); // 1 s/d 6
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['nama_kelas', 'tahun_ajaran_id']);
        });

        // 5. MATA PELAJARAN (Master Global)
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel');
            $table->timestamps();
        });

        // 6. MAPEL JENJANG (Penerapan Mapel ke Tingkat per TA)
        Schema::create('mapel_jenjang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mapel_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('tingkat');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['mapel_id', 'tingkat', 'tahun_ajaran_id']);
        });

        // 7. KELAS MAPEL (Auto-generated dari Kelas & Mapel Jenjang)
        Schema::create('kelas_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kelas_id', 'mapel_id', 'tahun_ajaran_id']);
        });

        // 8. GURU MAPEL KELAS (Pengampu: 1 Kelas-Mapel maksimal 1 Guru)
        Schema::create('guru_mapel_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kelas_mapel_id')->constrained('kelas_mapel')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kelas_mapel_id', 'tahun_ajaran_id']);
        });

        // 9. WALI KELAS (1 Guru maksimal 1 Kelas, 1 Kelas maksimal 1 Wali per TA)
        Schema::create('wali_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['guru_id', 'tahun_ajaran_id']);
            $table->unique(['kelas_id', 'tahun_ajaran_id']);
        });

        // 10. SISWA (Terhubung via kelas_id & menyimpan kelas_asli)
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 50);
            $table->string('nama_lengkap');
            $table->string('nama_arab')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->string('kelas_asli', 50);
            $table->string('kelas_dapodik', 50)->nullable();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['nis', 'tahun_ajaran_id']);
        });

        // 11. NILAI (Rentang 2-9)
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('guru_mapel_kelas_id')->constrained('guru_mapel_kelas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai_akhir'); // Validasi 2 - 9
            $table->timestamps();

            $table->unique(['siswa_id', 'guru_mapel_kelas_id', 'tahun_ajaran_id'], 'nilai_unik_idx');
        });

        // 12. ABSENSI (Rekap Semester per Siswa)
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->unsignedSmallInteger('sakit')->default(0);
            $table->unsignedSmallInteger('izin')->default(0);
            $table->unsignedSmallInteger('alfa')->default(0);
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_ajaran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('nilai');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('wali_kelas');
        Schema::dropIfExists('guru_mapel_kelas');
        Schema::dropIfExists('kelas_mapel');
        Schema::dropIfExists('mapel_jenjang');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('users');
        Schema::dropIfExists('tahun_ajaran');
        Schema::dropIfExists('pengaturan');
    }
};