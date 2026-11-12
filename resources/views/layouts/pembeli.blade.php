<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#103B2C">
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
            <div class="flex items-center gap-2.5 min-w-0">
                {{-- Ikon kupon SVG --}}
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
            </div>

            {{-- Slot header kanan (opsional per halaman) --}}
            @hasSection('header_action')
                <div class="shrink-0">
                    @yield('header_action')
                </div>
            @endif

        </div>
    </header>

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
