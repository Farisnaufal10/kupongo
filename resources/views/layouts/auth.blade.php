<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#103B2C">
    <title>@yield('title', 'Masuk') — KuponGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-full bg-brand-ivory font-sans antialiased text-ui-text flex flex-col">

    {{-- Header ringan identitas KuponGo --}}
    <header class="bg-brand-forest text-white border-b border-brand-gold/30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('pembeli.index') }}" class="flex items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold rounded-xl py-1">
                <span class="shrink-0 w-9 h-9 rounded-xl bg-brand-gold/20 border border-brand-gold/40 flex items-center justify-center" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 9V7a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v2"/>
                        <path d="M2 15v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2"/>
                        <path d="M22 9a3 3 0 0 1 0 6"/>
                        <path d="M2 9a3 3 0 0 0 0 6"/>
                    </svg>
                </span>
                <div>
                    <span class="block text-base font-bold leading-tight tracking-tight">KuponGo</span>
                    <span class="block text-xs text-white/75 leading-tight">Sentra Kuliner D&rsquo;Kupon</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                @yield('header_action')
            </div>
        </div>
    </header>

    {{-- Konten utama --}}
    <main class="flex-1 flex flex-col justify-center px-4 py-8 sm:px-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="py-5 px-4 text-center">
        <p class="text-xs text-ui-muted">&copy; {{ date('Y') }} KuponGo &mdash; Sentra Kuliner D&rsquo;Kupon &middot; Kelompok 38</p>
    </footer>

</body>
</html>
