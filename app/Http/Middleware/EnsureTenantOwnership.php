<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware isolasi data milik Tenant (mencegah akses lintas-tenant / IDOR).
 *
 * Memastikan:
 * 1. Pengguna yang login memiliki role 'tenant' serta profil Tenant yang aktif.
 * 2. Parameter route (seperti {tenant}, {id_tenant}, {menu}, {subOrder}, {sub_order})
 *    atau input request `id_tenant` selalu merujuk pada tenant milik pengguna yang login.
 * 3. Menyematkan instance Tenant aktif pada `$request->attributes->get('current_tenant')`
 *    agar controller Anggota 2 (Tenant) dapat langsung menggunakannya.
 *
 * Penggunaan pada route:
 *   ->middleware(['auth', 'role:tenant', 'tenant.owner'])
 *   ->middleware(['auth', 'role:tenant', 'tenant.owner:id_tenant'])
 */
class EnsureTenantOwnership
{
    /**
     * Daftar parameter route standar KuponGo yang akan otomatis diverifikasi kepemilikannya.
     *
     * @var list<string>
     */
    private const DEFAULT_ROUTE_PARAMS = [
        'tenant',
        'id_tenant',
        'menu',
        'subOrder',
        'sub_order',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $parameterName = null): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! $user->isTenant()) {
            abort(403, 'Halaman ini hanya dapat diakses oleh akun Tenant yang aktif.');
        }

        $ownTenant = $user->tenant;

        if (! $ownTenant || ! $ownTenant->is_active) {
            abort(403, 'Profil tenant tidak ditemukan atau sedang dinonaktifkan oleh Pengelola.');
        }

        $ownTenantId = (int) $ownTenant->id_tenant;

        // Sediakan instance Tenant aktif untuk controller modul Tenant.
        $request->attributes->set('current_tenant', $ownTenant);

        // 1. Verifikasi parameter route eksplisit atau parameter standar KuponGo.
        $paramsToCheck = $parameterName !== null
            ? [$parameterName]
            : self::DEFAULT_ROUTE_PARAMS;

        $route = $request->route();
        if ($route !== null) {
            foreach ($paramsToCheck as $param) {
                if (! $route->hasParameter($param)) {
                    continue;
                }

                $targetTenantId = $this->extractTenantId($route->parameter($param), $param);

                if ($targetTenantId === null || $targetTenantId !== $ownTenantId) {
                    abort(403, 'Anda tidak diizinkan mengakses atau mengubah data tenant lain.');
                }
            }
        }

        // 2. Verifikasi input/query `id_tenant` jika dikirim dalam request.
        if ($request->filled('id_tenant')) {
            $requestedTenantId = $request->input('id_tenant');

            if (! is_numeric($requestedTenantId) || (int) $requestedTenantId !== $ownTenantId) {
                abort(403, 'Anda tidak diizinkan mengakses atau mengubah data tenant lain.');
            }
        }

        return $next($request);
    }

    /**
     * Mengekstrak id_tenant dari parameter route (Model Eloquent atau scalar ID).
     */
    private function extractTenantId(mixed $value, string $paramName): ?int
    {
        if ($value instanceof Tenant) {
            return (int) $value->id_tenant;
        }

        if ($value instanceof Model) {
            $tenantId = $value->getAttribute('id_tenant');

            return is_numeric($tenantId) ? (int) $tenantId : null;
        }

        if (in_array($paramName, ['tenant', 'id_tenant'], true) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
