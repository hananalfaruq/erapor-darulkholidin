<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Pengaturan::firstOrCreate(
            ['id' => 1],
            ['nama_sekolah' => 'PONPES DARUL KHOLIDIN', 'logo' => 'logo.png']
        );

        TahunAjaran::firstOrCreate(
            ['nama_tahun_ajaran' => '2025/2026 - Ganjil'],
            ['is_active' => true]
        );
        TahunAjaran::firstOrCreate(
            ['nama_tahun_ajaran' => '2025/2026 - Genap'],
            ['is_active' => false]
        );

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'nama_lengkap' => 'Ustadz Ahmad (Administrator)',
                'password'     => Hash::make('admin123'),
                'role'         => 'administrator',
            ]
        );

        User::firstOrCreate(
            ['username' => 'guru1'],
            [
                'nama_lengkap' => 'Ustadzah Fatimah, S.Pd.',
                'password'     => Hash::make('guru123'),
                'role'         => 'guru',
            ]
        );
    }
}