<?php

namespace App\Services\Pembeli;

use App\Models\Menu;
use App\Models\Tenant;
use Illuminate\Support\Facades\Session;

/**
 * CartService — Kelola keranjang belanja multi-tenant via Laravel Session.
 *
 * Struktur session 'keranjang':
 * [
 *   <id_menu> => [
 *     'id_menu'      => int,
 *     'id_tenant'    => int,
 *     'nama_tenant'  => string,
 *     'nama_menu'    => string,
 *     'harga'        => float,       // snapshot harga dari DB saat tambah
 *     'jumlah'       => int,
 *     'catatan'      => string|null,
 *   ],
 *   ...
 * ]
 *
 * Harga selalu diambil dari DB, bukan dari input browser.
 * Validasi dilakukan setiap kali item ditambahkan.
 */
class CartService
{
    private const SESSION_KEY = 'keranjang';

    // =========================================================================
    // Read
    // =========================================================================

    /**
     * Ambil seluruh isi keranjang mentah dari session.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * Hitung jumlah baris item di keranjang.
     */
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * Hitung total jumlah unit (semua item dijumlah).
     */
    public function totalUnit(): int
    {
        return array_sum(array_column($this->all(), 'jumlah'));
    }

    /**
     * Total harga keseluruhan keranjang.
     */
    public function grandTotal(): float
    {
        $total = 0.0;
        foreach ($this->all() as $item) {
            $total += $item['harga'] * $item['jumlah'];
        }
        return $total;
    }

    /**
     * Kelompokkan item keranjang per tenant.
     * Kembalikan array dengan kunci id_tenant:
     * [
     *   id_tenant => [
     *     'nama_tenant' => string,
     *     'items'       => [...],
     *     'subtotal'    => float,
     *   ]
     * ]
     *
     * @return array<int, array<string, mixed>>
     */
    public function groupedByTenant(): array
    {
        $groups = [];
        foreach ($this->all() as $item) {
            $tid = $item['id_tenant'];
            if (!isset($groups[$tid])) {
                $groups[$tid] = [
                    'id_tenant'   => $tid,
                    'nama_tenant' => $item['nama_tenant'],
                    'items'       => [],
                    'subtotal'    => 0.0,
                ];
            }
            $groups[$tid]['items'][$item['id_menu']] = $item;
            $groups[$tid]['subtotal'] += $item['harga'] * $item['jumlah'];
        }
        return $groups;
    }

    /**
     * Apakah keranjang kosong?
     */
    public function isEmpty(): bool
    {
        return empty($this->all());
    }

    // =========================================================================
    // Write
    // =========================================================================

    /**
     * Tambahkan satu unit menu ke keranjang.
     *
     * Validasi:
     * - Menu harus ada, aktif, dan tersedia.
     * - Tenant harus aktif dan sedang buka.
     * - Harga diambil dari DB.
     *
     * Kembalikan array ['ok' => bool, 'message' => string].
     *
     * @return array{ok: bool, message: string}
     */
    public function tambah(int $id_menu, ?string $catatan = null): array
    {
        $menu = Menu::where('id_menu', $id_menu)
            ->where('is_active', true)
            ->where('is_tersedia', true)
            ->with('tenant')
            ->first();

        if (!$menu) {
            return ['ok' => false, 'message' => 'Menu tidak tersedia atau sudah habis.'];
        }

        $tenant = $menu->tenant;

        if (!$tenant || !$tenant->is_active) {
            return ['ok' => false, 'message' => 'Tenant tidak aktif.'];
        }

        if (!$tenant->isBuka()) {
            return ['ok' => false, 'message' => 'Tenant sedang tutup, pemesanan tidak dapat dilakukan.'];
        }

        $keranjang = $this->all();

        if (isset($keranjang[$id_menu])) {
            $keranjang[$id_menu]['jumlah'] += 1;
            // Perbarui catatan jika dikirim ulang
            if ($catatan !== null) {
                $keranjang[$id_menu]['catatan'] = $catatan;
            }
        } else {
            $keranjang[$id_menu] = [
                'id_menu'     => $menu->id_menu,
                'id_tenant'   => $tenant->id_tenant,
                'nama_tenant' => $tenant->nama_tenant,
                'nama_menu'   => $menu->nama_menu,
                'harga'       => (float) $menu->harga,
                'jumlah'      => 1,
                'catatan'     => $catatan ?? '',
            ];
        }

        Session::put(self::SESSION_KEY, $keranjang);

        return ['ok' => true, 'message' => 'Menu ditambahkan ke keranjang.'];
    }

    /**
     * Set jumlah item secara langsung.
     * Jumlah 0 akan menghapus item.
     *
     * @return array{ok: bool, message: string}
     */
    public function setJumlah(int $id_menu, int $jumlah): array
    {
        if ($jumlah < 0) {
            return ['ok' => false, 'message' => 'Jumlah tidak valid.'];
        }

        $keranjang = $this->all();

        if (!isset($keranjang[$id_menu])) {
            return ['ok' => false, 'message' => 'Item tidak ditemukan di keranjang.'];
        }

        if ($jumlah === 0) {
            return $this->hapus($id_menu);
        }

        // Batas wajar per item
        if ($jumlah > 99) {
            return ['ok' => false, 'message' => 'Jumlah maksimal per item adalah 99.'];
        }

        $keranjang[$id_menu]['jumlah'] = $jumlah;
        Session::put(self::SESSION_KEY, $keranjang);

        return ['ok' => true, 'message' => 'Jumlah diperbarui.'];
    }

    /**
     * Kurangi satu unit item. Jika jumlah mencapai 0, hapus item.
     *
     * @return array{ok: bool, message: string}
     */
    public function kurangi(int $id_menu): array
    {
        $keranjang = $this->all();

        if (!isset($keranjang[$id_menu])) {
            return ['ok' => false, 'message' => 'Item tidak ditemukan di keranjang.'];
        }

        $keranjang[$id_menu]['jumlah'] -= 1;

        if ($keranjang[$id_menu]['jumlah'] <= 0) {
            unset($keranjang[$id_menu]);
        }

        Session::put(self::SESSION_KEY, $keranjang);

        return ['ok' => true, 'message' => 'Jumlah dikurangi.'];
    }

    /**
     * Hapus satu item dari keranjang.
     *
     * @return array{ok: bool, message: string}
     */
    public function hapus(int $id_menu): array
    {
        $keranjang = $this->all();

        if (!isset($keranjang[$id_menu])) {
            return ['ok' => false, 'message' => 'Item tidak ditemukan di keranjang.'];
        }

        unset($keranjang[$id_menu]);
        Session::put(self::SESSION_KEY, $keranjang);

        return ['ok' => true, 'message' => 'Item dihapus dari keranjang.'];
    }

    /**
     * Perbarui catatan khusus untuk satu item.
     *
     * @return array{ok: bool, message: string}
     */
    public function updateCatatan(int $id_menu, string $catatan): array
    {
        $keranjang = $this->all();

        if (!isset($keranjang[$id_menu])) {
            return ['ok' => false, 'message' => 'Item tidak ditemukan di keranjang.'];
        }

        $keranjang[$id_menu]['catatan'] = $catatan;
        Session::put(self::SESSION_KEY, $keranjang);

        return ['ok' => true, 'message' => 'Catatan diperbarui.'];
    }

    /**
     * Kosongkan seluruh keranjang.
     */
    public function kosongkan(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
