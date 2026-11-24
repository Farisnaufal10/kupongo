<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Services\Pembeli\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * CartController — Kelola aksi keranjang Modul Pembeli.
 *
 * Semua mutasi (tambah/kurangi/hapus/catatan) menerima request POST/PATCH/DELETE
 * dan mengembalikan JSON untuk permintaan AJAX, atau redirect untuk form biasa.
 * Halaman keranjang (GET /keranjang) dikembalikan sebagai view Blade.
 */
class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    // =========================================================================
    // GET /keranjang
    // =========================================================================

    /**
     * Tampilkan halaman keranjang.
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $groups     = $this->cart->groupedByTenant();
        $grandTotal = $this->cart->grandTotal();
        $isEmpty    = $this->cart->isEmpty();

        return view('pembeli.keranjang', compact('groups', 'grandTotal', 'isEmpty'));
    }

    // =========================================================================
    // POST /keranjang/tambah
    // =========================================================================

    /**
     * Tambahkan satu unit menu ke keranjang.
     * Request: { id_menu: int, catatan?: string }
     */
    public function tambah(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_menu' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string', 'max:200'],
        ]);

        $result = $this->cart->tambah(
            (int) $validated['id_menu'],
            $validated['catatan'] ?? null
        );

        return response()->json([
            'ok'          => $result['ok'],
            'message'     => $result['message'],
            'cart_count'  => $this->cart->totalUnit(),
        ], $result['ok'] ? 200 : 422);
    }

    // =========================================================================
    // PATCH /keranjang/jumlah
    // =========================================================================

    /**
     * Set jumlah item (0 = hapus).
     * Request: { id_menu: int, jumlah: int }
     */
    public function setJumlah(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_menu' => ['required', 'integer', 'min:1'],
            'jumlah'  => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $result = $this->cart->setJumlah(
            (int) $validated['id_menu'],
            (int) $validated['jumlah']
        );

        return response()->json([
            'ok'          => $result['ok'],
            'message'     => $result['message'],
            'cart_count'  => $this->cart->totalUnit(),
            'grand_total' => $this->cart->grandTotal(),
        ], $result['ok'] ? 200 : 422);
    }

    // =========================================================================
    // DELETE /keranjang/hapus
    // =========================================================================

    /**
     * Hapus satu item dari keranjang.
     * Request: { id_menu: int }
     */
    public function hapus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_menu' => ['required', 'integer', 'min:1'],
        ]);

        $result = $this->cart->hapus((int) $validated['id_menu']);

        return response()->json([
            'ok'          => $result['ok'],
            'message'     => $result['message'],
            'cart_count'  => $this->cart->totalUnit(),
            'grand_total' => $this->cart->grandTotal(),
            'is_empty'    => $this->cart->isEmpty(),
        ], $result['ok'] ? 200 : 422);
    }

    // =========================================================================
    // PATCH /keranjang/catatan
    // =========================================================================

    /**
     * Perbarui catatan khusus satu item.
     * Request: { id_menu: int, catatan: string }
     */
    public function updateCatatan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_menu' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string', 'max:200'],
        ]);

        $result = $this->cart->updateCatatan(
            (int) $validated['id_menu'],
            $validated['catatan'] ?? ''
        );

        return response()->json([
            'ok'      => $result['ok'],
            'message' => $result['message'],
        ], $result['ok'] ? 200 : 422);
    }

    // =========================================================================
    // DELETE /keranjang
    // =========================================================================

    /**
     * Kosongkan seluruh keranjang.
     */
    public function kosongkan(Request $request): JsonResponse|RedirectResponse
    {
        $this->cart->kosongkan();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Keranjang dikosongkan.']);
        }

        return redirect()->route('pembeli.keranjang')
            ->with('success', 'Keranjang berhasil dikosongkan.');
    }
}
