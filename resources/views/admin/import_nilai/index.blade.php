@extends('layouts.app')
@section('title', 'Import Nilai & Absensi Massal per Tingkat')

@section('content')
<div class="row g-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">1. Unduh Template Excel per Tingkat</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tingkat</th>
                            <th>Jenjang</th>
                            <th>Jumlah Kelas</th>
                            <th>Jumlah Siswa</th>
                            <th>Jumlah Mapel</th>
                            <th class="text-end">Template</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ringkasanTingkat as $r)
                            <tr>
                                <td class="fw-bold">Tingkat {{ $r['tingkat'] }}</td>
                                <td><span class="badge bg-secondary">{{ $r['jenjang'] }}</span></td>
                                <td>{{ $r['jumlah_kelas'] }} Kelas</td>
                                <td>{{ $r['jumlah_siswa'] }} Siswa</td>
                                <td>{{ $r['jumlah_mapel'] }} Mapel</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.import-nilai.template', $r['tingkat']) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i> Unduh Template
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">2. Unggah Template yang Telah Diisi</div>
            <div class="card-body">
                <form action="{{ route('admin.import-nilai.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">File Excel Template (.xlsx)</label>
                        <input type="file" name="file_excel" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Impor Nilai & Absensi</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection