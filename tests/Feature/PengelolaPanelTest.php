<?php

namespace Tests\Feature;

use App\Models\Meja;
use App\Models\Order;
use App\Models\Pengelola;
use App\Models\SubOrder;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MejaQrService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature Test Panel Pengelola (Tahap 2 Anggota 3):
 * 1. Navigasi sidebar & proteksi akses Pengelola.
 * 2. CRUD Tenant & Akun + soft-deactivation aman tanpa hard delete.
 * 3. CRUD Meja, auto-generate QR Code SVG, preview, dan unduh stiker QR siap cetak.
 * 4. Akurasi query Dashboard Analitik Pengelola (hanya menghitung 'sudah_dibayar').
 */
class PengelolaPanelTest extends TestCase
{
    use DatabaseTransactions;

    private function buatPengelola(): User
    {
        $user = User::create([
            'username' => 'pengelola_panel_' . uniqid(),
            'name' => 'Admin Panel',
            'password' => Hash::make('password123'),
            'role' => 'pengelola',
            'is_active' => true,
        ]);

        Pengelola::create([
            'id_user' => $user->id_user,
            'nama' => 'Admin Sentra Kuliner',
            'no_hp' => '081200001111',
        ]);

        return $user;
    }

    /**
     * @return array{user: User, tenant: Tenant}
     */
    private function buatTenant(
        string $namaTenant = 'Kedai Uji',
        bool $isActive = true,
        string $operasional = Tenant::STATUS_OPERASIONAL_BUKA
    ): array {
        $user = User::create([
            'username' => 'tenant_panel_' . uniqid(),
            'name' => $namaTenant,
            'password' => Hash::make('password123'),
            'role' => 'tenant',
            'is_active' => $isActive,
        ]);

        $tenant = Tenant::create([
            'id_user' => $user->id_user,
            'nama_tenant' => $namaTenant,
            'nomor_kontak' => '08123456789',
            'status_operasional' => $operasional,
            'is_active' => $isActive,
        ]);

        return ['user' => $user, 'tenant' => $tenant];
    }

    // =========================================================================
    // 1. Sidebar & Proteksi Akses Panel Pengelola
    // =========================================================================

    #[Test]
    public function sidebar_pengelola_menampilkan_navigasi_lengkap_dan_terproteksi_role(): void
    {
        $pengelola = $this->buatPengelola();

        $this->actingAs($pengelola)
            ->get(route('pengelola.dashboard'))
            ->assertStatus(200)
            ->assertSee('Dashboard')
            ->assertSee('Kelola Tenant')
            ->assertSee('Kelola Meja &amp; QR', false);

        // Tenant tidak boleh mengakses halaman Kelola Tenant maupun Kelola Meja
        ['user' => $tenantUser] = $this->buatTenant();

        $this->actingAs($tenantUser)
            ->get(route('pengelola.tenants.index'))
            ->assertStatus(403);

        $this->actingAs($tenantUser)
            ->get(route('pengelola.meja.index'))
            ->assertStatus(403);
    }

    // =========================================================================
    // 2. CRUD Tenant & Akun + Soft-Deactivation Aman
    // =========================================================================

    #[Test]
    public function pengelola_dapat_menambah_dan_mengedit_tenant_beserta_akun_loginnya(): void
    {
        $pengelola = $this->buatPengelola();
        $username = 'warung_baru_' . substr(uniqid(), -6);

        // Create tenant + akun
        $this->actingAs($pengelola)
            ->post(route('pengelola.tenants.store'), [
                'nama_tenant' => 'Warung Sate Maranggi',
                'nama_pemilik' => 'Pak Hendi',
                'username' => $username,
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'nomor_kontak' => '081299998888',
                'deskripsi' => 'Sate maranggi khas Purwakarta.',
                'status_operasional' => Tenant::STATUS_OPERASIONAL_BUKA,
            ])
            ->assertRedirect(route('pengelola.tenants.index'))
            ->assertSessionHas('success');

        $createdUser = User::where('username', $username)->first();
        $this->assertNotNull($createdUser);
        $this->assertSame('tenant', $createdUser->role);
        $this->assertTrue($createdUser->is_active);

        $tenant = Tenant::where('id_user', $createdUser->id_user)->first();
        $this->assertNotNull($tenant);
        $this->assertSame('Warung Sate Maranggi', $tenant->nama_tenant);

        // Tolak username duplikat
        $this->actingAs($pengelola)
            ->post(route('pengelola.tenants.store'), [
                'nama_tenant' => 'Tenant Duplikat',
                'username' => $username,
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'status_operasional' => Tenant::STATUS_OPERASIONAL_TUTUP,
            ])
            ->assertSessionHasErrors('username');

        // Edit profil tenant
        $this->actingAs($pengelola)
            ->put(route('pengelola.tenants.update', $tenant), [
                'nama_tenant' => 'Warung Sate Maranggi Asli',
                'nama_pemilik' => 'Pak Hendi Updated',
                'username' => $username,
                'nomor_kontak' => '081277776666',
                'deskripsi' => 'Deskripsi diperbarui.',
                'status_operasional' => Tenant::STATUS_OPERASIONAL_TUTUP,
            ])
            ->assertRedirect(route('pengelola.tenants.index'))
            ->assertSessionHas('success');

        $tenant->refresh();
        $this->assertSame('Warung Sate Maranggi Asli', $tenant->nama_tenant);
        $this->assertSame(Tenant::STATUS_OPERASIONAL_TUTUP, $tenant->status_operasional);
    }

