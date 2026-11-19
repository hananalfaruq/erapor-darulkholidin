@extends('layouts.app')
@section('title', 'Kelola Mata Pelajaran')

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Tambah Mata Pelajaran</div>
            <div class="card-body">
                <form action="{{ route('admin.mapel.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kode Mapel (Unik)</label>
                        <input type="text" name="kode_mapel" class="form-control" placeholder="Contoh: FQH" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Mata Pelajaran</label>
                        <input type="text" name="nama_mapel" class="form-control" placeholder="Contoh: Fiqih" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold d-block">Terapkan pada Tingkat (TA Aktif)</label>
                        <div class="row g-2">
                            @for($i = 1; $i <= 6; $i++)
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="tingkat[]" value="{{ $i }}" id="addT{{ $i }}">
                                        <label class="form-check-label small" for="addT{{ $i }}">Tingkat {{ $i }} ({{ $i <= 3 ? 'SMP' : 'SMA' }})</label>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan Mapel</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Daftar Mata Pelajaran</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Kode</th>
                                <th>Nama Mata Pelajaran</th>
                                <th>Tingkat Aktif</th>
                                <th class="text-end pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mapelList as $m)
                                @php $aktifTingkat = $m->mapelJenjang->pluck('tingkat')->toArray(); @endphp
                                <tr>
                                    <td class="ps-3 fw-bold">{{ $m->kode_mapel }}</td>
                                    <td>{{ $m->nama_mapel }}</td>
                                    <td>
                                        @forelse($aktifTingkat as $t)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Tingkat {{ $t }}</span>
                                        @empty
                                            <span class="text-muted small">Belum diterapkan</span>
                                        @endforelse
                                    </td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMapel{{ $m->id }}">Edit</button>
                                        <form action="{{ route('admin.mapel.destroy', $m->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus mapel beserta seluruh relasi dan nilainya?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada mata pelajaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($mapelList as $m)
    @php $aktifTingkat = $m->mapelJenjang->pluck('tingkat')->toArray(); @endphp
    <div class="modal fade" id="editMapel{{ $m->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.mapel.update', $m->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">Edit Mata Pelajaran: {{ $m->nama_mapel }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kode Mapel</label>
                            <input type="text" name="kode_mapel" class="form-control" value="{{ $m->kode_mapel }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Mapel</label>
                            <input type="text" name="nama_mapel" class="form-control" value="{{ $m->nama_mapel }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold d-block">Tingkat (TA Aktif)</label>
                            <div class="row g-2">
                                @for($i = 1; $i <= 6; $i++)
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="tingkat[]" value="{{ $i }}" id="edT{{ $m->id }}_{{ $i }}" @checked(in_array($i, $aktifTingkat))>
                                            <label class="form-check-label small" for="edT{{ $m->id }}_{{ $i }}">Tingkat {{ $i }} ({{ $i <= 3 ? 'SMP' : 'SMA' }})</label>
                                        </div>
                                    </div>
                                @endfor
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