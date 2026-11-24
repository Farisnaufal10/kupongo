<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#103B2C">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KuponGo') — Sentra Kuliner D'Kupon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-ivory min-h-full font-sans antialiased text-ui-text selection:bg-brand-gold/30 selection:text-brand-forest">

    {{-- Frame Mobile-First Terpusat (Maksimal 480px untuk smartphone & tablet/desktop) --}}
    <div class="max-w-md mx-auto min-h-screen bg-brand-ivory flex flex-col relative shadow-xl border-x border-ui-border/50">

        {{-- ============================================================ --}}
        {{-- Header Navigation (Mobile-First Style)                        --}}
        {{-- ============================================================ --}}
        <header class="sticky top-0 z-30 bg-surface-default/95 backdrop-blur-md border-b border-ui-border/80 px-4 py-2.5 transition-all duration-200">
            <div class="flex items-center justify-between gap-3 min-h-[44px]">

                {{-- Left: Back Icon (pada subhalaman) / Spacer Simetris Pengganti Hamburger --}}
                <div class="shrink-0 flex items-center">
                    @hasSection('back_url')
                        <a href="@yield('back_url')"
                           class="min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-ui-text hover:bg-ui-border/50 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-forest"
                           aria-label="Kembali">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"/>
                            </svg>
                        </a>
                    @else
                        {{-- Spacer kosong pengganti hamburger menu agar logo tetap presisi di tengah --}}
                        <div class="w-11 h-11" aria-hidden="true"></div>
                    @endif
                </div>

                {{-- Center: Wordmark Logo "KuponGo" Terpusat Seperti Semula --}}
                <div class="flex-1 text-center min-w-0">
                    <a href="{{ route('pembeli.index') }}" class="inline-block py-1 focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-forest rounded">
                        <span class="text-xl font-extrabold tracking-tight text-brand-forest block leading-none font-sans">KuponGo</span>
                        @if(session('nama_pembeli'))
                            <span class="text-xs font-semibold text-ui-text block mt-1 truncate max-w-[200px] mx-auto">
                                Hai, <span class="text-brand-forest font-bold">{{ session('nama_pembeli') }}</span> 👋
                            </span>
                        @else
                            <span class="text-[10px] font-bold tracking-widest text-brand-gold uppercase block mt-1">Sentra Kuliner</span>
                        @endif
                    </a>
                </div>

                {{-- Right: Informasi Nomor Meja (jika ada) & Keranjang Belanja --}}
                <div class="shrink-0 flex items-center gap-1.5">
                    @php
                        $nomorMeja = session('nomor_meja');
                        $cartCount = app(\App\Services\Pembeli\CartService::class)->totalUnit();
                    @endphp

                    {{-- Informasi Nomor Meja (Hanya tampil jika identitas meja tervalidasi dan ada di session) --}}
                    @if($nomorMeja)
                        <span class="text-xs font-bold bg-brand-forest/10 text-brand-forest px-2.5 py-1 rounded-full border border-brand-forest/20 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-brand-forest" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19h16"/><path d="M4 15h16"/><path d="M9 11v8"/><path d="M15 11v8"/></svg>
                            Meja {{ $nomorMeja }}
                        </span>
                    @endif

                    {{-- Tombol Keranjang (Touch Target Minimal 44x44px) --}}
                    <a href="{{ route('pembeli.keranjang') }}"
                       class="relative min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-brand-forest hover:bg-brand-forest/5 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-forest"
                       aria-label="Keranjang belanja{{ $cartCount > 0 ? ', ' . $cartCount . ' item' : ' kosong' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>

                        {{-- Badge Keranjang --}}
                        <span id="cart-badge"
                              class="{{ $cartCount > 0 ? '' : 'hidden' }} absolute top-1 right-1 min-w-[20px] h-[20px] px-1 bg-brand-forest text-white text-[11px] font-extrabold rounded-full flex items-center justify-center border-2 border-white leading-none shadow-sm"
                              aria-hidden="true">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    </a>
                </div>

            </div>
        </header>

        {{-- ============================================================ --}}
        {{-- Flash Notification Messages                                   --}}
        {{-- ============================================================ --}}
        @if(session('success'))
            <div role="status" aria-live="polite" class="px-4 pt-3">
                <div class="flex items-center gap-2.5 bg-state-success-bg text-state-success text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-state-success/20 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div role="alert" aria-live="assertive" class="px-4 pt-3">
                <div class="flex items-center gap-2.5 bg-state-danger-bg text-state-danger text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-state-danger/20 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- Main Page Content (pb-32 agar konten tidak tertutup bottom nav) --}}
        {{-- ============================================================ --}}
        <main class="flex-1 px-4 py-4 pb-32">
            @yield('content')
        </main>

        {{-- ============================================================ --}}
        {{-- Bottom Navigation Bar (Mobile Reference Style)               --}}
        {{-- ============================================================ --}}
        <nav class="fixed bottom-0 inset-x-0 z-40 max-w-md mx-auto bg-surface-default/95 backdrop-blur-md border-t border-ui-border shadow-lg px-2 py-2"
             aria-label="Navigasi Bawah">
            <div class="grid grid-cols-4 items-center gap-1">

                {{-- Tab 1: Beranda --}}
                <a href="{{ route('pembeli.index') }}"
                   class="min-h-[48px] flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('pembeli.index') && !request()->has('filter') ? 'text-brand-forest font-bold' : 'text-ui-muted hover:text-brand-forest' }}"
                   aria-label="Beranda KuponGo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ request()->routeIs('pembeli.index') ? '2.4' : '1.8' }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <span class="text-xs leading-tight">Beranda</span>
                </a>

                {{-- Tab 2: Tenant --}}
                <a href="{{ route('pembeli.index') }}#daftar-tenant"
                   class="min-h-[48px] flex flex-col items-center justify-center py-1 px-1 rounded-xl text-ui-muted hover:text-brand-forest transition-colors"
                   aria-label="Daftar Tenant">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/>
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/>
                        <path d="M2 7h20"/>
                        <path d="M22 7v3a2 2 0 0 1-2 2v0a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12v0a2 2 0 0 1-2-2V7"/>
                    </svg>
                    <span class="text-xs leading-tight">Tenant</span>
                </a>

                {{-- Tab 3: Keranjang --}}
                <a href="{{ route('pembeli.keranjang') }}"
                   class="min-h-[48px] flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-colors {{ request()->routeIs('pembeli.keranjang') ? 'text-brand-forest font-bold' : 'text-ui-muted hover:text-brand-forest' }}"
                   aria-label="Keranjang Belanja">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ request()->routeIs('pembeli.keranjang') ? '2.4' : '1.8' }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                        <span id="nav-cart-badge"
                              class="{{ $cartCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-2 min-w-[16px] h-[16px] px-1 bg-brand-gold text-brand-forest text-[10px] font-extrabold rounded-full flex items-center justify-center leading-none shadow-xs">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    </div>
                    <span class="text-xs leading-tight">Keranjang</span>
                </a>

                {{-- Tab 4: Pesanan --}}
                <button type="button"
                        id="btn-nav-pesanan"
                        class="min-h-[48px] flex flex-col items-center justify-center py-1 px-1 rounded-xl text-ui-muted hover:text-brand-forest transition-colors"
                        aria-label="Status Pesanan">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <span class="text-xs leading-tight">Pesanan</span>
                </button>

            </div>
        </nav>

        {{-- ============================================================ --}}
        {{-- Modal Info Status Pesanan                                    --}}
        {{-- ============================================================ --}}
        <div id="modal-pesanan" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs opacity-0 pointer-events-none transition-opacity duration-300 flex items-center justify-center p-4">
            <div class="bg-surface-default rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl scale-95 transition-transform duration-300" id="modal-pesanan-content">
                <div class="w-14 h-14 bg-brand-forest/10 text-brand-forest rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-ui-text mb-1">Status Pesanan</h3>
                <p class="text-xs text-ui-muted leading-relaxed mb-5">
                    Riwayat dan pelacakan pesanan akan otomatis aktif setelah Anda melakukan checkout di keranjang (integrasi Sprint 3+ Anggota 4).
                </p>
                <div class="space-y-2">
                    <a href="{{ route('pembeli.keranjang') }}" class="min-h-[44px] flex items-center justify-center w-full py-2.5 px-4 bg-brand-forest hover:bg-brand-forest-hover text-white text-xs font-bold rounded-xl transition-colors">
                        Lihat Keranjang Saya
                    </a>
                    <button type="button" id="btn-close-pesanan-modal" class="min-h-[44px] flex items-center justify-center w-full py-2.5 px-4 bg-ui-border/50 text-ui-text text-xs font-semibold rounded-xl hover:bg-ui-border transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
