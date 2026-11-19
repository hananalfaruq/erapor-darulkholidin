@extends('layouts.app')
@section('title', 'Input Nilai: ' . $pengampu->kelasMapel->mapel->nama_mapel . ' — Kelas ' . $pengampu->kelasMapel->kelas->nama_kelas)

@section('content')
<div class="row g-4">
    <div class="col-md-8">
        <!-- Notifikasi Pemulihan Draft jika sebelumnya gagal tersimpan karena sinyal putus -->
        <div id="draftAlert" class="alert alert-warning d-none justify-content-between align-items-center shadow-sm">
            <div>
                <i class="bi bi-clock-history me-1"></i>
                <strong>Draft Ditemukan:</strong> Ada ketikan nilai Anda yang belum sempat tersimpan ke server.
            </div>
            <div class="d-flex gap-2">
                <button type="button" id="btnRestoreDraft" class="btn btn-sm btn-warning fw-semibold">Pulihkan Ketikan</button>
                <button type="button" id="btnClearDraft" class="btn btn-sm btn-outline-secondary">Abaikan</button>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Daftar Siswa Kelas {{ $pengampu->kelasMapel->kelas->nama_kelas }}</span>
                <span class="badge bg-info text-dark">Rentang Nilai Bulat: 2 s/d 9 (Kosongkan untuk menghapus)</span>
            </div>
            <div class="card-body">
                <form id="formNilai" action="{{ route('guru.nilai.save', $pengampu->id) }}" method="POST">
                    @csrf
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50">No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th width="140">Nilai Akhir (2–9)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswaList as $idx => $s)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td><code>{{ $s->nis }}</code></td>
                                    <td>{{ $s->nama_lengkap }}</td>
                                    <td>
                                        <input type="number"
                                               name="nilai[{{ $s->id }}]"
                                               data-siswa-id="{{ $s->id }}"
                                               class="form-control form-control-sm text-center fw-bold input-nilai"
                                               min="2" max="9"
                                               value="{{ $nilaiMap[$s->id] ?? '' }}"
                                               placeholder="-">
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Belum ada siswa di kelas ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" id="btnSubmitNilai" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Semua Nilai
                        </button>
                        <a href="{{ route('guru.nilai.index') }}" class="btn btn-light">Kembali</a>
                        <span id="draftStatus" class="ms-auto small text-muted"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel Unduh & Impor Template Excel Kelas-Mapel -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Impor Nilai via Template Excel</div>
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <p class="small text-muted mb-2">
                        Unduh template Excel khusus untuk kelas dan mata pelajaran ini (sudah berisi daftar nama dan NIS siswa Kelas <strong>{{ $pengampu->kelasMapel->kelas->nama_kelas }}</strong>).
                    </p>
                    <a href="{{ route('guru.nilai.template', $pengampu->id) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-download me-1"></i> 1. Unduh Template Kelas Ini
                    </a>
                </div>

                <form id="formImportExcel" action="{{ route('guru.nilai.import', $pengampu->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">2. Unggah Template yang Telah Diisi</label>
                        <input type="file" name="file_excel" class="form-control form-control-sm" accept=".xlsx,.xls" required>
                        <div class="form-text small">
                            Isi kolom <strong>Nilai Akhir (2-9)</strong> berwarna biru pada template, lalu unggah kembali file tersebut di sini.
                        </div>
                    </div>
                    <button type="submit" id="btnSubmitExcel" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Unggah & Impor Nilai
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const storageKey = 'draft_nilai_pengampu_{{ $pengampu->id }}';
    const inputs = document.querySelectorAll('.input-nilai');
    const draftAlert = document.getElementById('draftAlert');
    const draftStatus = document.getElementById('draftStatus');

    // Jika baru saja berhasil simpan ke server, bersihkan draft lama
    @if(session('success'))
        localStorage.removeItem(storageKey);
    @endif

    // Cek apakah ada draft tersimpan di browser
    const savedDraft = localStorage.getItem(storageKey);
    if (savedDraft) {
        draftAlert.classList.remove('d-none');
        draftAlert.classList.add('d-flex');
    }

    // Simpan otomatis ke localStorage setiap kali guru mengetik nilai
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            const currentData = {};
            inputs.forEach(el => {
                currentData[el.dataset.siswaId] = el.value;
            });
            localStorage.setItem(storageKey, JSON.stringify(currentData));
            draftStatus.textContent = 'Draft ketikan dicadangkan otomatis di browser';
        });
    });

    // Tombol Pulihkan Draft
    document.getElementById('btnRestoreDraft')?.addEventListener('click', function () {
        const data = JSON.parse(localStorage.getItem(storageKey) || '{}');
        inputs.forEach(el => {
            if (data[el.dataset.siswaId] !== undefined) {
                el.value = data[el.dataset.siswaId];
            }
        });
        draftAlert.classList.add('d-none');
        draftStatus.textContent = 'Ketikan berhasil dipulihkan! Silakan klik Simpan Semua Nilai.';
    });

    // Tombol Abaikan Draft
    document.getElementById('btnClearDraft')?.addEventListener('click', function () {
        localStorage.removeItem(storageKey);
        draftAlert.classList.add('d-none');
    });

    // Anti Double-Submit saat koneksi lambat
    document.getElementById('formNilai')?.addEventListener('submit', function () {
        const btn = document.getElementById('btnSubmitNilai');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan... Mohon tunggu';
    });

    document.getElementById('formImportExcel')?.addEventListener('submit', function () {
        const btn = document.getElementById('btnSubmitExcel');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah Excel...';
    });
});
</script>
@endsection