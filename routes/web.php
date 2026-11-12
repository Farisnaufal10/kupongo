<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pembeli\PembeliController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Modul Pembeli
|--------------------------------------------------------------------------
| Sprint 1: katalog tenant dan menu.
| Format akses via QR meja belum disepakati — route /meja/{qr} belum dibuat.
| Keranjang dan checkout akan ditambahkan pada Sprint 2+.
*/
Route::prefix('menu')->name('pembeli.')->group(function () {
    Route::get('/', [PembeliController::class, 'index'])->name('index');
    Route::get('/tenant/{id_tenant}', [PembeliController::class, 'showTenant'])
        ->name('tenant')
        ->whereNumber('id_tenant');
});
