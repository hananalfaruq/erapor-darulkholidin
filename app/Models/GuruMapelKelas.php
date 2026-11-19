<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuruMapelKelas extends Model
{
    protected $table = 'guru_mapel_kelas';
    protected $fillable = ['guru_id', 'kelas_mapel_id', 'tahun_ajaran_id'];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function kelasMapel(): BelongsTo
    {
        return $this->belongsTo(KelasMapel::class, 'kelas_mapel_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'guru_mapel_kelas_id');
    }
}