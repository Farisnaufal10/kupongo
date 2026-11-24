import './bootstrap';

/**
 * KuponGo — Mobile Redesign JS
 * Menangani interaksi keranjang AJAX, real-time instant search,
 * quick filter pills, toast notifications, dan modal navigasi bawah.
 */

const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ---------------------------------------------------------------------------
// 1. Toast Notification Helper
// ---------------------------------------------------------------------------

function showToast(message, type = 'success') {
    document.querySelectorAll('.kg-toast').forEach(el => el.remove());

    const toast = document.createElement('div');
    toast.className = [
        'kg-toast',
        'fixed bottom-20 left-1/2 -translate-x-1/2 z-50',
        'max-w-[340px] w-[calc(100%-2.5rem)]',
        'flex items-center gap-2.5 px-4 py-3 rounded-2xl shadow-xl',
        'text-xs font-bold tracking-tight',
        'transition-all duration-300 opacity-0 translate-y-3',
        type === 'success'
            ? 'bg-brand-forest text-white border border-brand-gold/40'
            : 'bg-state-danger text-white border border-white/20',
    ].join(' ');

    const iconSvg = type === 'success'
        ? '<polyline points="20 6 9 17 4 12"/>'
        : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>';

    toast.innerHTML = `
        <span class="w-6 h-6 rounded-full ${type === 'success' ? 'bg-brand-gold/20 text-brand-gold' : 'bg-white/20 text-white'} flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.8"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                ${iconSvg}
            </svg>
        </span>
        <span class="truncate flex-1 leading-tight">${message}</span>
    `;

    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-3');
        });
    });

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-3');
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}

// ---------------------------------------------------------------------------
// 2. Badge Keranjang Header & Navigasi Bawah
// ---------------------------------------------------------------------------

function updateCartBadge(count) {
    const badges = [
        document.getElementById('cart-badge'),
        document.getElementById('nav-cart-badge'),
    ];

    badges.forEach(badge => {
        if (!badge) return;
        if (count <= 0) {
            badge.textContent = '';
            badge.classList.add('hidden');
        } else {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.classList.remove('hidden');
        }
    });
}

function formatRupiah(amount) {
    return 'Rp\u00a0' + Number(amount).toLocaleString('id-ID', { minimumFractionDigits: 0 });
}

// ---------------------------------------------------------------------------
// 3. Tambah ke Keranjang AJAX (Tombol .add-to-cart-btn)
// ---------------------------------------------------------------------------

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.add-to-cart-btn');
    if (!btn || btn.dataset.loading === 'true') return;
    btn.dataset.loading = 'true';

    const idMenu = btn.dataset.menuId;
    const namaMenu = btn.dataset.menuNama ?? 'Menu';
    const originalContent = btn.innerHTML;

    btn.innerHTML = `
        <svg class="w-3.5 h-3.5 animate-spin mx-auto text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
    `;
    btn.disabled = true;

    try {
        const res = await fetch('/keranjang/tambah', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ id_menu: Number(idMenu) }),
        });

        const data = await res.json();

        if (data.ok) {
            updateCartBadge(data.cart_count);
            showToast(`${namaMenu} ditambahkan ke keranjang`, 'success');
        } else {
            showToast(data.message ?? 'Gagal menambahkan menu.', 'error');
        }
    } catch {
        showToast('Koneksi bermasalah. Silakan coba lagi.', 'error');
    } finally {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        delete btn.dataset.loading;
    }
});

// ---------------------------------------------------------------------------
// 4. Interaksi Halaman Keranjang (Stepper, Hapus, Catatan)
// ---------------------------------------------------------------------------

