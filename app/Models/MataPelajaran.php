<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';
    protected $fillable = ['kode_mapel', 'nama_mapel'];

    public function mapelJenjang(): HasMany
    {
        return $this->hasMany(MapelJenjang::class, 'mapel_id');
    }

    public function kelasMapel(): HasMany
    {
        return $this->hasMany(KelasMapel::class, 'mapel_id');
    }
}