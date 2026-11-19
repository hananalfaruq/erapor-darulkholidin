@extends('layouts.app')
@section('title', 'Status Penilaian Saya')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Kelas</th><th>Mata Pelajaran</th><th>Progres</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($statusList as $st)
                    <tr>
                        <td class="fw-semibold">Kelas {{ $st['nama_kelas'] }}</td>
                        <td>{{ $st['nama_mapel'] }}</td>
                        <td>{{ $st['terisi'] }} / {{ $st['total_siswa'] }} Siswa</td>
                        <td>
                            @if($st['lengkap']) <span class="badge bg-success">Lengkap</span>
                            @elseif($st['terisi'] > 0) <span class="badge bg-warning text-dark">Sebagian</span>
                            @else <span class="badge bg-danger">Belum Diisi</span> @endif
                        </td>
                        <td><a href="{{ route('guru.nilai.detail', $st['pengampu_id']) }}" class="btn btn-sm btn-outline-primary">Buka</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada kelas-mapel yang diampu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection