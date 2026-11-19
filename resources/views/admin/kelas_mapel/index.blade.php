@extends('layouts.app')
@section('title', 'Pemetaan Kelas - Mata Pelajaran')

@section('content')
<div class="alert alert-secondary small">
    Halaman ini bersifat <strong>lihat saja (view-only)</strong>. Pemetaan kelas-mapel dibuat secara otomatis saat Anda mengatur Tingkat pada menu <strong>Mata Pelajaran</strong> atau menambah <strong>Kelas</strong>.
</div>

<div class="row g-3">
    @forelse($kelasList as $kelas)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Kelas {{ $kelas->nama_kelas }}</span>
                    <span class="badge bg-secondary">{{ $kelas->jenjang_label }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Kode</th><th>Mata Pelajaran</th><th>Guru Pengampu</th></tr>
                        </thead>
                        <tbody>
                            @forelse($kelas->kelasMapel as $km)
                                <tr>
                                    <td>{{ $km->mapel->kode_mapel }}</td>
                                    <td>{{ $km->mapel->nama_mapel }}</td>
                                    <td>{{ $km->pengampu?->guru?->nama_lengkap ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted text-center py-2">Belum ada mapel tertaut.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-warning">Belum ada kelas pada Tahun Ajaran aktif.</div></div>
    @endforelse
</div>
@endsection