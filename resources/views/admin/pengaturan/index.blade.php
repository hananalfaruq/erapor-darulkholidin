@extends('layouts.app')
@section('title', 'Pengaturan Sekolah')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 500px;">
    <div class="card-body">
        <form action="{{ route('admin.pengaturan.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label small fw-semibold">Nama Sekolah / Pondok Pesantren</label>
                <input type="text" name="nama_sekolah" class="form-control" value="{{ $pengaturan->nama_sekolah }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Logo Sekolah (PNG/JPG)</label>
                <div class="mb-2">
                    <img src="{{ asset($pengaturan->logo) }}" alt="Logo" height="64" class="border p-1 rounded" onerror="this.style.display='none'">
                </div>
                <input type="file" name="logo" class="form-control" accept=".png,.jpg,.jpeg">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </form>
    </div>
</div>
@endsection