<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KelasMapel extends Model
{
    protected $table = 'kelas_mapel';
    protected $fillable = ['kelas_id', 'mapel_id', 'tahun_ajaran_id'];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    public function pengampu(): HasOne
    {
        return $this->hasOne(GuruMapelKelas::class, 'kelas_mapel_id');
    }
}