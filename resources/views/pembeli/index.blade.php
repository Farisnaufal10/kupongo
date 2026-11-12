@extends('layouts.pembeli')

@section('title', 'Pilih Tenant')
@section('subtitle', 'Pilih tenant untuk melihat menu')

@section('content')

    {{-- ============================================================ --}}
    {{-- Page heading                                                   --}}
    {{-- ============================================================ --}}
    <div class="mb-5">
        <h2 class="text-xl font-bold text-ui-text">Tenant Tersedia</h2>
        <p class="text-sm text-ui-muted mt-0.5">Pilih tenant untuk melihat daftar menu</p>
    </div>

    {{-- ============================================================ --}}
    {{-- Daftar Tenant                                                 --}}
    {{-- ============================================================ --}}
    @if($tenants->isEmpty())

        {{-- ── Empty State ── --}}
        <div
            role="status"
            aria-label="Tidak ada tenant tersedia"
            class="flex flex-col items-center justify-center py-20 text-center"
        >
            {{-- Ikon toko SVG --}}
            <span class="w-16 h-16 rounded-2xl bg-ui-border/40 flex items-center justify-center mb-4" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-ui-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </span>
            <p class="text-base font-semibold text-ui-text">Belum ada tenant tersedia</p>
            <p class="text-sm text-ui-muted mt-1 max-w-[220px]">Silakan coba lagi nanti.</p>
        </div>

    @else

        <div class="grid grid-cols-1 gap-3" role="list" aria-label="Daftar tenant">
            @foreach($tenants as $tenant)
                @php $buka = $tenant->isBuka(); @endphp

                {{-- ── Tenant Card ── --}}
                <article
                    role="listitem"
                    class="bg-surface-default rounded-2xl border border-ui-border overflow-hidden
                           transition-shadow duration-200 hover:shadow-md
                           {{ $buka ? '' : 'opacity-70' }}"
                    aria-label="{{ $tenant->nama_tenant }}, status {{ $buka ? 'buka' : 'tutup' }}"
                >
                    {{-- Garis aksen atas (Gold) --}}
                    <div class="h-0.5 {{ $buka ? 'bg-brand-gold' : 'bg-ui-border' }}"></div>

                    <div class="p-4">
                        {{-- Baris atas: ikon + nama + badge --}}
                        <div class="flex items-start gap-3">

                            {{-- Ikon toko placeholder --}}
                            <div
                                class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center
                                       {{ $buka ? 'bg-brand-forest/8' : 'bg-ui-border/40' }}"
                                aria-hidden="true"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-5 h-5 {{ $buka ? 'text-brand-forest' : 'text-ui-muted' }}"
                                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                            </div>

                            {{-- Nama + meta --}}
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-ui-text text-base leading-tight truncate">
                                    {{ $tenant->nama_tenant }}
                                </h3>
                                <p class="text-xs text-ui-muted mt-0.5">
                                    {{ $tenant->menu_tersedia_count }}
                                    menu tersedia
                                </p>
                            </div>

                            {{-- Badge status --}}
                            @if($buka)
                                <span
                                    class="shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold
                                           bg-state-success-bg text-state-success
                                           px-2.5 py-1 rounded-full"
                                    aria-label="Status: Buka"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-state-success" aria-hidden="true"></span>
                                    Buka
                                </span>
                            @else
                                <span
                                    class="shrink-0 inline-flex items-center gap-1.5 text-xs font-semibold
                                           bg-state-danger-bg text-state-danger
                                           px-2.5 py-1 rounded-full"
                                    aria-label="Status: Tutup"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-state-danger" aria-hidden="true"></span>
                                    Tutup
                                </span>
                            @endif
                        </div>

                        {{-- Tombol Lihat Menu --}}
                        <div class="mt-4">
                            @if($buka)
                                <a
                                    href="{{ route('pembeli.tenant', $tenant->id_tenant) }}"
                                    class="flex w-full items-center justify-center gap-2
                                           bg-brand-forest hover:bg-brand-forest-hover
                                           text-white text-sm font-semibold
                                           py-2.5 rounded-xl
                                           transition-colors duration-150
                                           focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-forest"
                                    aria-label="Lihat menu {{ $tenant->nama_tenant }}"
                                >
                                    Lihat Menu
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M9 18l6-6-6-6"/>
                                    </svg>
                                </a>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    aria-disabled="true"
                                    class="flex w-full items-center justify-center gap-2
                                           bg-ui-border/60 text-ui-muted
                                           text-sm font-semibold
                                           py-2.5 rounded-xl
                                           cursor-not-allowed"
                                    aria-label="{{ $tenant->nama_tenant }} sedang tutup"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                    </svg>
                                    Sedang Tutup
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

    @endif

@endsection