    #[Test]
    public function penonaktifan_tenant_menggunakan_soft_deactivation_dan_menjaga_riwayat_transaksi(): void
    {
        $pengelola = $this->buatPengelola();
        ['user' => $tenantUser, 'tenant' => $tenant] = $this->buatTenant('Kedai Bertransaksi');

        $meja = Meja::create([
            'nomor_meja' => 'M-SD-' . strtoupper(substr(uniqid(), -4)),
            'kapasitas' => 4,
            'status' => Meja::STATUS_TERSEDIA,
        ]);

        $order = Order::create([
            'kode_order' => 'ORD-SD-' . uniqid(),
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => Order::METODE_QRIS,
            'total_harga' => 45000,
            'status_pembayaran' => Order::STATUS_SUDAH_DIBAYAR,
            'waktu_pesan' => now(),
        ]);

        $subOrder = SubOrder::create([
            'id_order' => $order->id_order,
            'id_tenant' => $tenant->id_tenant,
            'kode_suborder' => 'SUB-SD-' . uniqid(),
            'subtotal' => 45000,
            'status_pembayaran' => SubOrder::STATUS_PEMBAYARAN_SUDAH,
            'status_pesanan' => SubOrder::STATUS_PESANAN_SELESAI,
        ]);

        // Nonaktifkan tenant
        $this->actingAs($pengelola)
            ->patch(route('pengelola.tenants.toggle-status', $tenant))
            ->assertRedirect(route('pengelola.tenants.index'))
            ->assertSessionHas('success');

        $tenant->refresh();
        $tenantUser->refresh();

        $this->assertFalse($tenant->is_active);
        $this->assertSame(Tenant::STATUS_OPERASIONAL_TUTUP, $tenant->status_operasional);
        $this->assertFalse($tenantUser->is_active);

        // Pastikan data tenant dan riwayat sub_order TIDAK terhapus
        $this->assertDatabaseHas('tenants', ['id_tenant' => $tenant->id_tenant]);
        $this->assertDatabaseHas('sub_orders', ['id_suborder' => $subOrder->id_suborder]);

        // Aktifkan kembali tenant
        $this->actingAs($pengelola)
            ->patch(route('pengelola.tenants.toggle-status', $tenant))
            ->assertRedirect(route('pengelola.tenants.index'));

        $tenant->refresh();
        $tenantUser->refresh();
        $this->assertTrue($tenant->is_active);
        $this->assertTrue($tenantUser->is_active);
    }

    // =========================================================================
    // 3. CRUD Meja & Pembuatan / Unduh QR Code
    // =========================================================================

