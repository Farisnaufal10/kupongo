@extends('layouts.pembeli')

@section('title', 'Keranjang Belanja')
@section('back_url', route('pembeli.index'))

@section('content')

    {{-- ============================================================ --}}
    {{-- A. Header Halaman                                             --}}
    {{-- ============================================================ --}}
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-extrabold text-ui-text tracking-tight">Keranjang Pesanan</h2>
            <p class="text-xs text-ui-muted mt-0.5">Periksa pesanan multi-tenant Anda</p>
        </div>
        @if(!$isEmpty)
            <span class="text-xs font-bold text-brand-forest bg-brand-forest/10 px-3 py-1 rounded-full">
                {{ count($groups) }} Tenant
            </span>
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- B. Empty State (Wajib untuk pengujian test)                   --}}
    {{-- ============================================================ --}}
    @if($isEmpty)
        <div role="status"
             aria-label="Keranjang kosong"
             class="flex flex-col items-center justify-center py-12 px-4 bg-surface-default rounded-3xl border border-ui-border text-center shadow-xs my-3">
            <div class="w-16 h-16 rounded-2xl bg-ui-border/40 text-ui-muted flex items-center justify-center mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>
            <h3 class="text-sm sm:text-base font-bold text-ui-text">Keranjang kosong</h3>
            <p class="text-xs text-ui-muted mt-1 max-w-[240px] leading-relaxed">
                Tambahkan menu dari tenant terlebih dahulu.
            </p>
            <a href="{{ route('pembeli.index') }}"
               class="mt-5 min-h-[44px] inline-flex items-center justify-center bg-brand-forest hover:bg-brand-forest-hover text-white text-xs font-bold px-6 py-2.5 rounded-full transition-all shadow-md active:scale-95">
                <span>Pilih Menu</span>
            </a>
        </div>
    @else

        {{-- ============================================================ --}}
        {{-- C. Daftar Item Keranjang Terkelompok per Tenant              --}}
        {{-- ============================================================ --}}
        <div id="cart-container" class="space-y-4">
            @foreach($groups as $group)
                {{-- Kartu Grup Tenant --}}
                <div class="cart-tenant-group bg-surface-default rounded-3xl border border-ui-border overflow-hidden shadow-xs"
                     data-tenant-id="{{ $group['id_tenant'] }}">

                    {{-- Header Tenant --}}
                    <div class="px-4 py-3 bg-brand-forest/5 border-b border-ui-border flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-lg bg-brand-forest text-white flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/>
                                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                                    <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/>
                                    <path d="M2 7h20"/>
                                </svg>
                            </span>
                            <h3 class="text-xs font-bold text-ui-text truncate">
                                {{ $group['nama_tenant'] }}
                            </h3>
                        </div>
                        <span class="text-xs font-semibold text-brand-forest bg-white px-2.5 py-0.5 rounded-full border border-ui-border">
                            Sub-Order Stand
                        </span>
                    </div>

                    {{-- Item Rows dalam Tenant --}}
                    <div class="divide-y divide-ui-border/70">
                        @foreach($group['items'] as $item)
                            <article class="cart-item-row p-4"
                                     data-menu-id="{{ $item['id_menu'] }}"
                                     data-harga="{{ $item['harga'] }}"
                                     aria-label="{{ $item['nama_menu'] }}">

                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-xs sm:text-sm text-ui-text leading-snug line-clamp-2">
                                            {{ $item['nama_menu'] }}
                                        </h4>
                                        <p class="text-xs text-ui-muted mt-0.5 font-medium">
                                            Rp&nbsp;{{ number_format($item['harga'], 0, ',', '.') }} / item
                                        </p>
                                    </div>

                                    {{-- Tombol Hapus (Min 44x44px touch target) --}}
                                    <button type="button"
                                            class="cart-hapus-btn shrink-0 min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-ui-muted hover:text-state-danger hover:bg-state-danger-bg transition-colors"
                                            data-menu-id="{{ $item['id_menu'] }}"
                                            aria-label="Hapus {{ $item['nama_menu'] }} dari keranjang">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6"/><path d="M14 11v6"/>
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Kontrol Stepper (+/-) dan Subtotal Item --}}
                                <div class="flex items-center justify-between gap-2 mt-3 pt-2">
                                    <div class="inline-flex items-center bg-brand-ivory border border-ui-border rounded-full p-1 shadow-2xs min-h-[44px]">
                                        <button type="button"
                                                class="cart-decrease-btn w-8 h-8 rounded-full flex items-center justify-center text-ui-text hover:bg-surface-default hover:shadow-2xs transition-all active:scale-90"
                                                data-menu-id="{{ $item['id_menu'] }}"
                                                aria-label="Kurangi jumlah {{ $item['nama_menu'] }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        </button>
                                        <span class="cart-jumlah w-8 text-center text-xs sm:text-sm font-extrabold text-ui-text tabular-nums"
                                              data-menu-id="{{ $item['id_menu'] }}"
                                              aria-live="polite">
                                            {{ $item['jumlah'] }}
                                        </span>
                                        <button type="button"
                                                class="cart-increase-btn w-8 h-8 rounded-full flex items-center justify-center text-ui-text hover:bg-surface-default hover:shadow-2xs transition-all active:scale-90"
                                                data-menu-id="{{ $item['id_menu'] }}"
                                                aria-label="Tambah jumlah {{ $item['nama_menu'] }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        </button>
                                    </div>

                                    <div class="text-right">
                                        <span class="text-xs text-ui-muted block">Subtotal</span>
                                        <span class="cart-item-subtotal text-xs sm:text-sm font-extrabold text-brand-forest tabular-nums">
                                            Rp&nbsp;{{ number_format($item['harga'] * $item['jumlah'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Catatan Khusus --}}
                                <div class="mt-2.5">
                                    <label for="catatan-{{ $item['id_menu'] }}" class="block text-xs font-semibold text-ui-muted mb-1">
                                        Catatan untuk Dapur (opsional)
                                    </label>
                                    <input type="text"
                                           id="catatan-{{ $item['id_menu'] }}"
                                           class="cart-catatan w-full min-h-[44px] text-xs text-ui-text bg-brand-ivory/50 border border-ui-border rounded-xl px-3.5 py-2 placeholder:text-ui-muted/60 focus:outline-none focus:ring-1 focus:ring-brand-forest focus:border-brand-forest transition-colors"
                                           maxlength="200"
                                           placeholder="Contoh: tidak pedas, kuah dipisah..."
                                           value="{{ $item['catatan'] }}"
                                           data-menu-id="{{ $item['id_menu'] }}"
                                           aria-label="Catatan untuk {{ $item['nama_menu'] }}">
                                </div>

                            </article>
                        @endforeach
                    </div>

                    {{-- Subtotal per Tenant Footer --}}
                    <div class="px-4 py-3 bg-brand-ivory/70 border-t border-ui-border flex items-center justify-between">
                        <span class="text-xs font-semibold text-ui-muted">Subtotal {{ $group['nama_tenant'] }}</span>
                        <span class="cart-subtotal-tenant text-xs sm:text-sm font-extrabold text-ui-text tabular-nums">
                            Rp&nbsp;{{ number_format($group['subtotal'], 0, ',', '.') }}
                        </span>
                    </div>

                </div>
            @endforeach
        </div>

        {{-- ============================================================ --}}
        {{-- D. Ringkasan & Total Keseluruhan                              --}}
        {{-- ============================================================ --}}
        <div id="cart-footer" class="mt-6 space-y-4">
            {{-- Grand Total Card --}}
            <div class="bg-surface-default rounded-3xl border border-ui-border p-4 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-ui-border/60">
                    <span class="text-xs font-medium text-ui-muted">Metode Pemesanan</span>
                    <span class="text-xs font-bold text-brand-forest">Makan di Tempat (Dine-in)</span>
                </div>
                <div class="flex items-center justify-between pt-3">
                    <div>
                        <span class="text-xs font-medium text-ui-muted block">Total Keseluruhan</span>
                        <span id="cart-grand-total"
                              class="text-xl sm:text-2xl font-extrabold text-brand-forest tabular-nums"
                              aria-live="polite">
                            Rp&nbsp;{{ number_format($grandTotal, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="w-11 h-11 rounded-2xl bg-brand-forest/10 text-brand-forest flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="14" x="2" y="5" rx="2"/>
                            <line x1="2" x2="22" y1="10" y2="10"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Informasi Alur Pembayaran / Checkout --}}
            <div class="bg-brand-forest/5 border border-brand-forest/15 rounded-2xl p-3.5 flex items-start gap-2.5 text-xs text-brand-forest">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5 text-brand-forest" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <p class="leading-relaxed text-xs">
                    Pesanan akan diteruskan ke masing-masing stand kuliner. Pembayaran menggunakan QRIS atau Tunai diproses pada tahap checkout (Sprint 3+ Anggota 4).
                </p>
            </div>

            {{-- Tombol Kosongkan Keranjang --}}
            <div class="flex justify-center pt-1 pb-4">
                <form method="POST" action="{{ route('pembeli.keranjang.kosongkan') }}" id="form-kosongkan">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="min-h-[44px] inline-flex items-center gap-1.5 text-xs font-bold text-ui-muted hover:text-state-danger transition-colors py-2 px-4 rounded-full hover:bg-state-danger-bg"
                            onclick="return confirm('Apakah Anda yakin ingin mengosongkan seluruh isi keranjang?')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        <span>Kosongkan Keranjang</span>
                    </button>
                </form>
            </div>
        </div>

    @endif

@endsection
