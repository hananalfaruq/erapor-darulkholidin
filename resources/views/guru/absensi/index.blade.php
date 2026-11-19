@extends('layouts.app')
@section('title', 'Rekap Absensi Kelas ' . $waliKelas->kelas->nama_kelas)

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('guru.absensi.save') }}" method="POST">
            @csrf
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th width="110">Sakit (S)</th>
                        <th width="110">Izin (I)</th>
                        <th width="110">Alfa (A)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswaList as $idx => $s)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><code>{{ $s->nis }}</code></td>
                            <td class="fw-semibold">{{ $s->nama_lengkap }}</td>
                            <td><input type="number" min="0" name="absensi[{{ $s->id }}][sakit]" class="form-control form-control-sm" value="{{ $s->absensi?->sakit ?? 0 }}"></td>
                            <td><input type="number" min="0" name="absensi[{{ $s->id }}][izin]" class="form-control form-control-sm" value="{{ $s->absensi?->izin ?? 0 }}"></td>
                            <td><input type="number" min="0" name="absensi[{{ $s->id }}][alfa]" class="form-control form-control-sm" value="{{ $s->absensi?->alfa ?? 0 }}"></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada siswa di kelas ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Rekap Absensi</button>
        </form>
    </div>
</div>
@endsection