    #[Test]
    public function pengelola_dapat_membuat_meja_generate_qr_otomatis_edit_dan_mengunduh_stiker_qr(): void
    {
        Storage::fake('public');

        $pengelola = $this->buatPengelola();
        $nomorMeja = 'M-QR-' . strtoupper(substr(uniqid(), -4));

        // Tambah meja -> QR otomatis tergenerate & path tersimpan
        $this->actingAs($pengelola)
            ->post(route('pengelola.meja.store'), [
                'nomor_meja' => $nomorMeja,
                'kapasitas' => 4,
                'status' => Meja::STATUS_TERSEDIA,
            ])
            ->assertRedirect(route('pengelola.meja.index'))
            ->assertSessionHas('success');

        $meja = Meja::where('nomor_meja', $nomorMeja)->first();
        $this->assertNotNull($meja);
        $this->assertNotNull($meja->qr_code_path);
        Storage::disk('public')->assertExists($meja->qr_code_path);

        // Preview QR mengembalikan SVG valid
        $preview = $this->actingAs($pengelola)
            ->get(route('pengelola.meja.qr.preview', $meja));
        $preview->assertStatus(200);
        $preview->assertHeader('Content-Type', 'image/svg+xml; charset=utf-8');
        $this->assertStringContainsString('<svg', $preview->getContent());

        // Unduh stiker QR siap cetak mengembalikan attachment SVG berisi nomor meja & token valid
        $qrService = app(MejaQrService::class);
        $expectedToken = $qrService->generateToken($meja);

        $download = $this->actingAs($pengelola)
            ->get(route('pengelola.meja.qr.download', $meja));
        $download->assertStatus(200);
        $download->assertHeader('Content-Type', 'image/svg+xml; charset=utf-8');
        $this->assertStringContainsString('attachment;', (string) $download->headers->get('Content-Disposition'));
        $this->assertStringContainsString('MEJA ' . $nomorMeja, $download->getContent());
        $this->assertStringContainsString($expectedToken, $download->getContent());

        // Ubah nomor meja -> path QR otomatis diperbarui
        $oldPath = $meja->qr_code_path;
        $nomorBaru = $nomorMeja . 'B';

        $this->actingAs($pengelola)
            ->put(route('pengelola.meja.update', $meja), [
                'nomor_meja' => $nomorBaru,
                'kapasitas' => 6,
                'status' => Meja::STATUS_TERISI,
            ])
            ->assertRedirect(route('pengelola.meja.index'));

        $meja->refresh();
        $this->assertSame($nomorBaru, $meja->nomor_meja);
        $this->assertSame(6, (int) $meja->kapasitas);
        $this->assertNotSame($oldPath, $meja->qr_code_path);
        Storage::disk('public')->assertExists($meja->qr_code_path);
        Storage::disk('public')->assertMissing($oldPath);
    }

    // =========================================================================
    // 4. Akurasi Query Dashboard Analitik Pengelola
    // =========================================================================

