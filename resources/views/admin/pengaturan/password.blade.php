@extends('layouts.app')
@section('title', 'Ganti Password Administrator')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 480px;">
    <div class="card-header bg-white fw-semibold">Form Perubahan Password</div>
    <div class="card-body">
        <form action="{{ route('admin.password.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label small fw-semibold">Username</label>
                <input type="text" class="form-control bg-light" value="{{ auth()->user()->username }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Password Lama</label>
                <input type="password" name="current_password" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Password Baru</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-check2-circle me-1"></i> Simpan Password Baru
            </button>
        </form>
    </div>
</div>
@endsection