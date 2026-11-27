@extends('layouts.pengelola')

@section('title', 'Kelola Tenant')
@section('page_title', 'Kelola Tenant & Akun')
@section('page_subtitle', 'Pendaftaran gerai kuliner, pembaruan data, serta aktivasi atau penonaktifan akun tenant.')

@section('page_actions')
    <a
        href="{{ route('pengelola.tenants.create') }}"
        class="inline-flex items-center justify-center gap-2 min-h-[44px] px-4 rounded-xl bg-brand-forest hover:bg-brand-forest-hover text-white text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-brand-forest"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Tenant</span>
    </a>
@endsection

@section('content')
    {{-- Ringkasan singkat --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-default border border-ui-border rounded-2xl p-4">
            <p class="text-xs font-medium text-ui-muted">Total Tenant</p>
            <p class="mt-1 text-2xl font-bold text-ui-text tabular-nums">{{ $ringkasan['total'] }}</p>
        </div>
        <div class="bg-surface-default border border-ui-border rounded-2xl p-4">
            <p class="text-xs font-medium text-ui-muted">Tenant Aktif</p>
            <p class="mt-1 text-2xl font-bold text-state-success tabular-nums">{{ $ringkasan['aktif'] }}</p>
        </div>
        <div class="bg-surface-default border border-ui-border rounded-2xl p-4">
            <p class="text-xs font-medium text-ui-muted">Sedang Buka</p>
            <p class="mt-1 text-2xl font-bold text-brand-forest tabular-nums">{{ $ringkasan['buka'] }}</p>
        </div>
        <div class="bg-surface-default border border-ui-border rounded-2xl p-4">
            <p class="text-xs font-medium text-ui-muted">Dinonaktifkan</p>
            <p class="mt-1 text-2xl font-bold text-state-danger tabular-nums">{{ $ringkasan['nonaktif'] }}</p>
        </div>
    </div>

    {{-- Filter & Pencarian --}}
    <form method="GET" action="{{ route('pengelola.tenants.index') }}" class="bg-surface-default border border-ui-border rounded-2xl p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
            <label for="search-tenant" class="sr-only">Cari nama tenant atau username</label>
            <input
                id="search-tenant"
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari nama tenant, username, atau kontak..."
                class="w-full h-11 px-3.5 rounded-xl border border-ui-border bg-surface-default text-sm text-ui-text placeholder:text-ui-muted/70 focus:outline-none focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20"
            >
        </div>

        <div class="sm:w-48">
            <label for="status-tenant" class="sr-only">Filter status akun</label>
            <select
                id="status-tenant"
                name="status"
                class="w-full h-11 px-3.5 rounded-xl border border-ui-border bg-surface-default text-sm text-ui-text focus:outline-none focus:border-brand-forest focus:ring-2 focus:ring-brand-forest/20"
            >
                <option value="semua" @selected($statusFilter === 'semua')>Semua Status</option>
                <option value="aktif" @selected($statusFilter === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($statusFilter === 'nonaktif')>Nonaktif</option>
            </select>
        </div>

        <button
            type="submit"
            class="min-h-[44px] px-5 rounded-xl bg-brand-forest hover:bg-brand-forest-hover text-white text-sm font-semibold transition-colors cursor-pointer"
        >
            Filter
        </button>

        @if ($search !== '' || $statusFilter !== 'semua')
            <a
                href="{{ route('pengelola.tenants.index') }}"
                class="inline-flex items-center justify-center min-h-[44px] px-4 rounded-xl border border-ui-border bg-surface-default text-sm font-semibold text-ui-text hover:bg-brand-ivory transition-colors"
            >
                Reset
            </a>
        @endif
    </form>

    {{-- Tabel Daftar Tenant --}}
    <div class="bg-surface-default border border-ui-border rounded-2xl overflow-hidden shadow-xs">
        @if ($tenants->isEmpty())
            <div class="p-10 text-center">
                <div class="w-12 h-12 rounded-2xl bg-brand-ivory border border-ui-border mx-auto flex items-center justify-center text-ui-muted mb-3" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l1.5-5h15L21 9M4 9h16v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9z"/>
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-ui-text">Belum ada tenant ditemukan</h2>
                <p class="mt-1 text-sm text-ui-muted">
                    Tambahkan tenant baru atau ubah kata kunci pencarian untuk menampilkan data.
                </p>
                <a
                    href="{{ route('pengelola.tenants.create') }}"
                    class="mt-4 inline-flex items-center justify-center min-h-[44px] px-4 rounded-xl bg-brand-forest hover:bg-brand-forest-hover text-white text-sm font-semibold"
                >
                    Tambah Tenant Pertama
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-ui-border bg-brand-ivory/70 text-xs font-semibold uppercase tracking-wide text-ui-muted">
                            <th class="px-5 py-3.5">Tenant &amp; Akun</th>
                            <th class="px-4 py-3.5">Kontak</th>
                            <th class="px-4 py-3.5">Operasional</th>
                            <th class="px-4 py-3.5">Status Akun</th>
                            <th class="px-4 py-3.5 text-right">Riwayat Pesanan</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ui-border text-sm">
                        @foreach ($tenants as $tenant)
                            <tr class="hover:bg-brand-ivory/40 transition-colors">
                                <td class="px-5 py-4 align-top">
                                    <p class="font-semibold text-ui-text">{{ $tenant->nama_tenant }}</p>
                                    <p class="mt-0.5 text-xs text-ui-muted">
                                        Username: <span class="font-mono text-ui-text">{{ $tenant->user?->username ?? '-' }}</span>
                                    </p>
                                    @if ($tenant->deskripsi)
                                        <p class="mt-1 text-xs text-ui-muted line-clamp-1 max-w-xs">{{ $tenant->deskripsi }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top text-ui-text whitespace-nowrap">
                                    {{ $tenant->nomor_kontak ?: '—' }}
                                </td>
                                <td class="px-4 py-4 align-top whitespace-nowrap">
                                    @if ($tenant->status_operasional === \App\Models\Tenant::STATUS_OPERASIONAL_BUKA)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-state-success-bg text-state-success">
                                            Buka
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-state-danger-bg text-state-danger">
                                            Tutup
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top whitespace-nowrap">
                                    @if ($tenant->is_active)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-state-success-bg text-state-success">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-state-danger-bg text-state-danger">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top text-right tabular-nums text-ui-text whitespace-nowrap">
                                    {{ number_format($tenant->sub_orders_count, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 align-top text-right whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('pengelola.tenants.edit', $tenant) }}"
                                            class="inline-flex items-center justify-center min-h-[44px] px-3.5 rounded-xl border border-brand-forest text-brand-forest text-xs font-semibold hover:bg-brand-forest hover:text-white transition-colors"
                                        >
                                            Edit
                                        </a>
                                        <button
                                            type="button"
                                            data-toggle-modal
                                            data-action="{{ route('pengelola.tenants.toggle-status', $tenant) }}"
                                            data-tenant-name="{{ $tenant->nama_tenant }}"
                                            data-is-active="{{ $tenant->is_active ? '1' : '0' }}"
                                            data-orders-count="{{ $tenant->sub_orders_count }}"
                                            class="inline-flex items-center justify-center min-h-[44px] px-3.5 rounded-xl text-xs font-semibold transition-colors cursor-pointer {{ $tenant->is_active ? 'bg-state-danger-bg text-state-danger hover:bg-state-danger hover:text-white' : 'bg-state-success-bg text-state-success hover:bg-state-success hover:text-white' }}"
                                        >
                                            {{ $tenant->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ================================================================== --}}
    {{-- Modal Konfirmasi Aktivasi / Penonaktifan Tenant (Soft-Deactivation) --}}
    {{-- ================================================================== --}}
    <div
        id="tenant-status-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-tenant-title"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50"
    >
        <div class="w-full max-w-md bg-surface-default border border-ui-border rounded-[20px] shadow-lg overflow-hidden">
            <div class="h-1 bg-brand-gold" aria-hidden="true"></div>
            <div class="p-6 space-y-4">
                <h2 id="modal-tenant-title" class="text-lg font-bold text-ui-text">
                    Konfirmasi Perubahan Status Tenant
                </h2>
                <p id="modal-tenant-desc" class="text-sm text-ui-muted leading-relaxed"></p>
                <div id="modal-tenant-note" class="rounded-xl bg-brand-ivory border border-ui-border px-3.5 py-2.5 text-xs text-ui-text"></div>

                <form id="modal-tenant-form" method="POST" action="" class="pt-2 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                    @csrf
                    @method('PATCH')
                    <button
                        type="button"
                        id="modal-tenant-cancel"
                        class="min-h-[44px] px-4 rounded-xl border border-ui-border bg-surface-default text-sm font-semibold text-ui-text hover:bg-brand-ivory transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        id="modal-tenant-confirm"
                        class="min-h-[44px] px-5 rounded-xl text-sm font-semibold text-white transition-colors cursor-pointer"
                    >
                        Konfirmasi
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const modal = document.getElementById('tenant-status-modal');
        const titleEl = document.getElementById('modal-tenant-title');
        const descEl = document.getElementById('modal-tenant-desc');
        const noteEl = document.getElementById('modal-tenant-note');
        const formEl = document.getElementById('modal-tenant-form');
        const cancelBtn = document.getElementById('modal-tenant-cancel');
        const confirmBtn = document.getElementById('modal-tenant-confirm');

        if (!modal || !formEl) return;

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        document.querySelectorAll('[data-toggle-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const action = btn.getAttribute('data-action');
                const tenantName = btn.getAttribute('data-tenant-name');
                const isActive = btn.getAttribute('data-is-active') === '1';
                const ordersCount = Number(btn.getAttribute('data-orders-count') || '0');

                formEl.setAttribute('action', action);

                if (isActive) {
                    titleEl.textContent = 'Nonaktifkan Tenant?';
                    descEl.textContent = `Tenant "${tenantName}" beserta akun loginnya akan dinonaktifkan. Gerai akan otomatis ditutup dan tidak tampil di katalog pembeli.`;
                    noteEl.textContent = ordersCount > 0
                        ? `Soft-deactivation aman: ${ordersCount} riwayat sub-pesanan milik tenant ini tetap dipertahankan untuk laporan dan analitik.`
                        : 'Soft-deactivation aman: data profil tenant tetap tersimpan dan dapat diaktifkan kembali kapan saja.';
                    confirmBtn.textContent = 'Ya, Nonaktifkan';
                    confirmBtn.className = 'min-h-[44px] px-5 rounded-xl text-sm font-semibold text-white bg-state-danger hover:opacity-90 transition-opacity cursor-pointer';
                } else {
                    titleEl.textContent = 'Aktifkan Kembali Tenant?';
                    descEl.textContent = `Akun login dan profil tenant "${tenantName}" akan diaktifkan kembali agar dapat masuk ke dashboard dan menerima pesanan.`;
                    noteEl.textContent = 'Setelah diaktifkan, tenant dapat mengatur status operasional buka/tutup secara mandiri.';
                    confirmBtn.textContent = 'Ya, Aktifkan';
                    confirmBtn.className = 'min-h-[44px] px-5 rounded-xl text-sm font-semibold text-white bg-brand-forest hover:bg-brand-forest-hover transition-colors cursor-pointer';
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    })();
</script>
@endpush
