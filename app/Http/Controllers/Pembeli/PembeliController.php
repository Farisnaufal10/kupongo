<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PembeliController extends Controller
{
    /**
     * Tampilkan halaman utama pembeli (daftar tenant dan menu pilihan).
     *
     * Hanya tenant dengan is_active = true yang ditampilkan.
     * Mendukung parameter pencarian ?q= untuk mencari tenant atau menu.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $tenantsQuery = Tenant::where('is_active', true)
            ->withCount(['menus as menu_tersedia_count' => function ($query) {
                $query->where('is_active', true)->where('is_tersedia', true);
            }])
            ->orderBy('nama_tenant');

        if ($search !== '') {
            $tenantsQuery->where(function ($q) use ($search) {
                $q->where('nama_tenant', 'like', "%{$search}%")
                  ->orWhereHas('menus', function ($mq) use ($search) {
                      $mq->where('is_active', true)
                         ->where('nama_menu', 'like', "%{$search}%");
                  });
            });
        }

        $tenants = $tenantsQuery->get();

        // Menu pilihan / populer dari tenant yang aktif
        $menusQuery = Menu::where('is_active', true)
            ->whereHas('tenant', function ($tq) {
                $tq->where('is_active', true);
            })
            ->with('tenant')
            ->orderByRaw('is_tersedia DESC')
            ->orderBy('nama_menu');

        if ($search !== '') {
            $menusQuery->where('nama_menu', 'like', "%{$search}%");
        }

        $featuredMenus = $menusQuery->limit(8)->get();

        return view('pembeli.index', compact('tenants', 'featuredMenus', 'search'));
    }

    /**
     * Tampilkan katalog menu untuk satu tenant.
     *
     * Hanya menu dengan is_active = true yang dimuat.
     * Jika tenant tidak aktif atau tidak ditemukan, kembalikan 404.
     */
    public function showTenant(int $id_tenant, Request $request)
    {
        $tenant = Tenant::where('id_tenant', $id_tenant)
            ->where('is_active', true)
            ->firstOrFail();

        $search = trim((string) $request->query('q', ''));

        $menusQuery = $tenant->menus()
            ->where('is_active', true)
            ->orderByRaw('is_tersedia DESC')
            ->orderBy('nama_menu');

        if ($search !== '') {
            $menusQuery->where('nama_menu', 'like', "%{$search}%");
        }

        $menus = $menusQuery->get();
        $tenantBuka = $tenant->isBuka();

        return view('pembeli.tenant', compact('tenant', 'menus', 'tenantBuka', 'search'));
    }
}
