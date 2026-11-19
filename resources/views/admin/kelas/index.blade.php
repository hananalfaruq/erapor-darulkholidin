@extends('layouts.app')
@section('title', 'Kelola Kelas')

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Tambah Kelas</div>
            <div class="card-body">
                <form action="{{ route('admin.kelas.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kelas</label>
                        <input type="text" name="nama_kelas" class="form-control" placeholder="Contoh: 1A / 4B" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tingkat / Jenjang</label>
                        <select name="tingkat" class="form-select" required>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">Tingkat {{ $i }} ({{ $i <= 3 ? 'SMP' : 'SMA' }})</option>
                            @endfor
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Tambah Kelas</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Daftar Kelas (TA Aktif)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Siswa</th>
                                <th>Mapel</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelasList as $k)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $k->nama_kelas }}</td>
                                    <td>{{ $k->jenjang_label }}</td>
                                    <td>{{ $k->jumlah_siswa }} Siswa</td>
                                    <td>{{ $k->kelas_mapel_count }} Mapel</td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editKelas{{ $k->id }}">Edit</button>
                                        <form action="{{ route('admin.kelas.destroy', $k->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kelas {{ $k->nama_kelas }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data kelas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($kelasList as $k)
    <div class="modal fade" id="editKelas{{ $k->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.kelas.update', $k->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Edit Kelas: {{ $k->nama_kelas }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Kelas</label>
                            <input type="text" name="nama_kelas" class="form-control" value="{{ $k->nama_kelas }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Tingkat</label>
                            <select name="tingkat" class="form-select" required>
                                @for($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" @selected($k->tingkat == $i)>Tingkat {{ $i }} ({{ $i <= 3 ? 'SMP' : 'SMA' }})</option>
                                @endfor
                            </select>
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