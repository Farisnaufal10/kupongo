<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pembeli\PembeliController;
use App\Http\Controllers\Pembeli\CartController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Modul Pembeli
|--------------------------------------------------------------------------
| Sprint 1: katalog tenant dan menu.
| Sprint 2: keranjang multi-tenant berbasis session.
| Format akses via QR meja belum disepakati — route /meja/{qr} belum dibuat.
| Checkout dan pembayaran akan ditambahkan pada Sprint 3+ (Anggota 4).
*/
Route::prefix('menu')->name('pembeli.')->group(function () {
    Route::get('/', [PembeliController::class, 'index'])->name('index');
    Route::get('/tenant/{id_tenant}', [PembeliController::class, 'showTenant'])
        ->name('tenant')
        ->whereNumber('id_tenant');
});

Route::prefix('keranjang')->name('pembeli.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('keranjang');
    Route::post('/tambah', [CartController::class, 'tambah'])->name('keranjang.tambah');
    Route::patch('/jumlah', [CartController::class, 'setJumlah'])->name('keranjang.jumlah');
    Route::delete('/hapus', [CartController::class, 'hapus'])->name('keranjang.hapus');
    Route::patch('/catatan', [CartController::class, 'updateCatatan'])->name('keranjang.catatan');
    Route::delete('/', [CartController::class, 'kosongkan'])->name('keranjang.kosongkan');
});
