@extends('layouts.auth')

@section('title', 'Dashboard Tenant')

@section('header_action')
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button
            type="submit"
            class="inline-flex items-center justify-center min-h-[44px] px-4 rounded-xl border border-white/25 text-xs sm:text-sm font-semibold text-white hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold transition-colors cursor-pointer"
        >
            Keluar
        </button>
    </form>
@endsection

@section('content')
    <div class="w-full max-w-3xl mx-auto">
        <div class="bg-surface-default border border-ui-border rounded-2xl shadow-sm overflow-hidden">
            <div class="h-1 bg-brand-gold" aria-hidden="true"></div>
            <div class="p-6 sm:p-8 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-brand-gold/20 text-brand-forest text-xs font-semibold">
                            Role: Tenant
                        </span>
                        <h1 class="mt-2 text-2xl font-bold text-ui-text">
                            Dashboard Tenant
                        </h1>
                        <p class="mt-1 text-sm text-ui-muted">
                            {{ $tenant?->nama_tenant ?? $user?->name ?? $user?->username }}
                        </p>
                    </div>

                    @if ($tenant)
                        @if ($tenant->status_operasional === \App\Models\Tenant::STATUS_OPERASIONAL_BUKA)
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-state-success-bg text-state-success text-xs font-semibold">
                                Buka
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-state-danger-bg text-state-danger text-xs font-semibold">
                                Tutup
                            </span>
                        @endif
                    @endif
                </div>

                <div class="rounded-xl border border-ui-border bg-brand-ivory/60 p-4 text-sm text-ui-text">
                    Autentikasi dan isolasi akses Tenant aktif. Operasional menu, status buka/tutup, dan antrean pesanan akan dikelola pada modul Anggota 2 (Tenant).
                </div>
            </div>
        </div>
    </div>
@endsection