// Kurangi Jumlah
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cart-decrease-btn');
    if (!btn || btn.dataset.loading === 'true') return;
    btn.dataset.loading = 'true';

    const idMenu = Number(btn.dataset.menuId);
    const jumlahEl = document.querySelector(`.cart-jumlah[data-menu-id="${idMenu}"]`);
    const jumlahSaat = jumlahEl ? Number(jumlahEl.textContent) : 1;
    const jumlahBaru = jumlahSaat - 1;

    try {
        const res = await fetch('/keranjang/jumlah', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ id_menu: idMenu, jumlah: jumlahBaru }),
        });
        const data = await res.json();

        if (data.ok) {
            updateCartBadge(data.cart_count);
            if (jumlahBaru <= 0) {
                document.querySelector(`.cart-item-row[data-menu-id="${idMenu}"]`)?.remove();
                checkAndCleanupEmptyGroups();
            } else {
                if (jumlahEl) jumlahEl.textContent = jumlahBaru;
                refreshItemSubtotal(idMenu, jumlahBaru);
            }
            updateGrandTotalDisplay(data.grand_total);
            if (data.cart_count === 0) renderDynamicEmptyCart();
        }
    } finally {
        delete btn.dataset.loading;
    }
});

// Tambah Jumlah
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cart-increase-btn');
    if (!btn || btn.dataset.loading === 'true') return;
    btn.dataset.loading = 'true';

    const idMenu = Number(btn.dataset.menuId);
    const jumlahEl = document.querySelector(`.cart-jumlah[data-menu-id="${idMenu}"]`);
    const jumlahSaat = jumlahEl ? Number(jumlahEl.textContent) : 1;
    const jumlahBaru = Math.min(jumlahSaat + 1, 99);

    try {
        const res = await fetch('/keranjang/jumlah', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ id_menu: idMenu, jumlah: jumlahBaru }),
        });
        const data = await res.json();

        if (data.ok) {
            updateCartBadge(data.cart_count);
            if (jumlahEl) jumlahEl.textContent = jumlahBaru;
            refreshItemSubtotal(idMenu, jumlahBaru);
            updateGrandTotalDisplay(data.grand_total);
        } else {
            showToast(data.message, 'error');
        }
    } finally {
        delete btn.dataset.loading;
    }
});

// Hapus Item
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cart-hapus-btn');
    if (!btn || btn.dataset.loading === 'true') return;
    btn.dataset.loading = 'true';

    const idMenu = Number(btn.dataset.menuId);

    try {
        const res = await fetch('/keranjang/hapus', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ id_menu: idMenu }),
        });
        const data = await res.json();

        if (data.ok) {
            updateCartBadge(data.cart_count);
            document.querySelector(`.cart-item-row[data-menu-id="${idMenu}"]`)?.remove();
            checkAndCleanupEmptyGroups();
            updateGrandTotalDisplay(data.grand_total);
            if (data.is_empty) {
                renderDynamicEmptyCart();
            }
        }
    } finally {
        delete btn.dataset.loading;
    }
});

// Update Catatan (Autosave saat input blur atau change)
document.addEventListener('change', async (e) => {
    const input = e.target.closest('.cart-catatan');
    if (!input) return;

    const idMenu = Number(input.dataset.menuId);
    const catatan = input.value;

    await fetch('/keranjang/catatan', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF(),
            'Accept': 'application/json',
        },
        body: JSON.stringify({ id_menu: idMenu, catatan }),
    });
});

function refreshItemSubtotal(idMenu, jumlah) {
    const row = document.querySelector(`.cart-item-row[data-menu-id="${idMenu}"]`);
    if (!row) return;

    const harga = Number(row.dataset.harga);
    const subEl = row.querySelector('.cart-item-subtotal');
    if (subEl) subEl.textContent = formatRupiah(harga * jumlah);

    const group = row.closest('.cart-tenant-group');
    if (!group) return;

    let tenantTotal = 0;
    group.querySelectorAll('.cart-item-row').forEach(r => {
        const h = Number(r.dataset.harga);
        const j = Number(r.querySelector('.cart-jumlah')?.textContent ?? 1);
        tenantTotal += h * j;
    });

    const tenantSubEl = group.querySelector('.cart-subtotal-tenant');
    if (tenantSubEl) tenantSubEl.textContent = formatRupiah(tenantTotal);
}

function updateGrandTotalDisplay(amount) {
    const el = document.getElementById('cart-grand-total');
    if (el) el.textContent = formatRupiah(amount);
}

function checkAndCleanupEmptyGroups() {
    document.querySelectorAll('.cart-tenant-group').forEach(group => {
        if (group.querySelectorAll('.cart-item-row').length === 0) {
            group.remove();
        }
    });
}

