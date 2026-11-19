@extends('layouts.app')
@section('title', 'Kelola Guru & Wali Kelas')

@section('content')
<div class="row g-4 mb-4">
    <!-- Kolom Kiri: Tambah Akun Guru Manual -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Tambah Akun Guru Manual</div>
            <div class="card-body">
                <form action="{{ route('admin.guru.store') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control form-control-sm" placeholder="Contoh: ustadz_ali" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-semibold">Password Awal</label>
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="Minimal 4 karakter" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Nama Lengkap & Gelar</label>
                        <input type="text" name="nama_lengkap" class="form-control form-control-sm" placeholder="Contoh: Ustadz Ali, S.Pd.I" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-person-plus me-1"></i> Simpan Guru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Template, Impor, & Ekspor Excel Guru -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="fw-semibold">Impor & Ekspor Guru via Excel</span>
                <div class="d-flex gap-1">
                    <a href="{{ route('admin.guru.template') }}" class="btn btn-sm btn-outline-primary py-0">
                        <i class="bi bi-download me-1"></i> Unduh Template
                    </a>
                    <a href="{{ route('admin.guru.export') }}" class="btn btn-sm btn-outline-success py-0">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor Guru
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.guru.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Pilih File Template Excel (.xlsx)</label>
                        <input type="file" name="file_excel" class="form-control form-control-sm" accept=".xlsx,.xls" required>
                    </div>
                    <div class="form-text small mb-3">
                        Kolom Excel: <strong>A</strong> = Username, <strong>B</strong> = Nama Guru, <strong>C</strong> = Password Awal. (Pengaturan Wali Kelas tetap diatur manual pada tabel di bawah).
                    </div>
                    <button type="submit" class="btn btn-sm btn-success w-100">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Unggah & Impor Data Guru
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Daftar Guru & Wali Kelas -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        Daftar Guru & Pengaturan Wali Kelas ({{ $guruList->count() }} Guru)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="50">No</th>
                        <th>Nama Guru</th>
                        <th>Username</th>
                        <th>Bebas/Ampu Mapel</th>
                        <th style="min-width: 180px;">Wali Kelas (TA Aktif)</th>
                        <th class="text-end pe-3" style="min-width: 200px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guruList as $idx => $g)
                        @php $wali = $g->waliKelas->first(); @endphp
                        <tr>
                            <td class="ps-3">{{ $idx + 1 }}</td>
                            <td class="fw-semibold">{{ $g->nama_lengkap }}</td>
                            <td><code>{{ $g->username }}</code></td>
                            <td>
                                <span class="badge bg-secondary">{{ $g->jumlah_ampu }} Kelas-Mapel</span>
                            </td>
                            <td>
                                <form action="{{ route('admin.guru.wali-kelas', $g->id) }}" method="POST">
                                    @csrf
                                    <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">-- Bukan Wali --</option>
                                        @foreach($kelasList as $k)
                                            <option value="{{ $k->id }}" @selected($wali && $wali->kelas_id == $k->id)>
                                                Kelas {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editGuru{{ $g->id }}">
                                    Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetPass{{ $g->id }}">
                                    Reset Pass
                                </button>
                                <form action="{{ route('admin.guru.destroy', $g->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus guru {{ $g->nama_lengkap }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada data guru terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Kumpulan Modal Edit & Reset Password (Di Luar Tabel) -->
@foreach($guruList as $g)
    <div class="modal fade" id="editGuru{{ $g->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.guru.update', $g->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Edit Guru: {{ $g->nama_lengkap }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control" value="{{ $g->username }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Lengkap & Gelar</label>
                            <input type="text" name="nama_lengkap" class="form-control" value="{{ $g->nama_lengkap }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resetPass{{ $g->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.guru.reset-password', $g->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Reset Password: {{ $g->nama_lengkap }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label small fw-semibold">Masukkan Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 4 karakter" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-warning fw-semibold">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection