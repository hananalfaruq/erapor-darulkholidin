<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    protected $fillable = ['username', 'password', 'nama_lengkap', 'role'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'administrator';
    }

    public function pengampu(): HasMany
    {
        return $this->hasMany(GuruMapelKelas::class, 'guru_id');
    }

    public function waliKelas(): HasMany
    {
        return $this->hasMany(WaliKelas::class, 'guru_id');
    }

    public function waliKelasAktif(?int $taId = null): ?WaliKelas
    {
        $taId = $taId ?? TahunAjaran::activeId();
        if (!$taId) return null;

        return $this->waliKelas()->with('kelas')->where('tahun_ajaran_id', $taId)->first();
    }
}