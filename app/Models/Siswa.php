<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $fillable = [
        'nis', 'nama_lengkap', 'nama_arab', 'jenis_kelamin',
        'kelas_id', 'kelas_asli', 'kelas_dapodik', 'tahun_ajaran_id'
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'siswa_id');
    }

    public function absensi(): HasOne
    {
        return $this->hasOne(Absensi::class, 'siswa_id');
    }
}