<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = ['nama_kelas', 'tingkat', 'tahun_ajaran_id'];

    public function getJenjangLabelAttribute(): string
    {
        return $this->tingkat <= 3 ? "SMP (Tingkat {$this->tingkat})" : "SMA (Tingkat {$this->tingkat})";
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

    public function kelasMapel(): HasMany
    {
        return $this->hasMany(KelasMapel::class, 'kelas_id');
    }

    public function waliKelas(): HasOne
    {
        return $this->hasOne(WaliKelas::class, 'kelas_id');
    }
}