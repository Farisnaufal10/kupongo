<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller autentikasi terpadu untuk role Pengelola dan Tenant.
 *
 * Pembeli tidak memerlukan login (akses publik melalui /menu dan QR meja).
 */
class AuthController extends Controller
{
    /**
     * Menampilkan halaman login terpadu Pengelola & Tenant.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Memproses autentikasi login berbasis username (termasuk format email) dan password.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username atau email wajib diisi.',
            'username.max' => 'Username maksimal 50 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginIdentifier = trim($credentials['username']);
        $remember = $request->boolean('remember');

        if (! Auth::attempt([
            'username' => $loginIdentifier,
            'password' => $credentials['password'],
        ], $remember)) {
            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'Username atau kata sandi tidak sesuai.',
                ]);
        }

        /** @var User $user */
        $user = Auth::user();

        // Tolak login jika akun user dinonaktifkan.
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'Akun Anda sedang dinonaktifkan. Hubungi Pengelola Sentra Kuliner.',
                ]);
        }

        // Tolak login jika akun tenant memiliki profil tenant yang dinonaktifkan oleh Pengelola.
        if ($user->isTenant() && $user->tenant !== null && ! $user->tenant->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors([
                    'username' => 'Akun tenant Anda sedang dinonaktifkan oleh Pengelola.',
                ]);
        }

        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    /**
     * Mengakhiri sesi autentikasi (logout) secara aman.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Mengarahkan pengguna ke dashboard sesuai role masing-masing.
     */
    private function redirectByRole(User $user): RedirectResponse
    {
        if ($user->isPengelola()) {
            return redirect()->route('pengelola.dashboard');
        }

        if ($user->isTenant()) {
            return redirect()->route('tenant.dashboard');
        }

        Auth::logout();

        return redirect()
            ->route('login')
            ->withErrors([
                'username' => 'Peran akun tidak dikenali oleh sistem.',
            ]);
    }
}
