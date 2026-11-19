@extends('layouts.app')
@section('title', 'Kelengkapan Nilai Kelas Perwalian (' . $kelas->nama_kelas . ')')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Mata Pelajaran</th>
                    <th>Guru Pengampu</th>
                    <th>Nilai Masuk</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($progresMapel as $pm)
                    <tr>
                        <td><code>{{ $pm['kode_mapel'] }}</code></td>
                        <td class="fw-semibold">{{ $pm['nama_mapel'] }}</td>
                        <td>{{ $pm['nama_guru'] }}</td>
                        <td>{{ $pm['terisi'] }} / {{ $pm['total'] }} Siswa</td>
                        <td>
                            @if($pm['lengkap']) <span class="badge bg-success">Lengkap</span>
                            @elseif($pm['terisi'] > 0) <span class="badge bg-warning text-dark">Sebagian</span>
                            @else <span class="badge bg-danger">Belum Ada Nilai</span> @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada mata pelajaran di kelas ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection