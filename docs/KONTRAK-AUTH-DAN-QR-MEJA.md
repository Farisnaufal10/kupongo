# Kontrak Autentikasi, Role Middleware, dan QR Meja KuponGo

Dokumen ini merupakan kontrak integrasi resmi Tahap 1 dari **Anggota 3 (Pengelola & Autentikasi)** untuk **Anggota 1 (Pembeli)** dan **Anggota 2 (Tenant)** pada proyek KuponGo.

Seluruh kontrak disusun mengacu pada skema database aktual di `docs/ERD-KuponGo.md` tanpa mengubah migrasi inti.

---

## 1. Kontrak Autentikasi & Redirect Role

### 1.1. Route Autentikasi Bersama

| Method | URI | Nama Route | Middleware | Fungsi |
|---|---|---|---|---|
| `GET` | `/login` | `login` | `guest` | Menampilkan halaman login terpadu Pengelola & Tenant |
| `POST` | `/login` | `login.submit` | `guest` | Memproses autentikasi berbasis `username` (mendukung username biasa maupun format email) |
| `POST` | `/logout` | `logout` | `auth` | Menghapus sesi login, regenerasi CSRF token, dan redirect ke `route('login')` |

### 1.2. Aturan Login & Redirect Otomatis

- **Pembeli tanpa login:** Seluruh route `/menu/*` (dan `/meja/{token}` milik Anggota 1) bersifat publik.
- **Kolom kredensial:** Menggunakan kolom aktual `users.username` (panjang maks. 50 karakter; dapat berisi username biasa seperti `tenant01` maupun format email seperti `tenant@kupongo.id`) dan `users.password` (bcrypt).
- **Validasi keaktifan:**
  - Akun dengan `users.is_active = false` ditolak saat login dan dipaksa logout apabila dinonaktifkan di tengah sesi.
  - Akun role `tenant` yang memiliki profil `tenants.is_active = false` ditolak saat login.
- **Redirect setelah login berhasil:**
  - `role = 'pengelola'` → `route('pengelola.dashboard')` (`/pengelola/dashboard`)
  - `role = 'tenant'` → `route('tenant.dashboard')` (`/tenant/dashboard`)

---

## 2. Kontrak Middleware Otorisasi (Untuk Anggota 2 — Tenant)

Alias middleware telah didaftarkan di `bootstrap/app.php`:

| Alias | Kelas Middleware | Fungsi |
|---|---|---|
| `role` | `App\Http\Middleware\EnsureUserHasRole` | Memastikan user aktif dan memiliki role sesuai parameter (`role:pengelola` atau `role:tenant`). Mengembalikan `403` untuk akses lintas-role. |
| `tenant.owner` | `App\Http\Middleware\EnsureTenantOwnership` | Memastikan akun `tenant` memiliki profil `Tenant` aktif, menyematkan `current_tenant` ke request, dan memblokir (`403`) akses ke data tenant lain. |

### 2.1. Cara Memasang di Route Modul Tenant

```php
Route::prefix('tenant')
    ->name('tenant.')
    ->middleware(['auth', 'role:tenant', 'tenant.owner'])
    ->group(function () {
        Route::get('/dashboard', ...)->name('dashboard');
        // Tambahkan route CRUD menu, status buka/tutup, sub-order, riwayat di sini
    });
```

### 2.2. Cara Membaca Tenant Aktif di Controller Anggota 2

```php
use App\Models\Tenant;
use Illuminate\Http\Request;

public function index(Request $request)
{
    /** @var Tenant $tenant */
    $tenant = $request->attributes->get('current_tenant');
    // Gunakan $tenant->id_tenant untuk seluruh query menu & sub_orders
}
```

### 2.3. Perlindungan Lintas-Tenant Otomatis (`tenant.owner`)

Middleware `EnsureTenantOwnership` otomatis memverifikasi dan mengembalikan `403 Forbidden` apabila:
1. Parameter route bernama `{tenant}`, `{id_tenant}`, `{menu}`, `{subOrder}`, atau `{sub_order}` memiliki `id_tenant` berbeda dari tenant yang sedang login.
2. Query string atau body request mengirimkan `id_tenant` milik tenant lain.
3. Parameter route kustom juga dapat ditunjuk eksplisit: `tenant.owner:nama_param`.

### 2.4. Informasi Meja pada Sub-Order untuk Anggota 2 (Tenant)

Saat menampilkan daftar antrean `sub_orders`, Tenant cukup membaca relasi Eloquent dari ERD aktual:

