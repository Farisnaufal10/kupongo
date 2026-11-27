@extends('layouts.auth')

@section('title', 'Masuk Pengelola & Tenant')

@section('header_action')
    <a
        href="{{ route('pembeli.index') }}"
        class="inline-flex items-center justify-center min-h-[44px] px-3.5 rounded-xl border border-white/25 text-xs sm:text-sm font-medium text-white hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold transition-colors"
    >
        Menu Pembeli
    </a>
@endsection

@section('content')
    <div class="w-full max-w-md mx-auto">
        {{-- Kartu Login (corner radius 16px, border #E2E5DE, surface White #FFFFFF) --}}
        <div class="bg-surface-default border border-ui-border rounded-2xl shadow-sm overflow-hidden">
            {{-- Garis aksen Champagne Gold --}}
            <div class="h-1 bg-brand-gold" aria-hidden="true"></div>

            <div class="p-6 sm:p-8">
                <div class="mb-6">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-brand-gold/20 text-brand-forest text-xs font-semibold mb-3">
                        Portal Pengelola &amp; Tenant
                    </span>
                    <h1 class="text-2xl font-bold text-ui-text leading-snug">
                        Masuk ke KuponGo
                    </h1>
                    <p class="mt-1.5 text-sm text-ui-muted leading-relaxed">
                        Gunakan akun yang terdaftar oleh Pengelola Sentra Kuliner. Pembeli tidak perlu login untuk memesan makanan.
                    </p>
                </div>

                {{-- Notifikasi status (misal: berhasil logout) --}}
                @if (session('status'))
                    <div
                        role="status"
                        class="mb-5 rounded-xl border border-state-success/30 bg-state-success-bg px-4 py-3 text-sm text-state-success font-medium"
                    >
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Ringkasan galat validasi/autentikasi --}}
                @if ($errors->any())
                    <div
                        role="alert"
                        class="mb-5 rounded-xl border border-state-danger/30 bg-state-danger-bg px-4 py-3 text-sm text-state-danger"
                    >
                        <p class="font-semibold">Gagal masuk ke akun</p>
                        <p class="mt-0.5">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('login.submit') }}"
                    class="space-y-5"
                    novalidate
                    onsubmit="const btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.setAttribute('aria-busy', 'true'); btn.textContent = 'Memproses...'; }"
                >
                    @csrf

                    {{-- Field Username / Email --}}
                    <div>
                        <label for="username" class="block text-sm font-semibold text-ui-text mb-1.5">
                            Username atau Email
                        </label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Contoh: pengelola01 atau tenant@kupongo.id"
                            aria-describedby="username-help @error('username') username-error @enderror"
                            aria-invalid="@error('username') true @else false @enderror"
                            class="w-full h-12 px-3.5 rounded-xl bg-surface-default border {{ $errors->has('username') ? 'border-state-danger focus:ring-state-danger/30' : 'border-ui-border focus:border-brand-forest focus:ring-brand-forest/20' }} text-sm sm:text-base text-ui-text placeholder:text-ui-muted/70 focus:outline-none focus:ring-2 transition-colors"
                        >
                        @error('username')
                            <p id="username-error" class="mt-1.5 text-xs font-medium text-state-danger">
                                {{ $message }}
                            </p>
                        @else
                            <p id="username-help" class="mt-1.5 text-xs text-ui-muted">
                                Masukkan username atau alamat email yang terdaftar pada akun Anda.
                            </p>
                        @enderror
                    </div>

                    {{-- Field Password --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold text-ui-text mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="Masukkan kata sandi"
                                aria-describedby="@error('password') password-error @enderror"
                                aria-invalid="@error('password') true @else false @enderror"
                                style="padding-right: 3rem;"
                                class="w-full h-12 px-3.5 pr-12 rounded-xl bg-surface-default border {{ $errors->has('password') ? 'border-state-danger focus:ring-state-danger/30' : 'border-ui-border focus:border-brand-forest focus:ring-brand-forest/20' }} text-sm sm:text-base text-ui-text placeholder:text-ui-muted/70 focus:outline-none focus:ring-2 transition-colors"
                            >
                            <button
                                type="button"
                                id="toggle-password-btn"
                                aria-label="Tampilkan kata sandi"
                                aria-controls="password"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 my-auto mr-0.5 inline-flex items-center justify-center w-11 h-11 rounded-xl text-ui-muted hover:text-brand-forest focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-forest transition-colors cursor-pointer"
                            >
                                {{-- Ikon mata terbuka (tampil saat password disembunyikan) --}}
                                <svg id="icon-eye-open" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                {{-- Ikon mata dicoret (tampil saat password terlihat) --}}
                                <svg id="icon-eye-closed" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587A2.25 2.25 0 0 0 13.414 13.417M9.88 5.09A9.77 9.77 0 0 1 12 4.875c6 0 9.75 7.125 9.75 7.125a18.2 18.2 0 0 1-3.164 4.11M6.228 6.228A18.3 18.3 0 0 0 2.25 12s3.75 7.125 9.75 7.125a9.7 9.7 0 0 0 4.242-.975"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p id="password-error" class="mt-1.5 text-xs font-medium text-state-danger">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center justify-between gap-2">
                        <label for="remember" class="inline-flex items-center gap-2.5 min-h-[44px] cursor-pointer select-none">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                value="1"
                                @checked(old('remember'))
                                class="w-4 h-4 rounded border-ui-border text-brand-forest focus:ring-brand-forest"
                            >
                            <span class="text-sm text-ui-text">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    {{-- Tombol Submit Primary (Forest Green + teks putih, radius 12px, min-h 44px) --}}
                    <button
                        type="submit"
                        class="w-full min-h-[48px] px-4 rounded-xl bg-brand-forest hover:bg-brand-forest-hover active:bg-brand-forest-hover text-white text-sm sm:text-base font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-brand-forest disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                    >
                        Masuk
                    </button>
                </form>
            </div>

            {{-- Info Pembeli di bagian bawah kartu --}}
            <div class="px-6 sm:px-8 py-4 bg-brand-ivory/70 border-t border-ui-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <p class="text-xs text-ui-muted">
                    Ingin memesan makanan sebagai pembeli?
                </p>
                <a
                    href="{{ route('pembeli.index') }}"
                    class="inline-flex items-center text-xs font-semibold text-brand-forest hover:underline min-h-[44px] sm:min-h-0"
                >
                    Lihat Menu Tanpa Login &rarr;
                </a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggle-password-btn');
            const eyeOpen = document.getElementById('icon-eye-open');
            const eyeClosed = document.getElementById('icon-eye-closed');

            if (!passwordInput || !toggleBtn || !eyeOpen || !eyeClosed) return;

            toggleBtn.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';

                eyeOpen.classList.toggle('hidden', isHidden);
                eyeClosed.classList.toggle('hidden', !isHidden);
                toggleBtn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            });
        })();
    </script>
@endsection
