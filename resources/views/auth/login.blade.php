<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ $pengaturan->nama_sekolah }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
            min-height: 100vh;
        }
        .btn-emerald {
            background-color: #059669;
            border-color: #059669;
            color: #fff;
        }
        .btn-emerald:hover {
            background-color: #047857;
            border-color: #047857;
            color: #fff;
        }
        .form-control:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25);
        }
        .logo-login {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: transparent !important;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
<div class="card shadow-lg border-0 rounded-4" style="width: 100%; max-width: 400px;">
    <div class="card-body p-4 p-sm-5">
        <div class="text-center mb-4">
            <img src="{{ asset($pengaturan->logo) }}" alt="Logo" class="logo-login mb-2" onerror="this.style.display='none'">
            <h5 class="fw-bold mb-1 text-dark">{{ $pengaturan->nama_sekolah }}</h5>
            <p class="text-muted small mb-0">Sistem Informasi Penilaian & Absensi Siswa</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger small py-2">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Username</label>
                <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-emerald w-100 fw-semibold py-2">Masuk Aplikasi</button>
        </form>
    </div>
</div>
</body>
</html>