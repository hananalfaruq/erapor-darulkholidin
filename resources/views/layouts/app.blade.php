<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-Rapor Internal') — {{ $pengaturanSekolah->nama_sekolah ?? 'Ponpes Darul Kholidin' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --bs-primary: #059669;
            --bs-primary-rgb: 5, 150, 105;
            --bs-link-color: #059669;
            --bs-link-hover-color: #047857;
        }
        body {
            background-color: #f0fdf4;
            overflow-x: hidden;
        }
        /* Override Bootstrap Primary ke Hijau Emerald */
        .btn-primary {
            background-color: #059669;
            border-color: #059669;
        }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background-color: #047857 !important;
            border-color: #047857 !important;
        }
        .btn-outline-primary {
            color: #059669;
            border-color: #059669;
        }
        .btn-outline-primary:hover, .btn-outline-primary:focus, .btn-outline-primary:active {
            background-color: #059669 !important;
            border-color: #059669 !important;
            color: #fff !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25);
        }
        .form-check-input:checked {
            background-color: #059669;
            border-color: #059669;
        }

        /* Sidebar Hijau Tua */
        .sidebar-custom {
            background: linear-gradient(180deg, #064e3b 0%, #022c22 100%);
            color: #fff;
        }
        @media (min-width: 992px) {
            .sidebar-desktop {
                height: 100vh;
                position: fixed;
                top: 0;
                left: 0;
                width: 265px;
                flex-shrink: 0;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                z-index: 1030;
            }
            .sidebar-nav-scroll {
                flex: 1;
                overflow-y: auto;
                scrollbar-width: none; /* Firefox */
                -ms-overflow-style: none; /* IE/Edge */
            }
            .sidebar-nav-scroll::-webkit-scrollbar {
                display: none; /* Chrome/Safari */
            }
            .content-wrapper {
                margin-left: 265px;
            }
        }
        .sidebar-custom .nav-link {
            color: #d1fae5;
            padding: 0.62rem 0.95rem;
            border-radius: 0.45rem;
            margin-bottom: 0.2rem;
            font-size: 0.92rem;
            transition: all 0.15s ease;
        }
        .sidebar-custom .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .sidebar-custom .nav-link.active {
            background: #10b981;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }
        .sidebar-custom .nav-section {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6ee7b7;
            padding: 0.85rem 0.95rem 0.25rem;
            font-weight: 700;
        }
        .logo-clean {
            width: 44px;
            height: 44px;
            object-fit: contain;
            background: transparent !important;
        }
        .content-wrapper {
            flex-grow: 1;
            min-width: 0;
            min-height: 100vh;
        }

        /* Kotak Scrollable Tabel & Sticky Header */
        .table-scroll-box {
            max-height: 430px;
            overflow-y: auto;
            overflow-x: auto;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
        }
        .table-scroll-box table.table {
            margin-bottom: 0 !important;
        }
        .table-scroll-box thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background-color: #ecfdf5 !important;
            color: #064e3b;
            box-shadow: inset 0 -2px 0 #a7f3d0;
            white-space: nowrap;
        }
        th.sortable-th {
            cursor: pointer;
            user-select: none;
            transition: background-color 0.15s ease;
        }
        th.sortable-th:hover {
            background-color: #d1fae5 !important;
        }
        .sort-indicator {
            font-size: 0.75rem;
            margin-left: 0.35rem;
            opacity: 0.45;
        }
        th.sortable-th.sort-active .sort-indicator {
            opacity: 1;
            color: #059669;
        }

        /* Pagination Warna Hijau */
        .pagination .page-link {
            color: #059669;
        }
        .pagination .page-item.active .page-link {
            background-color: #059669;
            border-color: #059669;
            color: #fff;
        }

        /* Sembunyikan tbody saat awal agar tidak flash seluruh data sebelum JS pagination aktif */
        table.table tbody {
            visibility: hidden;
        }
        .table-manager-container table.table tbody {
            visibility: visible;
        }
    </style>
</head>
<body>
<div class="d-flex flex-column flex-lg-row">

    <!-- Sidebar (Offcanvas di HP < lg, Sticky Penuh di Desktop >= lg) -->
    <div class="offcanvas-lg offcanvas-start sidebar-custom sidebar-desktop d-flex flex-column p-3" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom border-success border-opacity-50">
            <div class="d-flex align-items-center">
                <img src="{{ asset($pengaturanSekolah->logo ?? 'logo.png') }}" alt="Logo" class="logo-clean me-2" onerror="this.style.display='none'">
                <div>
                    <div class="fw-bold small lh-sm text-white" id="sidebarMenuLabel">{{ $pengaturanSekolah->nama_sekolah ?? 'PONPES DARUL KHOLIDIN' }}</div>
                    <span class="badge bg-success border border-light border-opacity-25 mt-1">{{ $taAktif->nama_tahun_ajaran ?? '-' }}</span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
        </div>

        <div class="sidebar-nav-scroll">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>

            @if(auth()->user()->isAdmin())
                <div class="nav-section">Data Master</div>
                <li><a href="{{ route('admin.tahun-ajaran.index') }}" class="nav-link {{ request()->routeIs('admin.tahun-ajaran.*') ? 'active' : '' }}"><i class="bi bi-calendar-range me-2"></i> Tahun Ajaran</a></li>
                <li><a href="{{ route('admin.kelas.index') }}" class="nav-link {{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}"><i class="bi bi-door-open me-2"></i> Kelola Kelas</a></li>
                <li><a href="{{ route('admin.mapel.index') }}" class="nav-link {{ request()->routeIs('admin.mapel.*') ? 'active' : '' }}"><i class="bi bi-journal-bookmark me-2"></i> Mata Pelajaran</a></li>
                <li><a href="{{ route('admin.kelas-mapel.index') }}" class="nav-link {{ request()->routeIs('admin.kelas-mapel.*') ? 'active' : '' }}"><i class="bi bi-diagram-3 me-2"></i> Kelas - Mapel</a></li>
                <li><a href="{{ route('admin.guru.index') }}" class="nav-link {{ request()->routeIs('admin.guru.*') ? 'active' : '' }}"><i class="bi bi-person-badge me-2"></i> Kelola Guru & Wali</a></li>
                <li><a href="{{ route('admin.atur-pengampu.index') }}" class="nav-link {{ request()->routeIs('admin.atur-pengampu.*') ? 'active' : '' }}"><i class="bi bi-person-check me-2"></i> Atur Pengampu</a></li>
                <li><a href="{{ route('admin.siswa.index') }}" class="nav-link {{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}"><i class="bi bi-people me-2"></i> Kelola Siswa</a></li>

                <div class="nav-section">Penilaian & Laporan</div>
                <li><a href="{{ route('admin.import-nilai.index') }}" class="nav-link {{ request()->routeIs('admin.import-nilai.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import Nilai Massal</a></li>
                <li><a href="{{ route('admin.cek-penilaian.index') }}" class="nav-link {{ request()->routeIs('admin.cek-penilaian.*') ? 'active' : '' }}"><i class="bi bi-clipboard2-check me-2"></i> Cek Penilaian</a></li>
                <li><a href="{{ route('admin.cetak-nilai.index') }}" class="nav-link {{ request()->routeIs('admin.cetak-nilai.*') ? 'active' : '' }}"><i class="bi bi-printer me-2"></i> Cetak Nilai (Leger)</a></li>

                <div class="nav-section">Sistem</div>
                <li><a href="{{ route('admin.pengaturan.index') }}" class="nav-link {{ request()->routeIs('admin.pengaturan.*') ? 'active' : '' }}"><i class="bi bi-gear me-2"></i> Pengaturan Sekolah</a></li>
                <li><a href="{{ route('admin.password.edit') }}" class="nav-link {{ request()->routeIs('admin.password.*') ? 'active' : '' }}"><i class="bi bi-key me-2"></i> Ganti Password</a></li>
            @else
                <div class="nav-section">Menu Guru</div>
                <li><a href="{{ route('guru.nilai.index') }}" class="nav-link {{ request()->routeIs('guru.nilai.*') ? 'active' : '' }}"><i class="bi bi-pencil-square me-2"></i> Input Nilai</a></li>
                <li><a href="{{ route('guru.status-penilaian.index') }}" class="nav-link {{ request()->routeIs('guru.status-penilaian.*') ? 'active' : '' }}"><i class="bi bi-bar-chart-steps me-2"></i> Status Penilaian</a></li>

                @if(!empty($waliKelasAktif))
                    <div class="nav-section">Wali Kelas ({{ $waliKelasAktif->kelas->nama_kelas }})</div>
                    <li><a href="{{ route('guru.absensi.index') }}" class="nav-link {{ request()->routeIs('guru.absensi.*') ? 'active' : '' }}"><i class="bi bi-calendar-check me-2"></i> Input Absensi</a></li>
                    <li><a href="{{ route('guru.cek-penilaian-kelas.index') }}" class="nav-link {{ request()->routeIs('guru.cek-penilaian-kelas.*') ? 'active' : '' }}"><i class="bi bi-list-check me-2"></i> Cek Penilaian Kelas</a></li>
                @endif
            @endif
        </ul>
        </div>

        <div class="pt-3 border-top border-success border-opacity-50 mt-auto flex-shrink-0">
            <div class="small text-light mb-2 text-truncate"><i class="bi bi-person-circle me-1"></i> {{ auth()->user()->nama_lengkap }}</div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger w-100"><i class="bi bi-box-arrow-right me-1"></i> Keluar</button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper d-flex flex-column">
        <header class="bg-white shadow-sm py-3 px-3 px-lg-4 d-flex justify-content-between align-items-center sticky-top">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-primary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h5 class="mb-0 fw-semibold text-dark fs-6 fs-md-5">@yield('title')</h5>
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                TA: {{ $taAktif->nama_tahun_ajaran ?? '-' }}
            </span>
        </header>

        <main class="p-3 p-lg-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm">{{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Pastikan seluruh modal berada langsung di bawah <body> agar tidak tertutup backdrop
    document.querySelectorAll('.modal').forEach(function (modal) {
        document.body.appendChild(modal);
    });

    // Helper mengambil nilai teks/input dari sel tabel untuk keperluan Sorting & Pencarian
    function getCellValue(row, index) {
        const cell = row.children[index];
        if (!cell) return '';
        const select = cell.querySelector('select');
        if (select) {
            return select.options[select.selectedIndex]?.text.trim() || '';
        }
        const input = cell.querySelector('input[type="number"], input[type="text"]');
        if (input) {
            return input.value.trim();
        }
        return cell.innerText.trim();
    }

    // Aktifkan Scrollable Box, Sortable Header, Pencarian, & Pagination (20 per halaman) di semua tabel
    document.querySelectorAll('table.table').forEach(function (table) {
        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        if (!thead || !tbody) return;

        const allRows = Array.from(tbody.querySelectorAll('tr'));
        // Lewati jika tabel kosong (hanya berisi 1 baris pesan "Belum ada data" dengan colspan)
        if (allRows.length === 0 || (allRows.length === 1 && allRows[0].children.length === 1 && allRows[0].children[0].hasAttribute('colspan'))) {
            return;
        }

        // 1. Bungkus tabel ke dalam .table-manager-container dan .table-scroll-box
        const existingResponsive = table.parentElement.classList.contains('table-responsive') ? table.parentElement : null;
        const targetNode = existingResponsive || table;

        const container = document.createElement('div');
        container.className = 'table-manager-container bg-white rounded';

        const scrollBox = document.createElement('div');
        scrollBox.className = 'table-scroll-box';

        targetNode.parentNode.insertBefore(container, targetNode);
        scrollBox.appendChild(table);
        if (existingResponsive) {
            existingResponsive.remove();
        }

        // 2. Buat Top Toolbar (Pilihan Jumlah Baris & Pencarian Cepat)
        const topBar = document.createElement('div');
        topBar.className = 'd-flex flex-wrap justify-content-between align-items-center gap-2 p-2 px-3 bg-white border-bottom';
        topBar.innerHTML = `
            <div class="d-flex align-items-center gap-2 small text-muted">
                <span>Tampilkan</span>
                <select class="form-select form-select-sm per-page-select" style="width: auto;">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="999999">Semua</option>
                </select>
                <span>data</span>
            </div>
            <div class="input-group input-group-sm" style="max-width: 240px;">
                <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                <input type="search" class="form-control table-search-input" placeholder="Cari di tabel ini...">
            </div>
        `;

        // 3. Buat Bottom Bar (Info Halaman & Tombol Page 1, 2, dst.)
        const bottomBar = document.createElement('div');
        bottomBar.className = 'd-flex flex-wrap justify-content-between align-items-center gap-2 p-2 px-3 bg-white';
        bottomBar.innerHTML = `
            <div class="small text-muted page-info-text"></div>
            <nav><ul class="pagination pagination-sm mb-0 page-buttons-list"></ul></nav>
        `;

        container.appendChild(topBar);
        container.appendChild(scrollBox);
        container.appendChild(bottomBar);

        // State Tabel
        let currentPage = 1;
        let perPage = 20;
        let searchQuery = '';
        let sortColIndex = null;
        let sortDirection = 'asc'; // 'asc' atau 'desc'

        const perPageSelect = topBar.querySelector('.per-page-select');
        const searchInput = topBar.querySelector('.table-search-input');
        const infoText = bottomBar.querySelector('.page-info-text');
        const paginationUl = bottomBar.querySelector('.page-buttons-list');

        // 4. Jadikan setiap kolom <th> bisa diklik untuk Sort (kecuali kolom Aksi/Template/Cetak)
        const headers = Array.from(thead.querySelectorAll('th'));
        headers.forEach((th, idx) => {
            const label = th.innerText.trim().toLowerCase();
            if (['aksi', 'template', 'cetak leger'].includes(label)) return;

            th.classList.add('sortable-th');
            const icon = document.createElement('i');
            icon.className = 'bi bi-arrow-down-up sort-indicator';
            th.appendChild(icon);

            th.addEventListener('click', function () {
                if (sortColIndex === idx) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortColIndex = idx;
                    sortDirection = 'asc';
                }

                // Update ikon di semua header
                headers.forEach(h => {
                    h.classList.remove('sort-active');
                    const ic = h.querySelector('.sort-indicator');
                    if (ic) ic.className = 'bi bi-arrow-down-up sort-indicator';
                });

                th.classList.add('sort-active');
                icon.className = sortDirection === 'asc'
                    ? 'bi bi-caret-up-fill sort-indicator'
                    : 'bi bi-caret-down-fill sort-indicator';

                // Urutkan baris
                allRows.sort((rowA, rowB) => {
                    const valA = getCellValue(rowA, idx);
                    const valB = getCellValue(rowB, idx);
                    const cmp = valA.localeCompare(valB, 'id', { numeric: true, sensitivity: 'base' });
                    return sortDirection === 'asc' ? cmp : -cmp;
                });

                // Susun ulang urutan DOM di dalam tbody
                allRows.forEach(r => tbody.appendChild(r));
                currentPage = 1;
                renderTable();
            });
        });

        // 5. Fungsi Render Baris & Nomor Halaman
        function renderTable() {
            const filteredRows = allRows.filter(row => {
                if (!searchQuery) return true;
                return row.innerText.toLowerCase().includes(searchQuery);
            });

            const totalItems = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(totalItems / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const startIndex = (currentPage - 1) * perPage;
            const endIndex = startIndex + perPage;

            // Sembunyikan semua baris terlebih dahulu
            allRows.forEach(row => {
                row.style.display = 'none';
            });

            // Tampilkan hanya baris pada halaman aktif
            filteredRows.slice(startIndex, endIndex).forEach(row => {
                row.style.display = '';
            });

            // Update teks informasi jumlah data
            const showStart = totalItems === 0 ? 0 : startIndex + 1;
            const showEnd = Math.min(endIndex, totalItems);
            infoText.textContent = `Menampilkan ${showStart}–${showEnd} dari ${totalItems} data`;

            // Render tombol Page 1, 2, 3, dst.
            paginationUl.innerHTML = '';
            if (totalPages <= 1) return;

            const addPageBtn = (label, targetPage, disabled = false, active = false) => {
                const li = document.createElement('li');
                li.className = `page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}`;
                const btn = document.createElement('button');
                btn.type = 'button'; // Penting agar tidak men-submit form nilai/absensi
                btn.className = 'page-link';
                btn.innerHTML = label;
                if (!disabled && !active) {
                    btn.addEventListener('click', () => {
                        currentPage = targetPage;
                        renderTable();
                        scrollBox.scrollTop = 0;
                    });
                }
                li.appendChild(btn);
                paginationUl.appendChild(li);
            };

            addPageBtn('&laquo;', currentPage - 1, currentPage === 1);

            for (let p = 1; p <= totalPages; p++) {
                if (p === 1 || p === totalPages || (p >= currentPage - 2 && p <= currentPage + 2)) {
                    addPageBtn(p, p, false, p === currentPage);
                } else if (p === currentPage - 3 || p === currentPage + 3) {
                    addPageBtn('...', p, true, false);
                }
            }

            addPageBtn('&raquo;', currentPage + 1, currentPage === totalPages);
        }

        // Event Listeners untuk Per-Page dan Search
        perPageSelect.addEventListener('change', function () {
            perPage = parseInt(this.value, 10) || 20;
            currentPage = 1;
            renderTable();
        });

        searchInput.addEventListener('input', function () {
            searchQuery = this.value.trim().toLowerCase();
            currentPage = 1;
            renderTable();
        });

        // Render pertama kali saat halaman dimuat
        renderTable();
    });
});
</script>
</body>
</html>