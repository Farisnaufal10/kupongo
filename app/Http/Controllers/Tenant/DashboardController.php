<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Landing dashboard Tahap 1 untuk role Tenant.
 * Fitur operasional tenant (buka/tutup, CRUD menu, sub-order) adalah lingkup Anggota 2.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('tenant.dashboard', [
            'user' => $user,
            'tenant' => $request->attributes->get('current_tenant') ?? $user?->tenant,
        ]);
    }

    /**
     * Endpoint contoh terproteksi `tenant.owner` untuk memverifikasi isolasi data per tenant.
     */
    public function showProfil(Request $request, Tenant $tenant): View
    {
        return view('tenant.dashboard', [
            'user' => $request->user(),
            'tenant' => $tenant,
        ]);
    }
}