function renderDynamicEmptyCart() {
    const container = document.getElementById('cart-container');
    if (container) {
        container.innerHTML = `
            <div role="status" aria-label="Keranjang kosong" class="flex flex-col items-center justify-center py-12 px-4 bg-surface-default rounded-3xl border border-ui-border text-center shadow-xs my-3">
                <div class="w-16 h-16 rounded-2xl bg-ui-border/40 text-ui-muted flex items-center justify-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-ui-text">Keranjang kosong</h3>
                <p class="text-xs text-ui-muted mt-1 max-w-[240px]">Tambahkan menu dari tenant terlebih dahulu.</p>
                <a href="/menu" class="mt-5 min-h-[44px] inline-flex items-center gap-2 bg-brand-forest hover:bg-brand-forest-hover text-white text-xs font-bold px-5 py-2.5 rounded-full transition-all shadow-md">
                    <span>Pilih Menu</span>
                </a>
            </div>
        `;
    }
    document.getElementById('cart-footer')?.remove();
}

// ---------------------------------------------------------------------------
// 5. Instant Search & Quick Filter Chips
// ---------------------------------------------------------------------------

// Real-time search di index
const searchInput = document.getElementById('search-input');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        const tenantCards = document.querySelectorAll('.tenant-card');
        const menuCards = document.querySelectorAll('.menu-card');

        tenantCards.forEach(card => {
            const nama = card.dataset.nama || '';
            if (query === '' || nama.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        menuCards.forEach(card => {
            const nama = card.dataset.menuNama || '';
            if (query === '' || nama.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
}

// Real-time filter di tenant page
const filterMenuInput = document.getElementById('filter-menu-input');
if (filterMenuInput) {
    filterMenuInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        const menuCards = document.querySelectorAll('.menu-card');
        let visibleCount = 0;

        menuCards.forEach(card => {
            const nama = card.dataset.menuNama || '';
            if (query === '' || nama.includes(query)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const counter = document.getElementById('menu-counter');
        if (counter) {
            counter.textContent = `Menampilkan ${visibleCount} menu`;
        }
    });
}

// Filter Pills (Semua vs Buka)
document.addEventListener('click', (e) => {
    const pill = e.target.closest('.filter-pill-btn');
    if (!pill) return;

    const filter = pill.dataset.filter;
    document.querySelectorAll('.filter-pill-btn').forEach(btn => {
        btn.classList.remove('bg-brand-forest', 'text-white', 'shadow-xs');
        btn.classList.add('bg-surface-default', 'text-ui-text', 'border', 'border-ui-border');
        btn.setAttribute('aria-selected', 'false');
    });

    pill.classList.remove('bg-surface-default', 'text-ui-text', 'border', 'border-ui-border');
    pill.classList.add('bg-brand-forest', 'text-white', 'shadow-xs');
    pill.setAttribute('aria-selected', 'true');

    const tenantCards = document.querySelectorAll('.tenant-card');
    tenantCards.forEach(card => {
        const status = card.dataset.status;
        if (filter === 'buka') {
            card.style.display = status === 'buka' ? '' : 'none';
        } else {
            card.style.display = '';
        }
    });
});

// ---------------------------------------------------------------------------
// 6. Modal Status Pesanan
// ---------------------------------------------------------------------------

// Modal Status Pesanan
const btnNavPesanan = document.getElementById('btn-nav-pesanan');
const btnClosePesananModal = document.getElementById('btn-close-pesanan-modal');
const modalPesanan = document.getElementById('modal-pesanan');
const modalPesananContent = document.getElementById('modal-pesanan-content');

function togglePesananModal(open) {
    if (!modalPesanan || !modalPesananContent) return;
    if (open) {
        modalPesanan.classList.remove('opacity-0', 'pointer-events-none');
        modalPesananContent.classList.remove('scale-95');
        modalPesananContent.classList.add('scale-100');
    } else {
        modalPesanan.classList.add('opacity-0', 'pointer-events-none');
        modalPesananContent.classList.remove('scale-100');
        modalPesananContent.classList.add('scale-95');
    }
}

btnNavPesanan?.addEventListener('click', () => togglePesananModal(true));
btnClosePesananModal?.addEventListener('click', () => togglePesananModal(false));
modalPesanan?.addEventListener('click', (e) => {
    if (e.target === modalPesanan) togglePesananModal(false);
});
