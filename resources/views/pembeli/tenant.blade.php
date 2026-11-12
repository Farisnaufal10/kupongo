@extends('layouts.pembeli')

@section('title', $tenant->nama_tenant)
@section('subtitle', 'Menu ' . $tenant->nama_tenant)

@section('content')

    {{-- ============================================================ --}}
    {{-- Navigasi kembali                                             --}}
    {{-- ============================================================ --}}
    <a
        href="{{ route('pembeli.index') }}"
        class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-forest
               hover:text-brand-forest-hover transition-colors duration-150 mb-5
               focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-forest rounded"
        aria-label="Kembali ke daftar tenant"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M15 18l-6-6 6-6"/>
        </svg>
        Semua Tenant
    </a>

    {{-- ============================================================ --}}
    {{-- Header Tenant                                                 --}}
    {{-- ============================================================ --}}
    <div class="bg-surface-default rounded-2xl border border-ui-border overflow-hidden mb-5">
        {{-- Accent bar --}}
        <div class="h-1 {{ $tenantBuka ? 'bg-brand-gold' : 'bg-state-danger' }}"></div>

        <div class="p-4">
            <div class="flex items-center gap-3">
                {{-- Ikon toko --}}
                <div
                    class="shrink-0 w-12 h-12 rounded-xl flex items-center justify-center
                           {{ $tenantBuka ? 'bg-brand-forest/8' : 'bg-state-danger-bg' }}"
                    aria-hidden="true"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6 {{ $tenantBuka ? 'text-brand-forest' : 'text-state-danger' }}"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>

                {{-- Nama + status --}}
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-ui-text truncate">{{ $tenant->nama_tenant }}</h2>

                    {{-- Badge status --}}
                    @if($tenantBuka)
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-semibold
                                   bg-state-success-bg text-state-success
                                   px-2.5 py-0.5 rounded-full mt-1"
                            aria-label="Status: Buka"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-state-success" aria-hidden="true"></span>
                            Buka
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-semibold
                                   bg-state-danger-bg text-state-danger
                                   px-2.5 py-0.5 rounded-full mt-1"
                            aria-label="Status: Tutup"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-state-danger" aria-hidden="true"></span>
                            Tutup
                        </span>
                    @endif
                </div>
            </div>

            {{-- Banner peringatan tutup --}}
            @if(!$tenantBuka)
                <div
                    class="mt-3 flex items-start gap-2.5 bg-state-danger-bg
                           rounded-xl px-3.5 py-3 text-state-danger"
                    role="alert"
                    aria-live="polite"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p class="text-xs font-medium leading-relaxed">
                        Tenant ini sedang tutup. Pemesanan tidak dapat dilakukan saat ini.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Daftar Menu                                                   --}}
    {{-- ============================================================ --}}
    @if($menus->isEmpty())

        {{-- ── Empty State ── --}}
        <div
            role="status"
            aria-label="Tidak ada menu tersedia"
            class="flex flex-col items-center justify-center py-20 text-center"
        >
            <span class="w-16 h-16 rounded-2xl bg-ui-border/40 flex items-center justify-center mb-4" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-ui-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/>
                    <path d="M7 2v20"/>
                    <path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                </svg>
            </span>
            <p class="text-base font-semibold text-ui-text">Belum ada menu tersedia</p>
            <p class="text-sm text-ui-muted mt-1 max-w-[220px]">Silakan coba lagi nanti.</p>
        </div>

    @else

        {{-- Section heading --}}
        <h3 class="text-base font-semibold text-ui-text mb-3">Daftar Menu</h3>

        <div class="grid grid-cols-1 gap-3" role="list" aria-label="Daftar menu {{ $tenant->nama_tenant }}">
            @foreach($menus as $menu)
                @php
                    $bisaDipesan = $tenantBuka && $menu->is_tersedia;
                    $alasanDisabled = !$tenantBuka ? 'Tenant tutup' : 'Stok habis';
                @endphp

                {{-- ── Menu Card ── --}}
                <article
                    role="listitem"
                    class="bg-surface-default rounded-2xl border border-ui-border overflow-hidden
                           transition-shadow duration-200 {{ $bisaDipesan ? 'hover:shadow-md' : 'opacity-60' }}"
                    aria-label="{{ $menu->nama_menu }}, {{ $menu->is_tersedia ? 'tersedia' : 'habis' }}"
                >
                    <div class="p-4 flex items-start gap-3">

                        {{-- Ikon menu placeholder --}}
                        <div
                            class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center
                                   {{ $bisaDipesan ? 'bg-brand-gold/15' : 'bg-ui-border/40' }}"
                            aria-hidden="true"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5 {{ $bisaDipesan ? 'text-brand-gold' : 'text-ui-muted' }}"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                                <line x1="6" y1="1" x2="6" y2="4"/>
                                <line x1="10" y1="1" x2="10" y2="4"/>
                                <line x1="14" y1="1" x2="14" y2="4"/>
                            </svg>
                        </div>

                        {{-- Info menu --}}
                        <div class="flex-1 min-w-0">
                            {{-- Nama + badge habis --}}
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h4 class="font-semibold text-ui-text text-sm leading-snug line-clamp-2">
                                    {{ $menu->nama_menu }}
                                </h4>

                                @if(!$menu->is_tersedia)
                                    <span
                                        class="shrink-0 text-xs font-semibold
                                               bg-state-danger-bg text-state-danger
                                               px-2 py-0.5 rounded-full"
                                        aria-label="Stok habis"
                                    >
                                        Habis
                                    </span>
                                @endif
                            </div>

                            {{-- Harga --}}
                            <p class="text-brand-forest font-bold text-sm">
                                Rp&nbsp;{{ number_format($menu->harga, 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Tombol Pesan --}}
                        <div class="shrink-0 self-center">
                            @if($bisaDipesan)
                                <button
                                    type="button"
                                    class="add-to-cart-btn
                                           inline-flex items-center justify-center gap-1
                                           min-w-[44px] min-h-[44px] px-3
                                           bg-brand-forest hover:bg-brand-forest-hover
                                           text-white text-xs font-bold
                                           rounded-xl transition-colors duration-150
                                           focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-forest"
                                    data-menu-id="{{ $menu->id_menu }}"
                                    data-menu-nama="{{ $menu->nama_menu }}"
                                    data-menu-harga="{{ $menu->harga }}"
                                    data-tenant-id="{{ $tenant->id_tenant }}"
                                    aria-label="Tambah {{ $menu->nama_menu }} ke keranjang"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    Pesan
                                </button>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    aria-disabled="true"
                                    class="inline-flex items-center justify-center gap-1
                                           min-w-[44px] min-h-[44px] px-3
                                           bg-ui-border/60 text-ui-muted
                                           text-xs font-semibold
                                           rounded-xl cursor-not-allowed"
                                    aria-label="{{ $alasanDisabled }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                    </svg>
                                    {{ !$tenantBuka ? 'Tutup' : 'Habis' }}
                                </button>
                            @endif
                        </div>

                    </div>
                </article>
            @endforeach
        </div>

        {{-- Sprint 2+: Keranjang akan ditambahkan di sini --}}

    @endif

@endsection
