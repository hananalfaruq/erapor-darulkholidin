<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapelJenjang extends Model
{
    protected $table = 'mapel_jenjang';
    protected $fillable = ['mapel_id', 'tingkat', 'tahun_ajaran_id'];

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }
}