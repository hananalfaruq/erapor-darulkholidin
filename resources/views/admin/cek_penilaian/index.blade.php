@extends('layouts.app')
@section('title', 'Monitoring Status Penilaian Guru')

@section('content')
<div class="accordion" id="accMonitoring">
    @forelse($monitoringGuru as $idx => $item)
        <div class="accordion-item border-0 shadow-sm mb-2">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#guruCollapse{{ $idx }}">
                    <span class="me-2">
                        @if($item['status'] === 'hijau') 🟢
                        @elseif($item['status'] === 'kuning') 🟡
                        @else 🔴 @endif
                    </span>
                    <strong class="me-2">{{ $item['guru']->nama_lengkap }}</strong>
                    <span class="badge bg-secondary ms-auto me-3">{{ $item['jml_lengkap'] }} / {{ $item['total_ampu'] }} Kelas-Mapel Lengkap</span>
                </button>
            </h2>
            <div id="guruCollapse{{ $idx }}" class="accordion-collapse collapse" data-bs-parent="#accMonitoring">
                <div class="accordion-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Kelas</th><th>Mata Pelajaran</th><th>Nilai Terisi</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($item['rincian'] as $r)
                                <tr>
                                    <td>Kelas {{ $r['nama_kelas'] }}</td>
                                    <td>{{ $r['nama_mapel'] }}</td>
                                    <td>{{ $r['terisi'] }} / {{ $r['total_siswa'] }} Siswa</td>
                                    <td>
                                        @if($r['lengkap']) <span class="badge bg-success">Lengkap</span>
                                        @elseif($r['terisi'] > 0) <span class="badge bg-warning text-dark">Sebagian</span>
                                        @else <span class="badge bg-danger">Kosong</span> @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-warning">Belum ada guru pengampu yang ditugaskan pada Tahun Ajaran aktif.</div>
    @endforelse
</div>
@endsection