<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasMapelController extends Controller
{
    public function index(Request $request)
    {
        $ta = $request->attributes->get('ta_aktif');

        $kelasList = Kelas::with(['kelasMapel.mapel', 'kelasMapel.pengampu.guru'])
            ->where('tahun_ajaran_id', $ta->id)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.kelas_mapel.index', compact('kelasList'));
    }
}