```php
$nomorMeja = $subOrder->order?->meja?->nomor_meja;
$statusMeja = $subOrder->order?->meja?->status; // 'tersedia' | 'terisi' | 'tidak_aktif'
```

---

## 3. Kontrak `MejaQrService` (Untuk Anggota 1 — Pembeli)

Kelas: `App\Services\MejaQrService`

Service ini menggunakan kolom aktual tabel `meja` (`id_meja`, `nomor_meja`, `qr_code_path`, `kapasitas`, `status`) tanpa menambah kolom baru.

### 3.1. Format Token & URL Scan QR Meja

- **Format Token:** `M{id_meja}.{signature}`
  - `{id_meja}`: Primary key integer dari tabel `meja`.
  - `{signature}`: 16 karakter hex awal dari `HMAC-SHA256("kupongo:meja:{id_meja}:{UPPER(TRIM(nomor_meja))}", config('app.key'))`.
  - Contoh: `M12.a1b2c3d4e5f60718`
- **URL Scan QR yang dihasilkan Pengelola:**
  - `MejaQrService::generateScanUrl(Meja $meja)` menghasilkan `/meja/{token}` (atau menggunakan `route('pembeli.meja', ['token' => $token])` jika Anggota 1 telah mendaftarkan route bernama `pembeli.meja`).

### 3.2. Kontrak Input / Output `validateToken(?string $token): array`

- **Input:** `string|null $token` (diambil dari parameter route `/meja/{token}`).
- **Output (Sukses — `valid: true`):**

```php
[
    'valid'   => true,
    'reason'  => null,
    'message' => 'Meja M-01 berhasil diverifikasi.',
    'meja'    => [
        'id_meja'      => 1,
        'nomor_meja'   => 'M-01',
        'kapasitas'    => 4,
        'status'       => 'tersedia', // 'tersedia' atau 'terisi'
        'qr_code_path' => 'qr/meja/meja-1-m-01.svg', // atau null
    ],
    'model'   => App\Models\Meja, // instance Eloquent Meja
]
```

- **Output (Gagal — `valid: false`):**

```php
[
    'valid'   => false,
    'reason'  => 'MEJA_TIDAK_AKTIF', // lihat tabel kode reason di bawah
    'message' => 'Meja M-01 sedang tidak aktif dan tidak dapat digunakan untuk pemesanan.',
    'meja'    => null,
    'model'   => null,
]
```

### 3.3. Daftar Kode `reason` Validasi QR

| Kode Konstanta (`MejaQrService::*`) | Nilai String | Penyebab |
|---|---|---|
| `REASON_TOKEN_KOSONG` | `TOKEN_KOSONG` | Token kosong atau hanya spasi |
| `REASON_FORMAT_TIDAK_VALID` | `FORMAT_TIDAK_VALID` | Tidak cocok dengan pola `M{id_meja}.{16-hex}` |
| `REASON_MEJA_TIDAK_DITEMUKAN` | `MEJA_TIDAK_DITEMUKAN` | `id_meja` tidak ada di tabel `meja` |
| `REASON_SIGNATURE_TIDAK_VALID` | `SIGNATURE_TIDAK_VALID` | Signature dipalsukan atau `nomor_meja` sudah berubah sejak QR dibuat |
| `REASON_MEJA_TIDAK_AKTIF` | `MEJA_TIDAK_AKTIF` | Kolom `meja.status === 'tidak_aktif'` |

> **Catatan status `terisi`:** Meja dengan status `tersedia` **dan** `terisi` sama-sama dinyatakan **valid** agar pembeli yang sedang duduk di meja dapat memindai ulang QR untuk menambah pesanan atau mengecek status.

### 3.4. Helper Sesi Konteks Meja untuk Anggota 1 (Pembeli)

Session key standar: `MejaQrService::SESSION_KEY` (`'kupongo.meja_context'`).

```php
// Contoh integrasi di PembeliController::scanMeja(string $token, Request $request, MejaQrService $qrService)
$result = $qrService->validateToken($token);

if (! $result['valid']) {
    return redirect()->route('pembeli.index')
        ->with('meja_error', $result['message']);
}

$qrService->storeSessionContext($request->session(), $result['model'], $token);

return redirect()->route('pembeli.index');
```

Untuk membaca konteks meja pada halaman katalog/keranjang/checkout:

```php
// Membaca ringan dari sesi (array|null)
$mejaContext = $qrService->getSessionContext($request->session());

// Atau memvalidasi ulang terhadap DB aktual (mengembalikan ?Meja, otomatis clear jika meja dinonaktifkan)
$activeMeja = $qrService->resolveActiveMejaFromSession($request->session());
```
