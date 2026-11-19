@extends('layouts.app')
@section('title', 'Atur Guru Pengampu')

@section('content')
<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Tetapkan Pengampu (Bisa Pilih Banyak)</div>
            <div class="card-body">
                <form action="{{ route('admin.atur-pengampu.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Guru</label>
                        <select name="guru_id" class="form-select" required>
                            <option value="">-- Pilih Guru --</option>
                            @foreach($guruList as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Kelas - Mapel (Yang Belum Punya Pengampu)</label>
                        <div class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                            @forelse($availableKelasMapel as $km)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="kelas_mapel_ids[]" value="{{ $km->id }}" id="km{{ $km->id }}">
                                    <label class="form-check-label small" for="km{{ $km->id }}">
                                        <strong>Kelas {{ $km->kelas->nama_kelas }}</strong> — {{ $km->mapel->nama_mapel }}
                                    </label>
                                </div>
                            @empty
                                <div class="text-muted small">Semua kelas-mapel sudah memiliki pengampu.</div>
                            @endforelse
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan Penugasan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Daftar Guru Pengampu (TA Aktif)</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Guru</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengampuList as $p)
                            <tr>
                                <td class="fw-semibold">{{ $p->guru->nama_lengkap }}</td>
                                <td><span class="badge bg-secondary">{{ $p->kelasMapel->kelas->nama_kelas }}</span></td>
                                <td>{{ $p->kelasMapel->mapel->nama_mapel }}</td>
                                <td class="text-end">
                                    <form action="{{ route('admin.atur-pengampu.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus penugasan pengampu ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada penugasan pengampu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection