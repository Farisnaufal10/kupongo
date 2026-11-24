<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#103B2C">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KuponGo') — KuponGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-ivory min-h-full font-sans antialiased text-ui-text">

    {{-- ============================================================ --}}
    {{-- Header                                                        --}}
    {{-- ============================================================ --}}
    <header class="bg-brand-forest text-white sticky top-0 z-20 shadow-md">
        <div class="max-w-screen-sm mx-auto px-4 h-14 flex items-center justify-between gap-3">

            {{-- Logo + Brand --}}
            <a href="{{ route('pembeli.index') }}" class="flex items-center gap-2.5 min-w-0 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white rounded-lg" aria-label="KuponGo — Beranda">
                <span class="shrink-0 w-8 h-8 rounded-lg bg-brand-gold/20 flex items-center justify-center" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 9V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v2"/>
                        <path d="M2 15v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2"/>
                        <path d="M22 9a3 3 0 0 1 0 6"/>
                        <path d="M2 9a3 3 0 0 0 0 6"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <h1 class="text-base font-bold leading-tight tracking-tight">KuponGo</h1>
                    <p class="text-[11px] text-white/60 leading-none truncate">@yield('subtitle', 'Pesan makanan &amp; minuman')</p>
                </div>
            </a>

            {{-- Tombol Keranjang --}}
            @php
                $cartCount = app(\App\Services\Pembeli\CartService::class)->totalUnit();
            @endphp
            <a
                href="{{ route('pembeli.keranjang') }}"
                class="relative shrink-0 w-10 h-10 rounded-xl flex items-center justify-center
                       hover:bg-white/10 transition-colors duration-150
                       focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
                aria-label="Keranjang belanja{{ $cartCount > 0 ? ', ' . $cartCount . ' item' : ' kosong' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                @if($cartCount > 0)
                    <span
                        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1
                               bg-brand-gold text-brand-forest text-[10px] font-bold
                               rounded-full flex items-center justify-center leading-none"
                        aria-hidden="true"
                        id="cart-badge"
                    >{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                @else
                    <span class="hidden" id="cart-badge" aria-hidden="true"></span>
                @endif
            </a>
        </div>
    </header>

    {{-- ============================================================ --}}
    {{-- Flash Messages                                                --}}
    {{-- ============================================================ --}}
    @if(session('success'))
        <div
            role="status"
            aria-live="polite"
            class="max-w-screen-sm mx-auto px-4 pt-3"
        >
            <div class="flex items-center gap-2 bg-state-success-bg text-state-success text-sm font-medium px-4 py-3 rounded-xl border border-state-success/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div
            role="alert"
            aria-live="assertive"
            class="max-w-screen-sm mx-auto px-4 pt-3"
        >
            <div class="flex items-center gap-2 bg-state-danger-bg text-state-danger text-sm font-medium px-4 py-3 rounded-xl border border-state-danger/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- Main Content                                                  --}}
    {{-- ============================================================ --}}
    <main class="max-w-screen-sm mx-auto px-4 py-5 pb-10">
        @yield('content')
    </main>

    {{-- ============================================================ --}}
    {{-- Footer                                                        --}}
    {{-- ============================================================ --}}
    <footer class="max-w-screen-sm mx-auto px-4 py-6 text-center">
        <p class="text-xs text-ui-muted">&copy; {{ date('Y') }} KuponGo &mdash; Capstone Kelompok 38</p>
    </footer>

</body>
</html>
