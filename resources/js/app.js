import './bootstrap';

/**
 * KuponGo — Cart JS (Sprint 2)
 * Menangani aksi keranjang secara AJAX dari halaman katalog menu.
 * Tombol .add-to-cart-btn mengirim POST ke /keranjang/tambah.
 * Badge keranjang di header diperbarui tanpa reload halaman.
 */

const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Tampilkan toast notifikasi ringkas di bawah layar.
 * @param {string} message
 * @param {'success'|'error'} type
 */
function showToast(message, type = 'success') {
    // Hapus toast lama
    document.querySelectorAll('.kg-toast').forEach(el => el.remove());

    const toast = document.createElement('div');
    toast.className = [
        'kg-toast',
        'fixed bottom-6 left-1/2 -translate-x-1/2 z-50',
        'max-w-[320px] w-[calc(100%-2rem)]',
        'flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-lg',
        'text-sm font-medium',
        'transition-all duration-300 opacity-0 translate-y-2',
        type === 'success'
            ? 'bg-brand-forest text-white'
            : 'bg-state-danger text-white',
    ].join(' ');

    // Ikon
    const iconSvg = type === 'success'
        ? '<polyline points="20 6 9 17 4 12"/>'
        : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>';

    toast.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            ${iconSvg}
        </svg>
        <span>${message}</span>
    `;

    document.body.appendChild(toast);

    // Animasi masuk
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
        });
    });

    // Hilang setelah 2.8 detik
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 2800);
}

/**
 * Perbarui badge jumlah keranjang di header.
 * @param {number} count
 */
function updateCartBadge(count) {
    const badge = document.getElementById('cart-badge');
    if (!badge) return;

    if (count <= 0) {
        badge.textContent = '';
        badge.classList.add('hidden');
    } else {
        badge.textContent = count > 99 ? '99+' : String(count);
        badge.classList.remove('hidden');
    }
}

// ---------------------------------------------------------------------------
// Tambah ke Keranjang (tombol .add-to-cart-btn)
// ---------------------------------------------------------------------------

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.add-to-cart-btn');
    if (!btn) return;

    // Cegah klik ganda
    if (btn.dataset.loading === 'true') return;
    btn.dataset.loading = 'true';

    const idMenu    = btn.dataset.menuId;
    const namaMenu  = btn.dataset.menuNama ?? 'menu';
    const originalHTML = btn.innerHTML;

    // State loading
    btn.innerHTML = `
        <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
            showToast(data.message ?? 'Gagal menambahkan item.', 'error');
        }
    } catch {
        showToast('Koneksi bermasalah. Coba lagi.', 'error');
    } finally {
        btn.innerHTML = originalHTML;
        btn.disabled  = false;
        delete btn.dataset.loading;
    }
});

// ---------------------------------------------------------------------------
// Keranjang — Kurangi (tombol .cart-decrease-btn)
// ---------------------------------------------------------------------------

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cart-decrease-btn');
    if (!btn || btn.dataset.loading === 'true') return;
    btn.dataset.loading = 'true';

    const idMenu  = Number(btn.dataset.menuId);
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
                // Hapus baris item dari DOM
                document.querySelector(`.cart-item-row[data-menu-id="${idMenu}"]`)?.remove();
                checkEmptyGroup(idMenu);
            } else {
                if (jumlahEl) jumlahEl.textContent = jumlahBaru;
                refreshSubtotal(idMenu, jumlahBaru);
            }
            updateGrandTotal(data.grand_total);
        }
    } finally {
        delete btn.dataset.loading;
    }
});

// ---------------------------------------------------------------------------
// Keranjang — Tambah (tombol .cart-increase-btn)
// ---------------------------------------------------------------------------

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cart-increase-btn');
    if (!btn || btn.dataset.loading === 'true') return;
    btn.dataset.loading = 'true';

    const idMenu  = Number(btn.dataset.menuId);
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
            refreshSubtotal(idMenu, jumlahBaru);
            updateGrandTotal(data.grand_total);
        } else {
            showToast(data.message, 'error');
        }
    } finally {
        delete btn.dataset.loading;
    }
});

// ---------------------------------------------------------------------------
// Keranjang — Hapus Item (tombol .cart-hapus-btn)
// ---------------------------------------------------------------------------

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
            checkEmptyGroup(idMenu);
            updateGrandTotal(data.grand_total);
            if (data.is_empty) {
                showEmptyState();
            }
        }
    } finally {
        delete btn.dataset.loading;
    }
});

// ---------------------------------------------------------------------------
// Keranjang — Update Catatan (textarea .cart-catatan)
// ---------------------------------------------------------------------------

document.addEventListener('change', async (e) => {
    const textarea = e.target.closest('.cart-catatan');
    if (!textarea) return;

    const idMenu  = Number(textarea.dataset.menuId);
    const catatan = textarea.value;

    await fetch('/keranjang/catatan', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF(),
            'Accept': 'application/json',
        },
        body: JSON.stringify({ id_menu: idMenu, catatan }),
    });
    // Tidak perlu umpan balik visual untuk autosave catatan
});

// ---------------------------------------------------------------------------
// DOM Helpers — Keranjang
// ---------------------------------------------------------------------------

function formatRupiah(amount) {
    return 'Rp\u00a0' + Number(amount).toLocaleString('id-ID', { minimumFractionDigits: 0 });
}

/** Perbarui tampilan subtotal satu item */
function refreshSubtotal(idMenu, jumlahBaru) {
    const row     = document.querySelector(`.cart-item-row[data-menu-id="${idMenu}"]`);
    if (!row) return;
    const harga   = Number(row.dataset.harga);
    const subEl   = row.querySelector('.cart-item-subtotal');
    if (subEl) subEl.textContent = formatRupiah(harga * jumlahBaru);

    // Perbarui subtotal grup tenant
    const groupEl = row.closest('.cart-tenant-group');
    if (!groupEl) return;
    let subtotal = 0;
    groupEl.querySelectorAll('.cart-item-row').forEach(r => {
        const h = Number(r.dataset.harga);
        const j = Number(r.querySelector('.cart-jumlah')?.textContent ?? 1);
        subtotal += h * j;
    });
    const subTenantEl = groupEl.querySelector('.cart-subtotal-tenant');
    if (subTenantEl) subTenantEl.textContent = formatRupiah(subtotal);
}

/** Perbarui grand total di footer keranjang */
function updateGrandTotal(amount) {
    const el = document.getElementById('cart-grand-total');
    if (el) el.textContent = formatRupiah(amount);
}

/** Jika grup tenant sudah kosong, hapus grupnya dari DOM */
function checkEmptyGroup(idMenu) {
    const row = document.querySelector(`.cart-item-row[data-menu-id="${idMenu}"]`);
    // row sudah dihapus sebelum dipanggil — cari grup yang tidak punya lagi .cart-item-row
    document.querySelectorAll('.cart-tenant-group').forEach(group => {
        if (group.querySelectorAll('.cart-item-row').length === 0) {
            group.remove();
        }
    });
}

/** Tampilkan empty state keranjang secara dinamis */
function showEmptyState() {
    const main = document.getElementById('cart-container');
    if (!main) return;
    main.innerHTML = `
        <div class="flex flex-col items-center justify-center py-20 text-center">
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
            <a href="/menu" class="mt-6 inline-flex items-center gap-2 bg-brand-forest hover:bg-brand-forest-hover text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                Pilih Menu
            </a>
        </div>
    `;
    // Sembunyikan footer total
    document.getElementById('cart-footer')?.classList.add('hidden');
}
