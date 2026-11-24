@extends('layouts.pembeli')

@section('title', 'Pilih Tenant & Menu')

@section('content')

    {{-- ============================================================ --}}
    {{-- A. Search Bar (Hanya ditampilkan jika tenant tersedia)        --}}
    {{-- ============================================================ --}}
    @if(!$tenants->isEmpty() || !empty($search))
        <div class="mb-4">
            <form method="GET" action="{{ route('pembeli.index') }}" id="search-form" class="relative flex items-center">
                <span class="absolute left-3.5 text-ui-muted pointer-events-none" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </span>

                <input type="text"
                       name="q"
                       id="search-input"
                       value="{{ $search ?? '' }}"
                       placeholder="Cari tenant atau menu favorit..."
                       class="w-full pl-10 pr-12 py-2.5 min-h-[44px] bg-surface-default border border-ui-border rounded-full text-xs font-medium text-ui-text placeholder:text-ui-muted/70 shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-forest/20 focus:border-brand-forest transition-all"
                       aria-label="Cari tenant atau menu">

                @if(!empty($search))
                    <a href="{{ route('pembeli.index') }}"
                       class="absolute right-11 min-w-[36px] min-h-[36px] flex items-center justify-center text-ui-muted hover:text-ui-text rounded-full"
                       aria-label="Hapus pencarian">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </a>
                @endif

                <button type="submit"
                        class="absolute right-1.5 w-8 h-8 rounded-full bg-brand-forest text-white flex items-center justify-center hover:bg-brand-forest-hover transition-colors shadow-xs"
                        aria-label="Cari">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="21" x2="4" y2="14"/>
                        <line x1="4" y1="10" x2="4" y2="3"/>
                        <line x1="12" y1="21" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12" y2="3"/>
                        <line x1="20" y1="21" x2="20" y2="16"/>
                        <line x1="20" y1="12" x2="20" y2="3"/>
                        <line x1="1" y1="14" x2="7" y2="14"/>
                        <line x1="9" y1="8" x2="15" y2="8"/>
                        <line x1="17" y1="16" x2="23" y2="16"/>
                    </svg>
                </button>
            </form>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- B. Hero Banner (Visual Keberagaman Hidangan Meja Kuliner)     --}}
    {{-- ============================================================ --}}
    <section class="relative rounded-3xl overflow-hidden shadow-md mb-6 bg-brand-forest min-h-[190px] flex items-center border border-brand-forest/20"
             aria-label="Highlight Sentra Kuliner">
        {{-- Foto Meja Kuliner dengan Hidangan Beragam --}}
        <img src="{{ asset('images/pembeli/hero-banner.jpg') }}"
             alt="Keberagaman Hidangan di Sentra Kuliner D'Kupon"
             class="absolute inset-0 w-full h-full object-cover object-center scale-105"
             loading="eager"
             onerror="this.style.display='none'">

        {{-- Gradient Overlay Gelap yang Dioptimalkan untuk Kontras Tajam --}}
        <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/75 to-black/35"></div>

        {{-- Konten Banner --}}
        <div class="relative z-10 px-5 py-5 max-w-[85%] text-white">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-gold tracking-wide uppercase mb-1.5 bg-black/40 px-2.5 py-0.5 rounded-full border border-brand-gold/30 backdrop-blur-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-brand-gold" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                Sentra Kuliner D'Kupon
            </span>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight leading-tight uppercase font-sans text-white mb-1.5 drop-shadow-sm">
                Satu Meja, Banyak Pilihan.
            </h2>
            <p class="text-xs sm:text-sm text-white/90 leading-relaxed font-medium mb-4">
                Pesan makanan dari berbagai tenant dalam satu keranjang.
            </p>
            <a href="#daftar-tenant"
               class="min-h-[44px] inline-flex items-center gap-2 bg-brand-gold hover:bg-[#b8955e] text-brand-forest text-xs font-extrabold px-5 py-2.5 rounded-full transition-all shadow-md active:scale-95">
                <span>Jelajahi Tenant</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- C. Filter Status Cepat (Hanya jika tenant tersedia)           --}}
    {{-- ============================================================ --}}
    @if(!$tenants->isEmpty())
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 mb-4 -mx-1 px-1" role="tablist" aria-label="Filter Tenant">
            <button type="button"
                    data-filter="semua"
                    class="filter-pill-btn min-h-[44px] shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold bg-brand-forest text-white transition-all shadow-xs"
                    role="tab"
                    aria-selected="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                Semua Tenant
            </button>
            <button type="button"
                    data-filter="buka"
                    class="filter-pill-btn min-h-[44px] shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold bg-surface-default border border-ui-border text-ui-text hover:bg-ui-border/40 transition-all"
                    role="tab"
                    aria-selected="false">
                <span class="w-2 h-2 rounded-full bg-state-success" aria-hidden="true"></span>
                Buka Sekarang
            </button>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- D. Daftar Tenant                                              --}}
    {{-- ============================================================ --}}
    <section id="daftar-tenant" class="mb-8" aria-labelledby="heading-tenant">
        <div class="flex items-center justify-between mb-3 px-1">
            <div>
                <h3 id="heading-tenant" class="text-base font-bold text-ui-text">Daftar Tenant</h3>
                <p class="text-xs text-ui-muted">Pilih stand kuliner untuk memesan hidangan</p>
            </div>
            @if(!$tenants->isEmpty())
                <span class="text-xs font-bold text-brand-forest bg-brand-forest/10 px-2.5 py-0.5 rounded-full">
                    {{ $tenants->count() }} Tenant
                </span>
            @endif
        </div>

        @if($tenants->isEmpty())
            {{-- Empty State Ringkas & Elegan (Teks wajib dipertahankan untuk test) --}}
            <div role="status"
                 aria-label="Tidak ada tenant tersedia"
                 class="flex flex-col items-center justify-center py-10 px-4 bg-surface-default rounded-2xl border border-ui-border text-center shadow-2xs">
                <div class="w-12 h-12 rounded-xl bg-ui-border/40 text-ui-muted flex items-center justify-center mb-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/>
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/>
                        <path d="M2 7h20"/>
                        <path d="M22 7v3a2 2 0 0 1-2 2v0a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12v0a2 2 0 0 1-2-2V7"/>
                    </svg>
                </div>
                <h4 class="text-xs sm:text-sm font-bold text-ui-text">Belum ada tenant tersedia</h4>
                <p class="text-xs text-ui-muted mt-0.5 max-w-xs">Silakan coba lagi nanti.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-3.5" role="list" aria-label="Daftar tenant Sentra Kuliner">
                @foreach($tenants as $tenant)
                    @php $buka = $tenant->isBuka(); @endphp

                    {{-- Modern Tenant Card --}}
                    <article role="listitem"
                             data-status="{{ $buka ? 'buka' : 'tutup' }}"
                             data-nama="{{ strtolower($tenant->nama_tenant) }}"
                             class="tenant-card bg-surface-default rounded-2xl border border-ui-border overflow-hidden transition-all duration-200 hover:shadow-md {{ $buka ? '' : 'opacity-75' }}"
                             aria-label="{{ $tenant->nama_tenant }}, status {{ $buka ? 'buka' : 'tutup' }}">

                        {{-- Card Header Cover --}}
                        <div class="relative h-28 bg-brand-forest/10 overflow-hidden">
                            <img src="{{ asset('images/pembeli/tenant-banner.jpg') }}"
                                 alt="{{ $tenant->nama_tenant }}"
                                 class="w-full h-full object-cover object-center brightness-95"
                                 loading="lazy"
                                 onerror="this.style.display='none'">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>

                            {{-- Badge Status di Sudut Kanan Atas --}}
                            <div class="absolute top-2.5 right-2.5">
                                @if($buka)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-state-success-bg/95 text-state-success px-2.5 py-1 rounded-full backdrop-blur-xs border border-state-success/30 shadow-xs"
                                          aria-label="Status: Buka">
                                        <span class="w-2 h-2 rounded-full bg-state-success" aria-hidden="true"></span>
                                        Buka
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold bg-state-danger-bg/95 text-state-danger px-2.5 py-1 rounded-full backdrop-blur-xs border border-state-danger/30 shadow-xs"
                                          aria-label="Status: Tutup">
                                        <span class="w-2 h-2 rounded-full bg-state-danger" aria-hidden="true"></span>
                                        Tutup
                                    </span>
                                @endif
                            </div>

                            {{-- Nama Tenant di Atas Gradient Cover --}}
                            <div class="absolute bottom-2.5 left-3 right-3 text-white">
                                <h4 class="font-bold text-base leading-tight drop-shadow-xs truncate">
                                    {{ $tenant->nama_tenant }}
                                </h4>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-3.5 flex items-center justify-between gap-3 bg-surface-default">
                            <div class="flex items-center gap-2 text-xs text-ui-muted min-w-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-brand-gold shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                                </svg>
                                <span class="truncate font-semibold text-ui-text text-xs">
                                    {{ $tenant->menu_tersedia_count }} menu tersedia
                                </span>
                            </div>

                            {{-- Tombol Lihat Menu (Diuji oleh PembeliPageTest, min-h 44px) --}}
                            <div class="shrink-0">
                                @if($buka)
                                    <a href="{{ route('pembeli.tenant', $tenant->id_tenant) }}"
                                       class="min-h-[44px] inline-flex items-center gap-1.5 bg-brand-forest hover:bg-brand-forest-hover text-white text-xs font-bold px-4 py-2 rounded-xl transition-all shadow-xs active:scale-95"
                                       aria-label="Lihat menu {{ $tenant->nama_tenant }}">
                                        <span>Lihat Menu</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                                    </a>
                                @else
                                    <button type="button"
                                            disabled
                                            aria-disabled="true"
                                            class="min-h-[44px] inline-flex items-center gap-1.5 bg-ui-border/60 text-ui-muted text-xs font-semibold px-3 py-2 rounded-xl cursor-not-allowed"
                                            aria-label="{{ $tenant->nama_tenant }} sedang tutup">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                        Sedang Tutup
                                    </button>
                                @endif
                            </div>
                        </div>

                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ============================================================ --}}
    {{-- E. Menu Pilihan / Tersedia (Food Cards ala Referensi)         --}}
    {{-- ============================================================ --}}
    @if(isset($featuredMenus) && $featuredMenus->isNotEmpty() && !$tenants->isEmpty())
        <section class="mb-8" aria-labelledby="heading-menu-pilihan">
            <div class="flex items-center justify-between mb-3 px-1">
                <div>
                    <h3 id="heading-menu-pilihan" class="text-base font-bold text-ui-text">Pilihan Menu</h3>
                    <p class="text-xs text-ui-muted">Langsung pesan hidangan favorit Anda</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3" role="list" aria-label="Daftar menu pilihan">
                @foreach($featuredMenus as $menu)
                    @php
                        $tenantBuka = $menu->tenant && $menu->tenant->isBuka();
                        $bisaDipesan = $tenantBuka && $menu->is_tersedia;
                    @endphp

                    {{-- Food Card Vertikal ala Referensi Desain --}}
                    <article role="listitem"
                             class="bg-surface-default rounded-2xl border border-ui-border overflow-hidden flex flex-col transition-all duration-200 hover:shadow-md {{ $bisaDipesan ? '' : 'opacity-65' }}"
                             aria-label="{{ $menu->nama_menu }}">

                        {{-- Foto Makanan --}}
                        <div class="relative h-28 bg-brand-forest/5 overflow-hidden">
                            <img src="{{ asset('images/pembeli/menu-default.jpg') }}"
                                 alt="{{ $menu->nama_menu }}"
                                 class="w-full h-full object-cover object-center"
                                 loading="lazy"
                                 onerror="this.style.display='none'">

                            {{-- Badge Status Ketersediaan --}}
                            @if(!$menu->is_tersedia)
                                <span class="absolute top-2 left-2 text-[11px] font-bold bg-state-danger-bg text-state-danger px-2 py-0.5 rounded-full border border-state-danger/30 shadow-xs">
                                    Habis
                                </span>
                            @elseif(!$tenantBuka)
                                <span class="absolute top-2 left-2 text-[11px] font-bold bg-ui-border text-ui-muted px-2 py-0.5 rounded-full shadow-xs">
                                    Tutup
                                </span>
                            @endif
                        </div>

                        {{-- Informasi Menu --}}
                        <div class="p-3 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[11px] font-bold text-brand-gold uppercase tracking-wide block truncate">
                                    {{ $menu->tenant->nama_tenant ?? 'Tenant' }}
                                </span>
                                <h4 class="font-bold text-xs text-ui-text leading-snug line-clamp-2 mt-0.5">
                                    {{ $menu->nama_menu }}
                                </h4>
                            </div>

                            <div class="flex items-center justify-between gap-2 mt-2.5 pt-2 border-t border-ui-border/60">
                                <span class="text-xs font-extrabold text-brand-forest tabular-nums">
                                    Rp&nbsp;{{ number_format($menu->harga, 0, ',', '.') }}
                                </span>

                                @if($bisaDipesan)
                                    <button type="button"
                                            class="add-to-cart-btn min-w-[44px] min-h-[44px] rounded-full bg-brand-forest hover:bg-brand-forest-hover active:bg-brand-forest-hover text-white flex items-center justify-center transition-transform active:scale-90 shadow-xs"
                                            data-menu-id="{{ $menu->id_menu }}"
                                            data-menu-nama="{{ $menu->nama_menu }}"
                                            data-menu-harga="{{ $menu->harga }}"
                                            data-tenant-id="{{ $menu->id_tenant }}"
                                            aria-label="Tambah {{ $menu->nama_menu }} ke keranjang">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                        </svg>
                                    </button>
                                @else
                                    <span class="min-w-[44px] min-h-[44px] rounded-full bg-ui-border/50 text-ui-muted flex items-center justify-center text-xs font-bold"
                                          aria-label="Tidak dapat dipesan">
                                        -
                                    </span>
                                @endif
                            </div>
                        </div>

                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================================================ --}}
    {{-- F. Highlight Banner Sekunder (Hanya jika tenant tersedia)     --}}
    {{-- ============================================================ --}}
    @if(!$tenants->isEmpty())
        <section class="relative rounded-2xl overflow-hidden shadow-md bg-brand-forest min-h-[120px] flex items-center mb-4 border border-brand-forest/20"
                 aria-label="Informasi Pemesanan">
            <img src="{{ asset('images/pembeli/banner-multi.jpg') }}"
                 alt="Pesan Makanan Multi Tenant"
                 class="absolute inset-0 w-full h-full object-cover object-center opacity-40"
                 loading="lazy"
                 onerror="this.style.display='none'">
            <div class="absolute inset-0 bg-gradient-to-r from-brand-forest via-brand-forest/90 to-transparent"></div>

            <div class="relative z-10 px-4 py-4 text-white max-w-[80%]">
                <span class="text-[11px] font-bold text-brand-gold uppercase tracking-wider block mb-0.5">
                    Multi-Tenant KuponGo
                </span>
                <h4 class="text-xs font-bold leading-tight mb-1">
                    Bebas Pesan dari Tenant Berbeda
                </h4>
                <p class="text-xs text-white/80 leading-relaxed mb-2.5">
                    Semua menu digabung dalam satu keranjang tanpa repot bolak-balik.
                </p>
                <a href="{{ route('pembeli.keranjang') }}"
                   class="min-h-[44px] inline-flex items-center gap-1.5 text-xs font-bold text-brand-gold hover:underline">
                    <span>Periksa Keranjang Saya</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>
        </section>
    @endif

@endsection
