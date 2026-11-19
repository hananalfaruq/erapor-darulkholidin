<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';
    protected $fillable = ['nama_sekolah', 'logo'];

    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['nama_sekolah' => 'PONPES DARUL KHOLIDIN', 'logo' => 'logo.png']
        );
    }
}