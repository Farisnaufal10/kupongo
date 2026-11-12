<?php

namespace App\Http\Controllers\Pembeli;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PembeliController extends Controller
{
    /**
     * Tampilkan daftar tenant yang aktif.
     *
     * Hanya tenant dengan is_active = true yang ditampilkan.
     * Status buka/tutup ditentukan oleh status_operasional dan is_active.
     */
    public function index()
    {
        $tenants = Tenant::where('is_active', true)
            ->withCount(['menus as menu_tersedia_count' => function ($query) {
                $query->where('is_active', true)->where('is_tersedia', true);
            }])
            ->orderBy('nama_tenant')
            ->get();

        return view('pembeli.index', compact('tenants'));
    }

    /**
     * Tampilkan katalog menu untuk satu tenant.
     *
     * Hanya menu dengan is_active = true yang dimuat.
     * Jika tenant tidak aktif atau tidak ditemukan, kembalikan 404.
     */
    public function showTenant(int $id_tenant)
    {
        $tenant = Tenant::where('id_tenant', $id_tenant)
            ->where('is_active', true)
            ->firstOrFail();

        $menus = $tenant->menus()
            ->where('is_active', true)
            ->orderByRaw('is_tersedia DESC')
            ->orderBy('nama_menu')
            ->get();

        $tenantBuka = $tenant->isBuka();

        return view('pembeli.tenant', compact('tenant', 'menus', 'tenantBuka'));
    }
}

