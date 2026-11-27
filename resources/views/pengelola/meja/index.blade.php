@extends('layouts.pengelola')

@section('title', 'Kelola Meja & QR')
@section('page_title', 'Kelola Meja & QR Code')
@section('page_subtitle', 'Atur meja fisik Sentra Kuliner D\'Kupon, buat token QR unik, dan unduh stiker QR siap cetak.')

@section('page_actions')
    <a
        href="{{ route('pengelola.meja.create') }}"
        class="inline-flex items-center justify-center gap-2 min-h-[44px] px-4 rounded-xl bg-brand-forest hover:bg-brand-forest-hover text-white text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-brand-forest"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Meja</span>
    </a>
@endsection

@section('content')
    {{-- Ringkasan Meja --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-default border border-ui-border rounded-2xl p-4">
            <p class="text-xs font-medium text-ui-muted">Total Meja</p>
            <p class="mt-1 text-2xl font-bold text-ui-text tabular-nums">{{ $ringkasan['total'] }}</p>
        </div>
        <div class="bg-surface-default border border-ui-border rounded-2xl p-4">
            <p class="text-xs font-medium text-ui-muted">Tersedia</p>
            <p class="mt-1 text-2xl font-bold text-state-success tabular-nums">{{ $ringkasan['tersedia'] }}</p>
        </div>
        <div class="bg-surface-default border border-ui-border rounded-2xl p-4">
            <p class="text-xs font-medium text-ui-muted">Terisi</p>
            <p class="mt-1 text-2xl font-bold text-state-warning tabular-nums">{{ $ringkasan['terisi'] }}</p>
        </div>
        <div class="bg-surface-default border border-ui-border rounded-2xl p-4">
            <p class="text-xs font-medium text-ui-muted">Tidak Aktif</p>
            <p class="mt-1 text-2xl font-bold text-state-danger tabular-nums">{{ $ringkasan['tidak_aktif'] }}</p>
        </div>
    </div>

    {{-- Filter Status Meja --}}
    <div class="flex flex-wrap items-center gap-2">
        @foreach (['semua' => 'Semua Meja', 'tersedia' => 'Tersedia', 'terisi' => 'Terisi', 'tidak_aktif' => 'Tidak Aktif'] as $val => $label)
            <a
                href="{{ route('pengelola.meja.index', $val === 'semua' ? [] : ['status' => $val]) }}"
                class="inline-flex items-center justify-center min-h-[40px] px-4 rounded-xl text-xs font-semibold border transition-colors {{ $statusFilter === $val ? 'bg-brand-forest text-white border-brand-forest' : 'bg-surface-default text-ui-text border-ui-border hover:bg-brand-ivory' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Daftar Kartu Meja & QR --}}
    @if ($mejas->isEmpty())
        <div class="bg-surface-default border border-ui-border rounded-2xl p-10 text-center">
            <h2 class="text-base font-semibold text-ui-text">Belum ada data meja</h2>
            <p class="mt-1 text-sm text-ui-muted">Tambahkan meja baru untuk menghasilkan QR Code pemesanan otomatis.</p>
            <a
                href="{{ route('pengelola.meja.create') }}"
                class="mt-4 inline-flex items-center justify-center min-h-[44px] px-4 rounded-xl bg-brand-forest hover:bg-brand-forest-hover text-white text-sm font-semibold"
            >
                Tambah Meja Pertama
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($mejas as $meja)
                @php
                    $token = $qrService->generateToken($meja);
                @endphp
                <div class="bg-surface-default border border-ui-border rounded-2xl shadow-xs overflow-hidden flex flex-col">
                    <div class="h-1 bg-brand-gold" aria-hidden="true"></div>
                    <div class="p-5 flex-1 flex flex-col gap-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h2 class="text-lg font-bold text-ui-text">Meja {{ $meja->nomor_meja }}</h2>
                                <p class="text-xs text-ui-muted">
                                    {{ $meja->kapasitas ? "Kapasitas {$meja->kapasitas} kursi" : 'Kapasitas belum diatur' }}
                                </p>
                            </div>

                            @if ($meja->status === \App\Models\Meja::STATUS_TERSEDIA)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-state-success-bg text-state-success">
                                    Tersedia
                                </span>
                            @elseif ($meja->status === \App\Models\Meja::STATUS_TERISI)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-state-warning-bg text-state-warning">
                                    Terisi
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-state-danger-bg text-state-danger">
                                    Tidak Aktif
                                </span>
                            @endif
                        </div>

                        {{-- Preview QR Code --}}
                        <div class="rounded-xl border border-ui-border bg-brand-ivory/60 p-4 flex flex-col items-center justify-center gap-2">
                            <img
                                src="{{ route('pengelola.meja.qr.preview', $meja) }}"
                                alt="QR Code Meja {{ $meja->nomor_meja }}"
                                width="150"
                                height="150"
                                class="w-36 h-36 rounded-lg bg-white p-1.5 border border-ui-border"
                            >
                            <p class="text-[11px] font-mono text-ui-muted break-all text-center">
                                Token: {{ $token }}
                            </p>
                        </div>

                        {{-- Aksi --}}
                        <div class="mt-auto pt-2 flex flex-col gap-2">
                            <a
                                href="{{ route('pengelola.meja.qr.download', $meja) }}"
                                class="w-full inline-flex items-center justify-center gap-2 min-h-[44px] px-4 rounded-xl bg-brand-forest hover:bg-brand-forest-hover text-white text-xs font-semibold transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M7 10l5 5 5-5M12 15V3"/>
                                </svg>
                                <span>Unduh Stiker QR (SVG)</span>
                            </a>

                            <div class="grid grid-cols-2 gap-2">
                                <a
                                    href="{{ route('pengelola.meja.edit', $meja) }}"
                                    class="inline-flex items-center justify-center min-h-[44px] px-3 rounded-xl border border-brand-forest text-brand-forest text-xs font-semibold hover:bg-brand-forest hover:text-white transition-colors"
                                >
                                    Edit
                                </a>

                                <form method="POST" action="{{ route('pengelola.meja.qr.regenerate', $meja) }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full min-h-[44px] px-3 rounded-xl border border-ui-border bg-surface-default text-ui-text text-xs font-semibold hover:bg-brand-ivory transition-colors cursor-pointer"
                                    >
                                        Perbarui QR
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
