<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#103B2C">
    <title>@yield('title', 'Panel Pengelola') — KuponGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-full bg-brand-ivory font-sans antialiased text-ui-text">
@php
    $currentUser = auth()->user();
    $displayName = $currentUser?->pengelola?->nama ?? $currentUser?->name ?? $currentUser?->username ?? 'Pengelola';

    $navItems = [
        [
            'label' => 'Dashboard',
            'route' => 'pengelola.dashboard',
            'active' => request()->routeIs('pengelola.dashboard'),
            'icon' => 'dashboard',
        ],
        [
            'label' => 'Kelola Tenant',
            'route' => 'pengelola.tenants.index',
            'active' => request()->routeIs('pengelola.tenants.*'),
            'icon' => 'tenant',
        ],
        [
            'label' => 'Kelola Meja & QR',
            'route' => 'pengelola.meja.index',
            'active' => request()->routeIs('pengelola.meja.*'),
            'icon' => 'meja',
        ],
    ];
@endphp

<div class="min-h-screen flex flex-col lg:flex-row">

    {{-- ================================================================== --}}
    {{-- Mobile Top Bar (hanya tampil di layar < lg)                         --}}
    {{-- ================================================================== --}}
    <header class="lg:hidden sticky top-0 z-30 bg-brand-forest text-white border-b border-brand-gold/30">
        <div class="h-16 px-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <button
                    type="button"
                    id="sidebar-open-btn"
                    aria-label="Buka menu navigasi"
                    aria-controls="pengelola-sidebar"
                    aria-expanded="false"
                    class="inline-flex items-center justify-center w-11 h-11 rounded-xl border border-white/20 text-white hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold cursor-pointer"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="min-w-0">
                    <span class="block text-sm font-bold leading-tight tracking-tight truncate">KuponGo</span>
                    <span class="block text-[11px] text-brand-gold font-medium leading-tight truncate">Panel Pengelola</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center justify-center min-h-[44px] px-3.5 rounded-xl border border-white/20 text-xs font-semibold text-white hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold cursor-pointer"
                >
                    Keluar
                </button>
            </form>
        </div>
    </header>

    {{-- Backdrop mobile drawer --}}
    <div
        id="sidebar-backdrop"
        class="fixed inset-0 z-40 bg-black/45 hidden lg:hidden"
        aria-hidden="true"
    ></div>

    {{-- ================================================================== --}}
    {{-- Sidebar Administrasi Pengelola (Desktop tetap, Mobile drawer)       --}}
    {{-- ================================================================== --}}
    <aside
        id="pengelola-sidebar"
        aria-label="Navigasi Panel Pengelola"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-brand-forest text-white flex flex-col transform -translate-x-full transition-transform duration-200 ease-out lg:translate-x-0 lg:static lg:inset-auto lg:shrink-0 border-r border-brand-gold/25"
    >
        {{-- Identitas Brand --}}
        <div class="h-16 px-5 flex items-center justify-between border-b border-white/10">
            <a href="{{ route('pengelola.dashboard') }}" class="flex items-center gap-3 min-w-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold rounded-xl py-1">
                <span class="shrink-0 w-9 h-9 rounded-xl bg-brand-gold/20 border border-brand-gold/40 flex items-center justify-center" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 9V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v2"/>
                        <path d="M2 15v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2"/>
                        <path d="M22 9a3 3 0 0 1 0 6"/>
                        <path d="M2 9a3 3 0 0 0 0 6"/>
                    </svg>
                </span>
                <div class="min-w-0">
                    <span class="block text-base font-bold leading-tight tracking-tight">KuponGo</span>
                    <span class="block text-[11px] text-brand-gold font-medium leading-tight">Sentra Kuliner D&rsquo;Kupon</span>
                </div>
            </a>

            <button
                type="button"
                id="sidebar-close-btn"
                aria-label="Tutup menu navigasi"
                class="lg:hidden inline-flex items-center justify-center w-11 h-11 rounded-xl text-white/80 hover:text-white hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold cursor-pointer"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigasi Utama --}}
        <nav class="flex-1 px-4 py-5 space-y-1.5 overflow-y-auto">
            <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-white/55">
                Administrasi
            </p>

            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    @if ($item['active']) aria-current="page" @endif
                    class="flex items-center gap-3 min-h-[46px] px-3.5 rounded-xl text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold {{ $item['active'] ? 'bg-white text-brand-forest shadow-xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}"
                >
                    @if ($item['icon'] === 'dashboard')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 {{ $item['active'] ? 'text-brand-forest' : 'text-brand-gold' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                        </svg>
                    @elseif ($item['icon'] === 'tenant')
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 {{ $item['active'] ? 'text-brand-forest' : 'text-brand-gold' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l1.5-5h15L21 9M4 9h16v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9zm5 4h6"/>
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 {{ $item['active'] ? 'text-brand-forest' : 'text-brand-gold' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h2m4 0h-2m0-3v6"/>
                        </svg>
                    @endif
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach

            <div class="pt-4 mt-4 border-t border-white/10">
                <a
                    href="{{ route('pembeli.index') }}"
                    class="flex items-center gap-3 min-h-[44px] px-3.5 rounded-xl text-xs font-medium text-white/75 hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 text-brand-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>Lihat Halaman Pembeli</span>
                </a>
            </div>
        </nav>

        {{-- Profil Pengelola & Logout --}}
        <div class="p-4 border-t border-white/10 space-y-3">
            <div class="px-2">
                <p class="text-xs text-white/60">Masuk sebagai Pengelola</p>
                <p class="text-sm font-semibold text-white truncate">{{ $displayName }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full min-h-[44px] px-4 rounded-xl border border-white/25 text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold transition-colors cursor-pointer"
                >
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ================================================================== --}}
    {{-- Area Konten Utama                                                   --}}
    {{-- ================================================================== --}}
    <div class="flex-1 min-w-0 flex flex-col">
        {{-- Header halaman desktop --}}
        <div class="bg-surface-default border-b border-ui-border">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold text-ui-text leading-snug">
                        @yield('page_title', 'Panel Pengelola')
                    </h1>
                    @hasSection('page_subtitle')
                        <p class="mt-1 text-sm text-ui-muted">
                            @yield('page_subtitle')
                        </p>
                    @endif
                </div>
                @hasSection('page_actions')
                    <div class="shrink-0 flex flex-wrap items-center gap-2.5">
                        @yield('page_actions')
                    </div>
                @endif
            </div>
        </div>

        <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            {{-- Flash notifikasi sukses / error --}}
            @if (session('success'))
                <div role="status" class="rounded-2xl border border-state-success/30 bg-state-success-bg px-4 py-3.5 text-sm font-medium text-state-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="rounded-2xl border border-state-danger/30 bg-state-danger-bg px-4 py-3.5 text-sm font-medium text-state-danger">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5 border-t border-ui-border/70">
            <p class="text-xs text-ui-muted">&copy; {{ date('Y') }} KuponGo &mdash; Panel Administrasi Pengelola Sentra Kuliner D&rsquo;Kupon</p>
        </footer>
    </div>

</div>

<script>
    (function () {
        const sidebar = document.getElementById('pengelola-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const openBtn = document.getElementById('sidebar-open-btn');
        const closeBtn = document.getElementById('sidebar-close-btn');

        if (!sidebar || !backdrop || !openBtn) return;

        const openSidebar = () => {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            openBtn.setAttribute('aria-expanded', 'true');
        };

        const closeSidebar = () => {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
            openBtn.setAttribute('aria-expanded', 'false');
        };

        openBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        backdrop.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeSidebar();
        });
    })();
</script>
@stack('scripts')
</body>
</html>