    #[Test]
    public function dashboard_analitik_hanya_menghitung_transaksi_sudah_dibayar_dan_mengabaikan_pending_atau_batal(): void
    {
        $pengelola = $this->buatPengelola();

        // Bersihkan/isolasi baseline agar angka assert bersifat eksak di dalam transaksi test
        SubOrder::query()->delete();
        Order::query()->delete();
        Tenant::query()->update(['is_active' => false]);

        ['tenant' => $tenantA] = $this->buatTenant('Kedai Omzet A', true);
        ['tenant' => $tenantB] = $this->buatTenant('Kedai Omzet B', true);
        $this->buatTenant('Kedai Nonaktif C', false, Tenant::STATUS_OPERASIONAL_TUTUP);

        $meja = Meja::create([
            'nomor_meja' => 'M-AN-' . strtoupper(substr(uniqid(), -4)),
            'kapasitas' => 4,
            'status' => Meja::STATUS_TERSEDIA,
        ]);

        // Order 1: Lunas penuh (Rp 75.000 -> Tenant A 50.000 + Tenant B 25.000)
        $orderLunas = Order::create([
            'kode_order' => 'ORD-AN-LUNAS-' . uniqid(),
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => Order::METODE_QRIS,
            'total_harga' => 75000,
            'status_pembayaran' => Order::STATUS_SUDAH_DIBAYAR,
            'waktu_pesan' => now(),
        ]);

        SubOrder::create([
            'id_order' => $orderLunas->id_order,
            'id_tenant' => $tenantA->id_tenant,
            'kode_suborder' => 'SUB-AN-1A-' . uniqid(),
            'subtotal' => 50000,
            'status_pembayaran' => SubOrder::STATUS_PEMBAYARAN_SUDAH,
            'status_pesanan' => SubOrder::STATUS_PESANAN_SELESAI,
        ]);

        SubOrder::create([
            'id_order' => $orderLunas->id_order,
            'id_tenant' => $tenantB->id_tenant,
            'kode_suborder' => 'SUB-AN-1B-' . uniqid(),
            'subtotal' => 25000,
            'status_pembayaran' => SubOrder::STATUS_PEMBAYARAN_SUDAH,
            'status_pesanan' => SubOrder::STATUS_PESANAN_SELESAI,
        ]);

        // Order 2: Tunai sebagian dibayar (hanya sub-order Tenant A Rp 30.000 yang sah, Tenant B Rp 30.000 masih pending)
        $orderSebagian = Order::create([
            'kode_order' => 'ORD-AN-SEB-' . uniqid(),
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => Order::METODE_TUNAI,
            'total_harga' => 60000,
            'status_pembayaran' => Order::STATUS_SEBAGIAN_DIBAYAR,
            'waktu_pesan' => now(),
        ]);

        SubOrder::create([
            'id_order' => $orderSebagian->id_order,
            'id_tenant' => $tenantA->id_tenant,
            'kode_suborder' => 'SUB-AN-2A-' . uniqid(),
            'subtotal' => 30000,
            'status_pembayaran' => SubOrder::STATUS_PEMBAYARAN_SUDAH,
            'status_pesanan' => SubOrder::STATUS_PESANAN_DIPROSES,
        ]);

        SubOrder::create([
            'id_order' => $orderSebagian->id_order,
            'id_tenant' => $tenantB->id_tenant,
            'kode_suborder' => 'SUB-AN-2B-' . uniqid(),
            'subtotal' => 30000,
            'status_pembayaran' => SubOrder::STATUS_PEMBAYARAN_MENUNGGU,
            'status_pesanan' => SubOrder::STATUS_PESANAN_MENUNGGU,
        ]);

        // Order 3: Menunggu pembayaran Rp 999.000 (HARUS diabaikan total)
        $orderPending = Order::create([
            'kode_order' => 'ORD-AN-PEND-' . uniqid(),
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => Order::METODE_QRIS,
            'total_harga' => 999000,
            'status_pembayaran' => Order::STATUS_MENUNGGU_PEMBAYARAN,
            'waktu_pesan' => now(),
        ]);

        SubOrder::create([
            'id_order' => $orderPending->id_order,
            'id_tenant' => $tenantA->id_tenant,
            'kode_suborder' => 'SUB-AN-3A-' . uniqid(),
            'subtotal' => 999000,
            'status_pembayaran' => SubOrder::STATUS_PEMBAYARAN_MENUNGGU,
            'status_pesanan' => SubOrder::STATUS_PESANAN_MENUNGGU,
        ]);

        // Order 4: Dibatalkan Rp 888.000 (HARUS diabaikan total)
        $orderBatal = Order::create([
            'kode_order' => 'ORD-AN-BATAL-' . uniqid(),
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => Order::METODE_QRIS,
            'total_harga' => 888000,
            'status_pembayaran' => Order::STATUS_DIBATALKAN,
            'waktu_pesan' => now(),
        ]);

        SubOrder::create([
            'id_order' => $orderBatal->id_order,
            'id_tenant' => $tenantB->id_tenant,
            'kode_suborder' => 'SUB-AN-4B-' . uniqid(),
            'subtotal' => 888000,
            'status_pembayaran' => SubOrder::STATUS_PEMBAYARAN_DIBATALKAN,
            'status_pesanan' => SubOrder::STATUS_PESANAN_DIBATALKAN,
        ]);

        $response = $this->actingAs($pengelola)
            ->get(route('pengelola.dashboard'));

        $response->assertStatus(200);
        // Total omzet valid = 50.000 + 25.000 + 30.000 = 105.000
        $response->assertViewHas('totalOmzetValid', 105000.0);
        // Total transaksi (master order) lunas = 1 (hanya $orderLunas)
        $response->assertViewHas('totalTransaksiLunas', 1);
        // Jumlah tenant aktif = 2 ($tenantA & $tenantB)
        $response->assertViewHas('jumlahTenantAktif', 2);

        $rekap = collect($response->viewData('rekapTenant'))->keyBy('id_tenant');
        $this->assertSame(80000.0, $rekap[$tenantA->id_tenant]['total_omzet']);
        $this->assertSame(2, $rekap[$tenantA->id_tenant]['jumlah_suborder_lunas']);
        $this->assertSame(25000.0, $rekap[$tenantB->id_tenant]['total_omzet']);
        $this->assertSame(1, $rekap[$tenantB->id_tenant]['jumlah_suborder_lunas']);

        $response->assertSee('Rp 105.000');
        $response->assertDontSee('999.000');
        $response->assertDontSee('888.000');
    }
}
