<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware otorisasi berbasis peran (Role-Based Access Control / RBAC).
 *
 * Penggunaan pada route:
 *   ->middleware(['auth', 'role:pengelola'])
 *   ->middleware(['auth', 'role:tenant'])
 *   ->middleware(['auth', 'role:pengelola,tenant'])
 */
class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Autentikasi diperlukan untuk mengakses halaman ini.',
                ], 401);
            }

            return redirect()->route('login');
        }

        // Pastikan akun masih aktif; jika dinonaktifkan saat sesi berjalan, paksa logout.
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akun Anda sedang dinonaktifkan. Hubungi Pengelola Sentra Kuliner.',
                ], 403);
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'username' => 'Akun Anda sedang dinonaktifkan. Hubungi Pengelola Sentra Kuliner.',
                ]);
        }

        $allowedRoles = array_map('strtolower', $roles);
        $userRole = strtolower((string) $user->role);

        if ($allowedRoles !== [] && ! in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
