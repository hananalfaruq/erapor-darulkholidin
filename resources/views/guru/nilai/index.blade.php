@extends('layouts.app')
@section('title', 'Pilih Kelas - Mata Pelajaran')

@section('content')
@forelse($groupedPengampu as $namaMapel => $items)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-bold text-primary">{{ $namaMapel }}</div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($items as $p)
                    <div class="col-md-3">
                        <a href="{{ route('guru.nilai.detail', $p->id) }}" class="btn btn-outline-dark w-100 text-start d-flex justify-content-between align-items-center">
                            <span>Kelas <strong>{{ $p->kelasMapel->kelas->nama_kelas }}</strong></span>
                            <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-info">Anda belum memiliki kelas-mapel yang diampu pada Tahun Ajaran ini.</div>
@endforelse
@endsection