@extends('layouts.app')
@section('title', 'Kelola Siswa')

@section('content')
<div class="row g-4 mb-4">
    <!-- Form Tambah Siswa Manual -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Tambah Siswa Manual</div>
            <div class="card-body">
                <form action="{{ route('admin.siswa.store') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-12 col-sm-4">
                        <input type="text" name="nis" class="form-control form-control-sm" placeholder="NIS" required>
                    </div>
                    <div class="col-12 col-sm-8">
                        <input type="text" name="nama_lengkap" class="form-control form-control-sm" placeholder="Nama Lengkap" required>
                    </div>
                    <div class="col-12 col-sm-5">
                        <input type="text" name="nama_arab" class="form-control form-control-sm" placeholder="Nama Arab (Opsional)">
                    </div>
                    <div class="col-4 col-sm-2">
                        <select name="jenis_kelamin" class="form-select form-select-sm" required>
                            <option value="L">L</option>
                            <option value="P">P</option>
                        </select>
                    </div>
                    <div class="col-4 col-sm-3">
                        <select name="kelas_asli" class="form-select form-select-sm" required>
                            <option value="">-- Kelas Asli --</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->nama_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 col-sm-2">
                        <input type="text" name="kelas_dapodik" class="form-control form-control-sm" placeholder="Dapodik">
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Form Impor Siswa via Template Excel -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Impor Siswa via Template Excel</span>
                <a href="{{ route('admin.siswa.template') }}" class="btn btn-sm btn-outline-primary py-0">
                    <i class="bi bi-download me-1"></i> Unduh Template
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.siswa.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file_excel" class="form-control form-control-sm mb-2" accept=".xlsx,.xls" required>
                    <div class="form-text small mb-2">
                        1. Klik <strong>Unduh Template</strong> di kanan atas.<br>
                        2. Isi data siswa sesuai kolom pada template, lalu unggah kembali di sini.
                    </div>
                    <button type="submit" class="btn btn-sm btn-success w-100">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Unggah & Impor Template Siswa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Daftar Siswa -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Daftar Siswa ({{ $siswaList->count() }} Siswa)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">NIS</th>
                        <th>Nama Lengkap</th>
                        <th>Nama Arab</th>
                        <th>JK</th>
                        <th>Kelas Asli</th>
                        <th>Kelas Dapodik</th>
                        <th class="text-end pe-3" style="min-width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswaList as $s)
                        <tr>
                            <td class="ps-3"><code>{{ $s->nis }}</code></td>
                            <td class="fw-semibold">{{ $s->nama_lengkap }}</td>
                            <td>{{ $s->nama_arab ?? '-' }}</td>
                            <td>{{ $s->jenis_kelamin }}</td>
                            <td><span class="badge bg-success">{{ $s->kelas_asli }}</span></td>
                            <td>{{ $s->kelas_dapodik ?? '-' }}</td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#editSiswa{{ $s->id }}">
                                    Edit
                                </button>
                                <form action="{{ route('admin.siswa.destroy', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus siswa {{ $s->nama_lengkap }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada data siswa pada Tahun Ajaran ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($siswaList as $s)
    <div class="modal fade" id="editSiswa{{ $s->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.siswa.update', $s->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Edit Siswa: {{ $s->nama_lengkap }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">NIS</label>
                            <input type="text" name="nis" class="form-control form-control-sm" value="{{ $s->nis }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control form-control-sm" value="{{ $s->nama_lengkap }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Nama Arab</label>
                            <input type="text" name="nama_arab" class="form-control form-control-sm" value="{{ $s->nama_arab }}">
                        </div>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label small fw-semibold">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select form-select-sm" required>
                                    <option value="L" @selected($s->jenis_kelamin === 'L')>L</option>
                                    <option value="P" @selected($s->jenis_kelamin === 'P')>P</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-semibold">Kelas Asli</label>
                                <select name="kelas_asli" class="form-select form-select-sm" required>
                                    @foreach($kelasList as $k)
                                        <option value="{{ $k->nama_kelas }}" @selected($s->kelas_asli === $k->nama_kelas)>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-semibold">Kelas Dapodik</label>
                                <input type="text" name="kelas_dapodik" class="form-control form-control-sm" value="{{ $s->kelas_dapodik }}">
                            </div>
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
@endforeach
@endsection