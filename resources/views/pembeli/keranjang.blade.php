@extends('layouts.pembeli')

@section('title', 'Keranjang')
@section('subtitle', 'Keranjang belanja Anda')

@section('content')

    <div class="mb-5">
        <h2 class="text-xl font-bold text-ui-text">Keranjang</h2>
        <p class="text-sm text-ui-muted mt-0.5">Periksa pesanan sebelum checkout</p>
    </div>

    {{-- ============================================================ --}}
    {{-- Empty State                                                   --}}
    {{-- ============================================================ --}}
    @if($isEmpty)
        <div
            role="status"
            aria-label="Keranjang kosong"
            class="flex flex-col items-center justify-center py-20 text-center"
        >
            <span class="w-16 h-16 rounded-2xl bg-ui-border/40 flex items-center justify-center mb-4" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-ui-muted" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </span>
            <p class="text-base font-semibold text-ui-text">Keranjang kosong</p>
            <p class="text-sm text-ui-muted mt-1 max-w-[220px]">Tambahkan menu dari tenant terlebih dahulu.</p>
            <a
                href="{{ route('pembeli.index') }}"
                class="mt-6 inline-flex items-center gap-2
                       bg-brand-forest hover:bg-brand-forest-hover
                       text-white text-sm font-semibold
                       px-5 py-2.5 rounded-xl transition-colors duration-150
                       focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-forest"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
                Pilih Menu
            </a>
        </div>

    @else

        {{-- ============================================================ --}}
        {{-- Item Keranjang                                                --}}
        {{-- ============================================================ --}}
        <div id="cart-container">
            @foreach($groups as $group)
                {{-- ── Grup Tenant ── --}}
                <div
                    class="cart-tenant-group mb-4"
                    data-tenant-id="{{ $group['id_tenant'] }}"
                >
                    {{-- Header grup --}}
                    <div class="flex items-center gap-2 mb-2 px-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-ui-muted shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <h3 class="text-sm font-semibold text-ui-text truncate">
                            {{ $group['nama_tenant'] }}
                        </h3>
                    </div>

                    {{-- Kartu grup --}}
                    <div class="bg-surface-default rounded-2xl border border-ui-border overflow-hidden">
                        @foreach($group['items'] as $item)
                            {{-- ── Baris Item ── --}}
                            <article
                                class="cart-item-row px-4 py-4 border-b border-ui-border last:border-b-0"
                                data-menu-id="{{ $item['id_menu'] }}"
                                data-harga="{{ $item['harga'] }}"
                                aria-label="{{ $item['nama_menu'] }}, {{ $item['jumlah'] }} item"
                            >
                                {{-- Nama + hapus --}}
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <h4 class="font-semibold text-ui-text text-sm leading-snug line-clamp-2 flex-1">
                                        {{ $item['nama_menu'] }}
                                    </h4>
                                    <button
                                        type="button"
                                        class="cart-hapus-btn shrink-0 w-7 h-7 rounded-lg
                                               flex items-center justify-center
                                               text-ui-muted hover:text-state-danger hover:bg-state-danger-bg
                                               transition-colors duration-150"
                                        data-menu-id="{{ $item['id_menu'] }}"
                                        aria-label="Hapus {{ $item['nama_menu'] }} dari keranjang"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6"/><path d="M14 11v6"/>
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Harga satuan --}}
                                <p class="text-xs text-ui-muted mb-3">
                                    Rp&nbsp;{{ number_format($item['harga'], 0, ',', '.') }} / item
                                </p>

                                {{-- Kontrol jumlah + subtotal --}}
                                <div class="flex items-center justify-between gap-3">
                                    {{-- Stepper --}}
                                    <div class="flex items-center border border-ui-border rounded-xl overflow-hidden">
                                        <button
                                            type="button"
                                            class="cart-decrease-btn w-9 h-9 flex items-center justify-center
                                                   text-ui-muted hover:bg-brand-forest hover:text-white
                                                   transition-colors duration-150"
                                            data-menu-id="{{ $item['id_menu'] }}"
                                            aria-label="Kurangi jumlah {{ $item['nama_menu'] }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <line x1="5" y1="12" x2="19" y2="12"/>
                                            </svg>
                                        </button>
                                        <span
                                            class="cart-jumlah w-8 text-center text-sm font-bold text-ui-text select-none"
                                            data-menu-id="{{ $item['id_menu'] }}"
                                            aria-live="polite"
                                            aria-label="Jumlah: {{ $item['jumlah'] }}"
                                        >{{ $item['jumlah'] }}</span>
                                        <button
                                            type="button"
                                            class="cart-increase-btn w-9 h-9 flex items-center justify-center
                                                   text-ui-muted hover:bg-brand-forest hover:text-white
                                                   transition-colors duration-150"
                                            data-menu-id="{{ $item['id_menu'] }}"
                                            aria-label="Tambah jumlah {{ $item['nama_menu'] }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <line x1="12" y1="5" x2="12" y2="19"/>
                                                <line x1="5" y1="12" x2="19" y2="12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    {{-- Subtotal item --}}
                                    <span class="cart-item-subtotal text-sm font-bold text-brand-forest tabular-nums">
                                        Rp&nbsp;{{ number_format($item['harga'] * $item['jumlah'], 0, ',', '.') }}
                                    </span>
                                </div>

                                {{-- Catatan khusus --}}
                                <div class="mt-3">
                                    <label
                                        for="catatan-{{ $item['id_menu'] }}"
                                        class="block text-xs font-medium text-ui-muted mb-1"
                                    >
                                        Catatan (opsional)
                                    </label>
                                    <textarea
                                        id="catatan-{{ $item['id_menu'] }}"
                                        class="cart-catatan w-full text-xs text-ui-text
                                               border border-ui-border rounded-xl
                                               px-3 py-2 resize-none
                                               placeholder:text-ui-muted/60
                                               focus:outline-none focus:ring-2 focus:ring-brand-forest/30 focus:border-brand-forest
                                               transition-colors duration-150"
                                        rows="2"
                                        maxlength="200"
                                        placeholder="Contoh: tidak pedas, tanpa bawang…"
                                        data-menu-id="{{ $item['id_menu'] }}"
                                        aria-label="Catatan untuk {{ $item['nama_menu'] }}"
                                    >{{ $item['catatan'] }}</textarea>
                                </div>
                            </article>
                        @endforeach

                        {{-- Subtotal per tenant --}}
                        <div class="px-4 py-3 bg-brand-ivory/60 flex items-center justify-between gap-2">
                            <span class="text-xs font-medium text-ui-muted">Subtotal {{ $group['nama_tenant'] }}</span>
                            <span class="cart-subtotal-tenant text-sm font-bold text-ui-text tabular-nums">
                                Rp&nbsp;{{ number_format($group['subtotal'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ============================================================ --}}
        {{-- Footer — Grand Total + CTA                                    --}}
        {{-- ============================================================ --}}
        <div id="cart-footer" class="mt-4 space-y-3">
            {{-- Total keseluruhan --}}
            <div class="bg-surface-default rounded-2xl border border-ui-border px-4 py-4 flex items-center justify-between gap-2">
                <div>
                    <p class="text-xs text-ui-muted">Total Keseluruhan</p>
                    <p
                        id="cart-grand-total"
                        class="text-xl font-bold text-brand-forest tabular-nums"
                        aria-live="polite"
                        aria-label="Total: Rp {{ number_format($grandTotal, 0, ',', '.') }}"
                    >
                        Rp&nbsp;{{ number_format($grandTotal, 0, ',', '.') }}
                    </p>
                </div>
                <div class="shrink-0 w-10 h-10 rounded-xl bg-brand-forest/8 flex items-center justify-center" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-brand-forest" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
            </div>

            {{-- CTA Checkout (placeholder Sprint 3+) --}}
            <div
                class="bg-ui-border/30 rounded-2xl border border-ui-border px-4 py-4 text-center"
                role="note"
                aria-label="Checkout belum tersedia"
            >
                <p class="text-xs text-ui-muted font-medium">
                    Proses checkout akan tersedia setelah integrasi Payment Gateway (Sprint 3+).
                </p>
            </div>

            {{-- Kosongkan keranjang --}}
            <div class="flex justify-center pt-1">
                <form method="POST" action="{{ route('pembeli.keranjang.kosongkan') }}" id="form-kosongkan">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="text-xs font-medium text-ui-muted hover:text-state-danger transition-colors duration-150
                               focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-state-danger rounded"
                        onclick="return confirm('Kosongkan seluruh keranjang?')"
                    >
                        Kosongkan Keranjang
                    </button>
                </form>
            </div>
        </div>

    @endif

@endsection
