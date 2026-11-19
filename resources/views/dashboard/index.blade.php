@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@if(auth()->user()->isAdmin())
    <!-- Baris 1: Kartu Ringkasan Data Master -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">TOTAL SISWA (TA AKTIF)</span>
                        <i class="bi bi-people fs-4 text-primary"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $stats['total_siswa'] }}</h3>
                    <div class="small text-muted">
                        <span class="me-2"><i class="bi bi-gender-male text-primary"></i> L: <strong>{{ $stats['siswa_l'] }}</strong></span>
                        <span><i class="bi bi-gender-female text-danger"></i> P: <strong>{{ $stats['siswa_p'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">GURU & WALI KELAS</span>
                        <i class="bi bi-person-badge fs-4 text-success"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $stats['total_guru'] }} <span class="fs-6 fw-normal text-muted">Guru</span></h3>
                    <div class="small text-muted">
                        Wali Kelas Terisi: <strong>{{ $stats['total_wali'] }} / {{ $stats['total_kelas'] }}</strong> Kelas
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">ROMBONGAN KELAS</span>
                        <i class="bi bi-door-open fs-4 text-warning"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $stats['total_kelas'] }} <span class="fs-6 fw-normal text-muted">Kelas</span></h3>
                    <div class="small text-muted">
                        SMP (1–3): <strong>{{ $stats['kelas_smp'] }}</strong> | SMA (4–6): <strong>{{ $stats['kelas_sma'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">MAPEL & PENGAMPU</span>
                        <i class="bi bi-journal-bookmark fs-4 text-info"></i>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $stats['total_mapel'] }} <span class="fs-6 fw-normal text-muted">Mapel</span></h3>
                    <div class="small text-muted">
                        Pengampu Terisi: <strong>{{ $stats['total_pengampu'] }} / {{ $stats['total_kelas_mapel'] }}</strong> Kelas-Mapel
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Peringatan Otomatis Jika Ada Kelas-Mapel Tanpa Pengampu -->
    @if($stats['belum_ada_pengampu'] > 0)
        <div class="alert alert-warning d-flex justify-content-between align-items-center shadow-sm">
            <div>
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Terdapat <strong>{{ $stats['belum_ada_pengampu'] }} Kelas-Mapel</strong> yang belum memiliki Guru Pengampu pada Tahun Ajaran ini.
            </div>
            <a href="{{ route('admin.atur-pengampu.index') }}" class="btn btn-sm btn-warning fw-semibold">Atur Pengampu Sekarang</a>
        </div>
    @endif

    <!-- Baris 2: Progres Penilaian Pondok & Status Guru -->
    <div class="row g-4 mb-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    <span>Progres Kelengkapan Penilaian Seluruh Kelas</span>
                    <span class="badge bg-primary">{{ $stats['persen_lengkap'] }}% Selesai</span>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 22px;">
                        <div class="progress-bar bg-success fw-semibold" role="progressbar" style="width: {{ $stats['persen_lengkap'] }}%;">
                            {{ $stats['persen_lengkap'] }}%
                        </div>
                    </div>
                    <div class="row text-center g-2">
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <div class="small text-muted">Kelas-Mapel Lengkap</div>
                                <div class="fs-5 fw-bold text-success">{{ $stats['km_lengkap'] }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <div class="small text-muted">Terisi Sebagian</div>
                                <div class="fs-5 fw-bold text-warning">{{ $stats['km_sebagian'] }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <div class="small text-muted">Belum Ada Nilai</div>
                                <div class="fs-5 fw-bold text-danger">{{ $stats['km_kosong'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    <span>Ringkasan Status Guru Pengampu</span>
                    <a href="{{ route('admin.cek-penilaian.index') }}" class="btn btn-sm btn-outline-primary py-0">Detail</a>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span>🟢 <strong>Selesai Semua</strong> (100% Lengkap)</span>
                        <span class="badge bg-success rounded-pill fs-6">{{ $stats['guru_hijau'] }} Guru</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span>🟡 <strong>Proses Mengisi</strong> (Sebagian)</span>
                        <span class="badge bg-warning text-dark rounded-pill fs-6">{{ $stats['guru_kuning'] }} Guru</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>🔴 <strong>Belum Mengisi</strong> (Masih Kosong)</span>
                        <span class="badge bg-danger rounded-pill fs-6">{{ $stats['guru_merah'] }} Guru</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 3: Tabel Ringkasan Kesiapan Rapor per Kelas -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Rekapitulasi Progres Penilaian & Absensi per Kelas</span>
            <a href="{{ route('admin.cetak-nilai.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-printer me-1"></i> Ke Menu Cetak Leger
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kelas</th>
                        <th>Jenjang</th>
                        <th>Wali Kelas</th>
                        <th>Jumlah Siswa</th>
                        <th>Kelengkapan Nilai Mapel</th>
                        <th>Rekap Absensi</th>
                        <th class="text-end pe-3">Cetak Leger</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekapKelas as $rk)
                        <tr>
                            <td class="ps-3 fw-bold">Kelas {{ $rk['nama_kelas'] }}</td>
                            <td><span class="badge bg-secondary">{{ $rk['jenjang'] }}</span></td>
                            <td>
                                @if($rk['wali_kelas'])
                                    {{ $rk['wali_kelas'] }}
                                @else
                                    <span class="text-danger small"><i class="bi bi-exclamation-circle"></i> Belum diatur</span>
                                @endif
                            </td>
                            <td>{{ $rk['jml_siswa'] }} Siswa</td>
                            <td>
                                @if($rk['jml_mapel'] > 0 && $rk['mapel_selesai'] >= $rk['jml_mapel'] && $rk['jml_siswa'] > 0)
                                    <span class="badge bg-success">Lengkap ({{ $rk['mapel_selesai'] }}/{{ $rk['jml_mapel'] }} Mapel)</span>
                                @elseif($rk['mapel_selesai'] > 0)
                                    <span class="badge bg-warning text-dark">{{ $rk['mapel_selesai'] }} / {{ $rk['jml_mapel'] }} Mapel Lengkap</span>
                                @else
                                    <span class="badge bg-danger">0 / {{ $rk['jml_mapel'] }} Mapel Lengkap</span>
                                @endif
                            </td>
                            <td>
                                @if($rk['jml_siswa'] > 0 && $rk['absensi_terisi'] >= $rk['jml_siswa'])
                                    <span class="badge bg-success">Lengkap ({{ $rk['absensi_terisi'] }}/{{ $rk['jml_siswa'] }})</span>
                                @elseif($rk['absensi_terisi'] > 0)
                                    <span class="badge bg-warning text-dark">{{ $rk['absensi_terisi'] }} / {{ $rk['jml_siswa'] }} Siswa</span>
                                @else
                                    <span class="badge bg-secondary">0 / {{ $rk['jml_siswa'] }} Siswa</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.cetak-nilai.export', ['tahun_ajaran_id' => $taAktif->id, 'kelas_id' => $rk['id']]) }}" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-file-earmark-excel"></i> Unduh Excel
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">Belum ada kelas terdaftar pada Tahun Ajaran aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <!-- DASHBOARD GURU & WALI KELAS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Mapel Diampu</div>
                <h3 class="fw-bold mb-0">{{ $guruStats['jumlah_mapel'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Kelas Diampu</div>
                <h3 class="fw-bold mb-0">{{ $guruStats['jumlah_kelas'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Total Kelas-Mapel</div>
                <h3 class="fw-bold mb-0">{{ $guruStats['jumlah_kelas_mapel'] }}</h3>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm"><div class="card-body">
                <div class="text-muted small">Kelas-Mapel Selesai</div>
                <h3 class="fw-bold text-success mb-0">{{ $guruStats['jumlah_lengkap'] }} / {{ $guruStats['jumlah_kelas_mapel'] }}</h3>
            </div></div>
        </div>
    </div>

    @if($infoWali)
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <strong>Status Wali Kelas:</strong> Anda adalah Wali Kelas <strong>{{ $infoWali['nama_kelas'] }}</strong>.
                Progres Rekap Absensi: <strong>{{ $infoWali['absensi_terisi'] }} / {{ $infoWali['total_siswa'] }}</strong> siswa.
            </div>
            <a href="{{ route('guru.absensi.index') }}" class="btn btn-sm btn-primary">Kelola Absensi</a>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Progres Penilaian Kelas-Mapel Anda</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Progres Nilai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($progresKelasMapel as $item)
                        <tr>
                            <td>{{ $item['nama_kelas'] }}</td>
                            <td>{{ $item['nama_mapel'] }}</td>
                            <td>{{ $item['terisi'] }} / {{ $item['total_siswa'] }} Siswa</td>
                            <td>
                                @if($item['lengkap'])
                                    <span class="badge bg-success">Lengkap</span>
                                @elseif($item['terisi'] > 0)
                                    <span class="badge bg-warning text-dark">Sebagian</span>
                                @else
                                    <span class="badge bg-danger">Belum Diisi</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('guru.nilai.detail', $item['pengampu_id']) }}" class="btn btn-sm btn-outline-primary">Input Nilai</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada penugasan kelas-mapel pada Tahun Ajaran ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection