@extends('layouts.app')
@section('title', 'Kelola Tahun Ajaran')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Tambah Tahun Ajaran Baru</div>
            <div class="card-body">
                <form action="{{ route('admin.tahun-ajaran.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">Tahun (Format YYYY/YYYY)</label>
                        <input type="text" name="tahun" class="form-control" placeholder="2026/2027" required>
                        <div class="form-text">Sistem otomatis membuat semester Ganjil & Genap.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan Tahun Ajaran</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Daftar Tahun Ajaran</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tahunAjaran as $ta)
                            <tr>
                                <td class="fw-semibold">{{ $ta->nama_tahun_ajaran }}</td>
                                <td>
                                    @if($ta->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(!$ta->is_active)
                                        <form action="{{ route('admin.tahun-ajaran.activate', $ta->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">Aktifkan</button>
                                        </form>
                                        <form action="{{ route('admin.tahun-ajaran.destroy', $ta->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus Tahun Ajaran ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection