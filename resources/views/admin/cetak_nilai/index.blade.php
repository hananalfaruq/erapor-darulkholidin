@extends('layouts.app')
@section('title', 'Cetak Leger Nilai Kelas')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 550px;">
    <div class="card-header bg-white fw-semibold">Pilih Tahun Ajaran & Kelas</div>
    <div class="card-body">
        <form action="{{ route('admin.cetak-nilai.index') }}" method="GET" class="mb-3">
            <label class="form-label small fw-semibold">Tahun Ajaran (Bisa Pilih Arsip TA Lama)</label>
            <select name="tahun_ajaran_id" class="form-select" onchange="this.form.submit()">
                @foreach($tahunAjaranList as $ta)
                    <option value="{{ $ta->id }}" @selected($selectedTaId == $ta->id)>
                        {{ $ta->nama_tahun_ajaran }} {{ $ta->is_active ? '(Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>

        <form action="{{ route('admin.cetak-nilai.export') }}" method="GET">
            <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedTaId }}">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Pilih Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}">Kelas {{ $k->nama_kelas }} ({{ $k->jenjang_label }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Leger Nilai (Excel)
            </button>
        </form>
    </div>
</div>
@endsection