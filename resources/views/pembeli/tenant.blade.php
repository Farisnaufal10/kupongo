@extends('layouts.pembeli')

@section('title', $tenant->nama_tenant)
@section('back_url', route('pembeli.index'))

@section('content')

    {{-- ============================================================ --}}
    {{-- A. Navigasi Kembali & Breadcrumb                              --}}
    {{-- ============================================================ --}}
    <div class="mb-3">
        <a href="{{ route('pembeli.index') }}"
           class="min-h-[44px] inline-flex items-center gap-1.5 text-xs font-bold text-brand-forest hover:text-brand-forest-hover transition-colors py-2 px-1 -ml-1 rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-forest"
           aria-label="Kembali ke daftar tenant">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Semua Tenant</span>
        </a>
    </div>

    {{-- ============================================================ --}}
    {{-- B. Header Tenant Card (Cover Foto + Status Operasional)      --}}
    {{-- ============================================================ --}}
    <section class="bg-surface-default rounded-3xl border border-ui-border overflow-hidden shadow-xs mb-5"
             aria-label="Profil Tenant {{ $tenant->nama_tenant }}">

        {{-- Cover Image --}}
        <div class="relative h-32 bg-brand-forest/15 overflow-hidden">
            <img src="{{ asset('images/pembeli/tenant-banner.jpg') }}"
                 alt="{{ $tenant->nama_tenant }}"
                 class="w-full h-full object-cover object-center brightness-95"
                 loading="eager"
                 onerror="this.style.display='none'">
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>

            {{-- Badge Buka / Tutup --}}
            <div class="absolute top-3 right-3">
                @if($tenantBuka)
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-state-success-bg/95 text-state-success px-3 py-1 rounded-full backdrop-blur-xs border border-state-success/30 shadow-xs"
                          aria-label="Status: Buka">
                        <span class="w-2 h-2 rounded-full bg-state-success" aria-hidden="true"></span>
                        Buka
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-state-danger-bg/95 text-state-danger px-3 py-1 rounded-full backdrop-blur-xs border border-state-danger/30 shadow-xs"
                          aria-label="Status: Tutup">
                        <span class="w-2 h-2 rounded-full bg-state-danger" aria-hidden="true"></span>
                        Tutup
                    </span>
                @endif
            </div>

            {{-- Nama Tenant di Cover --}}
            <div class="absolute bottom-3 left-4 right-4 text-white">
                <span class="text-[11px] font-bold uppercase tracking-wider text-brand-gold block mb-0.5">Stand Kuliner</span>
                <h2 class="text-lg sm:text-xl font-extrabold leading-tight truncate drop-shadow-xs">
                    {{ $tenant->nama_tenant }}
                </h2>
            </div>
        </div>

        {{-- Info Tambahan & Peringatan Tutup --}}
        <div class="p-4 bg-surface-default">
            <div class="flex items-center justify-between text-xs text-ui-muted">
                <span class="flex items-center gap-1.5 font-semibold text-ui-text">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-brand-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
                    {{ $menus->count() }} Menu Terdaftar
                </span>
                <span class="text-xs font-semibold text-brand-forest bg-brand-forest/5 px-2.5 py-1 rounded-full">
                    D'Kupon Sentra Kuliner
                </span>
            </div>

            {{-- Peringatan Tenant Tutup (Wajib untuk test) --}}
            @if(!$tenantBuka)
                <div class="mt-3.5 flex items-start gap-2.5 bg-state-danger-bg/80 border border-state-danger/25 text-state-danger text-xs font-semibold p-3.5 rounded-2xl"
                     role="alert"
                     aria-live="polite">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <p class="leading-relaxed">
                        Tenant ini sedang tutup. Pemesanan tidak dapat dilakukan saat ini.
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- C. Filter Pencarian Menu Tenant                               --}}
    {{-- ============================================================ --}}
    @if(!$menus->isEmpty())
        <div class="mb-4">
            <div class="relative flex items-center">
                <span class="absolute left-3.5 text-ui-muted pointer-events-none" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </span>
                <input type="text"
                       id="filter-menu-input"
                       placeholder="Cari hidangan di {{ $tenant->nama_tenant }}..."
                       class="w-full pl-10 pr-4 py-2.5 min-h-[44px] bg-surface-default border border-ui-border rounded-full text-xs font-medium text-ui-text placeholder:text-ui-muted/70 shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-forest/20 focus:border-brand-forest transition-all"
                       aria-label="Cari menu tenant">
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- D. Katalog Menu (Food Cards Grid ala Referensi)              --}}
    {{-- ============================================================ --}}
    <section aria-label="Katalog hidangan {{ $tenant->nama_tenant }}">
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-base font-bold text-ui-text">Daftar Menu</h3>
            <span class="text-xs text-ui-muted font-medium" id="menu-counter">
                Menampilkan {{ $menus->count() }} menu
            </span>
        </div>

        @if($menus->isEmpty())
            {{-- Empty State (Wajib untuk test) --}}
            <div role="status"
                 aria-label="Tidak ada menu tersedia"
                 class="flex flex-col items-center justify-center py-10 px-4 bg-surface-default rounded-2xl border border-ui-border text-center shadow-2xs">
                <div class="w-12 h-12 rounded-xl bg-ui-border/40 text-ui-muted flex items-center justify-center mb-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                        <line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>
                    </svg>
                </div>
                <h4 class="text-xs sm:text-sm font-bold text-ui-text">Belum ada menu tersedia</h4>
                <p class="text-xs text-ui-muted mt-0.5 max-w-xs">Silakan coba lagi nanti.</p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-3" role="list" id="menu-list-container" aria-label="Menu {{ $tenant->nama_tenant }}">
                @foreach($menus as $menu)
                    @php
                        $bisaDipesan = $tenantBuka && $menu->is_tersedia;
                    @endphp

                    {{-- Modern Food Card --}}
                    <article role="listitem"
                             data-menu-nama="{{ strtolower($menu->nama_menu) }}"
                             class="menu-card bg-surface-default rounded-2xl border border-ui-border overflow-hidden flex flex-col transition-all duration-200 hover:shadow-md {{ $bisaDipesan ? '' : 'opacity-65' }}"
                             aria-label="{{ $menu->nama_menu }}, {{ $menu->is_tersedia ? 'tersedia' : 'habis' }}">

                        {{-- Foto Menu Makanan --}}
                        <div class="relative h-28 bg-brand-forest/5 overflow-hidden">
                            <img src="{{ asset('images/pembeli/menu-default.jpg') }}"
                                 alt="{{ $menu->nama_menu }}"
                                 class="w-full h-full object-cover object-center"
                                 loading="lazy"
                                 onerror="this.style.display='none'">

                            {{-- Badge Ketersediaan Menu --}}
                            @if(!$menu->is_tersedia)
                                <span class="absolute top-2 left-2 text-[11px] font-bold bg-state-danger-bg text-state-danger px-2.5 py-0.5 rounded-full border border-state-danger/30 shadow-xs"
                                      aria-label="Status: Habis">
                                    Habis
                                </span>
                            @elseif(!$tenantBuka)
                                <span class="absolute top-2 left-2 text-[11px] font-bold bg-ui-border text-ui-muted px-2.5 py-0.5 rounded-full shadow-xs"
                                      aria-label="Status: Tutup">
                                    Tutup
                                </span>
                            @endif
                        </div>

                        {{-- Konten Menu --}}
                        <div class="p-3 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-xs sm:text-sm text-ui-text leading-snug line-clamp-2">
                                    {{ $menu->nama_menu }}
                                </h4>
                            </div>

                            <div class="mt-2.5 pt-2 border-t border-ui-border/60 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="text-[10px] text-ui-muted block -mb-0.5">Harga</span>
                                    <span class="text-xs sm:text-sm font-extrabold text-brand-forest tabular-nums truncate block">
                                        Rp&nbsp;{{ number_format($menu->harga, 0, ',', '.') }}
                                    </span>
                                </div>

                                {{-- Tombol Pesan (+ Pesan / Tutup / Habis, min-h-[44px]) --}}
                                <div>
                                    @if($bisaDipesan)
                                        <button type="button"
                                                class="add-to-cart-btn min-h-[44px] min-w-[44px] inline-flex items-center justify-center gap-1 bg-brand-forest hover:bg-brand-forest-hover active:bg-brand-forest-hover text-white text-xs font-bold px-3 py-2 rounded-xl transition-all shadow-xs active:scale-90"
                                                data-menu-id="{{ $menu->id_menu }}"
                                                data-menu-nama="{{ $menu->nama_menu }}"
                                                data-menu-harga="{{ $menu->harga }}"
                                                data-tenant-id="{{ $tenant->id_tenant }}"
                                                aria-label="Tambah {{ $menu->nama_menu }} ke keranjang">
                                            <span>+ Pesan</span>
                                        </button>
                                    @elseif(!$tenantBuka)
                                        <button type="button"
                                                disabled
                                                aria-disabled="true"
                                                class="min-h-[44px] inline-flex items-center justify-center bg-ui-border/60 text-ui-muted text-xs font-semibold px-3 py-2 rounded-xl cursor-not-allowed"
                                                aria-label="Tenant tutup">
                                            Tutup
                                        </button>
                                    @else
                                        <button type="button"
                                                disabled
                                                aria-disabled="true"
                                                class="min-h-[44px] inline-flex items-center justify-center bg-ui-border/60 text-ui-muted text-xs font-semibold px-3 py-2 rounded-xl cursor-not-allowed"
                                                aria-label="Menu habis">
                                            Habis
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </article>
                @endforeach
            </div>
        @endif
    </section>

@endsection
