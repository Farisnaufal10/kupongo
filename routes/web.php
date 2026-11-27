<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Pembeli\PembeliController;
use App\Http\Controllers\Pengelola\DashboardController as PengelolaDashboardController;
use App\Http\Controllers\Pengelola\MejaController as PengelolaMejaController;
use App\Http\Controllers\Pengelola\TenantController as PengelolaTenantController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Autentikasi Terpadu (Pengelola & Tenant)
|--------------------------------------------------------------------------
| Pembeli tidak memerlukan login. Login berbasis username (termasuk format
| email) dengan redirect otomatis sesuai role.
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Modul Pengelola (Dilindungi auth + role:pengelola)
|--------------------------------------------------------------------------
*/
Route::prefix('pengelola')
    ->name('pengelola.')
    ->middleware(['auth', 'role:pengelola'])
    ->group(function () {
        Route::get('/dashboard', [PengelolaDashboardController::class, 'index'])
            ->name('dashboard');

        // CRUD Tenant & Akun (soft-deactivation, tanpa hard delete)
        Route::prefix('tenants')->name('tenants.')->group(function () {
            Route::get('/', [PengelolaTenantController::class, 'index'])->name('index');
            Route::get('/create', [PengelolaTenantController::class, 'create'])->name('create');
            Route::post('/', [PengelolaTenantController::class, 'store'])->name('store');
            Route::get('/{tenant}/edit', [PengelolaTenantController::class, 'edit'])
                ->name('edit')
                ->whereNumber('tenant');
            Route::put('/{tenant}', [PengelolaTenantController::class, 'update'])
                ->name('update')
                ->whereNumber('tenant');
            Route::patch('/{tenant}/toggle-status', [PengelolaTenantController::class, 'toggleStatus'])
                ->name('toggle-status')
                ->whereNumber('tenant');
        });

        // CRUD Meja & QR Code
        Route::prefix('meja')->name('meja.')->group(function () {
            Route::get('/', [PengelolaMejaController::class, 'index'])->name('index');
            Route::get('/create', [PengelolaMejaController::class, 'create'])->name('create');
            Route::post('/', [PengelolaMejaController::class, 'store'])->name('store');
            Route::get('/{meja}/edit', [PengelolaMejaController::class, 'edit'])
                ->name('edit')
                ->whereNumber('meja');
            Route::put('/{meja}', [PengelolaMejaController::class, 'update'])
                ->name('update')
                ->whereNumber('meja');
            Route::post('/{meja}/qr/regenerate', [PengelolaMejaController::class, 'regenerateQr'])
                ->name('qr.regenerate')
                ->whereNumber('meja');
            Route::get('/{meja}/qr/preview', [PengelolaMejaController::class, 'previewQr'])
                ->name('qr.preview')
                ->whereNumber('meja');
            Route::get('/{meja}/qr/download', [PengelolaMejaController::class, 'downloadQr'])
                ->name('qr.download')
                ->whereNumber('meja');
        });
    });

/*
|--------------------------------------------------------------------------
| Modul Tenant (Dilindungi auth + role:tenant + tenant.owner)
|--------------------------------------------------------------------------
| Route tambahan operasional Tenant akan ditambahkan oleh Anggota 2 di grup ini.
*/
Route::prefix('tenant')
    ->name('tenant.')
    ->middleware(['auth', 'role:tenant', 'tenant.owner'])
    ->group(function () {
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/profil/{tenant}', [TenantDashboardController::class, 'showProfil'])
            ->name('profil')
            ->whereNumber('tenant');
    });

/*
|--------------------------------------------------------------------------
| Modul Pembeli
|--------------------------------------------------------------------------
| Sprint 1: katalog tenant dan menu.
| Format QR meja kini tersedia melalui App\Services\MejaQrService
| (lihat docs/KONTRAK-AUTH-DAN-QR-MEJA.md untuk integrasi route /meja/{token}).
| Keranjang dan checkout akan ditambahkan pada Sprint 2+.
*/
Route::prefix('menu')->name('pembeli.')->group(function () {
    Route::get('/', [PembeliController::class, 'index'])->name('index');
    Route::get('/tenant/{id_tenant}', [PembeliController::class, 'showTenant'])
        ->name('tenant')
        ->whereNumber('id_tenant');
});
